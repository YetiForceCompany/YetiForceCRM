<?php
/**
 * UIType Image Field File.
 *
 * @package   Settings.UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * UIType Image Field Class.
 */
class Settings_Media_Image_UIType extends Vtiger_Image_UIType
{
	/**
	 * Get upload URL.
	 *
	 * @return string
	 */
	public function getUploadUrl(): string
	{
		$moduleModel = $this->getFieldModel()->getModule();
		$moduleName = $moduleModel->getName(false);

		return "file.php?module={$moduleName}&parent={$moduleModel->parent}&action=Images&field={$this->getFieldModel()->getName()}";
	}

	/**
	 * Get removal URL.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getRemoveURL(string $key): string
	{
		$moduleModel = $this->getFieldModel()->getModule();
		$moduleName = $moduleModel->getName(false);

		return "file.php?module={$moduleName}&parent={$moduleModel->parent}&action=Images&field={$this->getFieldModel()->getName()}&key={$key}&remove=true";
	}

	/**
	 * Delete image file.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function removeImage(string $key): bool
	{
		return \App\Layout\Media::removeImage($key);
	}

	/** {@inheritdoc} */
	public function getAcceptFormats(): string
	{
		$formats = [];
		foreach (\App\Fields\File::$allowedFormats['image'] as $format) {
			$formats[] = "image/{$format}";
		}
		return $formats ? implode(',', $formats) : 'image/*';
	}

	/** {@inheritdoc} */
	public function getFieldInfo(): array
	{
		$fieldInfo = $this->getFieldModel()->loadFieldInfo();
		$fieldInfo['limit'] = 1;
		$fieldInfo['formats'] = explode(',', $this->getAcceptFormats());
		$defaultSize = 2097152;
		$maxUploadSize = \App\Config::getMaxUploadSize();
		if ($defaultSize < $maxUploadSize) {
			$maxUploadSize = $defaultSize;
		}
		$fieldInfo['maxFileSize'] = $maxUploadSize;
		$fieldInfo['maxFileSizeDisplay'] = \vtlib\Functions::showBytes($maxUploadSize);

		return $fieldInfo;
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		$hashValue = \is_array($value) ? md5(print_r($value, true)) : $value;
		if (isset($this->validate[$hashValue]) || empty($value)) {
			return;
		}
		if (!$isUserFormat && \is_string($value)) {
			$value = \App\Json::decode($value);
		}
		$fieldInfo = $this->getFieldModel()->getFieldInfo();
		foreach ($value as $index => $item) {
			if (empty($item['key']) || empty($item['name']) || empty($item['size']) || 50 !== App\TextUtils::getTextLength($item['key'])) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . \App\Json::encode($value), 406);
			}
			if ($index > (int) $fieldInfo['limit']) {
				throw new \App\Exceptions\Security('ERR_TO_MANY_FILES||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . \App\Json::encode($value), 406);
			}
			$path = \App\Fields\File::getLocalPath($item['path']);
			if (!file_exists($path)) {
				continue;
			}
			$file = \App\Fields\File::loadFromInfo([
				'path' => $path,
				'name' => $item['name'],
			]);
			$validFormat = $file->validateAndSecure('image');
			$validExtension = false;
			foreach ($fieldInfo['formats'] as $format) {
				if ($file->getMimeType(true) === strtolower($format)) {
					$validExtension = true;
					break;
				}
			}
			if (!$validExtension || !$validFormat) {
				throw new \App\Exceptions\Security('ERR_FILE_WRONG_IMAGE||' . $this->getFieldModel()->getName() . '||' . \App\Json::encode($value), 406);
			}
		}
		$this->validate[$hashValue] = true;
	}

	/** {@inheritdoc} */
	public function uploadTempFile(array $files, int $recordId, ?string $hash = null)
	{
		$db = \App\Db::getInstance();
		$attach = [];
		$maxSize = $this->getFieldInfo()['maxFileSize'];
		foreach ($files as $key => $fileDetails) {
			$file = \App\Fields\File::loadFromRequest($fileDetails);
			if ($file->getSize() > $maxSize || !$file->validate('image')) {
				$attach[] = ['name' => $file->getName(true), 'error' => $file->validateError];
				continue;
			}
			if (!$uploadFilePath = \App\Fields\File::initStorage($this->getStorageFileDir())) {
				\App\Log::error("Upload location failed: {$this->getStorageFileDir()}");
				break;
			}
			$key = $file->generateHash(true, $uploadFilePath);
			$tempValue = [
				'name' => $file->getName(true),
				'size' => $file->getSize(),
				'path' => $file->getPath(),
				'key' => $key,
				'type' => $file->getMimeType(),
			];
			$this->validate([$tempValue]);

			$newPath = $uploadFilePath . $key . ".{$file->getExtension()}";

			$tempAdd = $file->insertMediaFile(['path' => $uploadFilePath, 'fieldname' => $this->getFieldModel()->getName(), 'key' => $key]);
			if ($tempAdd && move_uploaded_file($file->getPath(), $newPath)) {
				$db->createCommand()->update(\App\Layout\Media::TABLE_NAME_MEDIA, ['status' => 1], ['key' => $key])->execute();
				$attach[] = [
					'name' => $file->getName(true),
					'size' => $file->getSize(),
					'sizeDisplay' => \vtlib\Functions::showBytes($file->getSize()),
					'key' => $key,
					'src' => IS_PUBLIC_DIR && 0 === strpos($newPath, 'public_html/') ? substr($newPath, 12, \strlen($newPath)) : $newPath,
					'type' => $tempValue['type'],
				];
			} else {
				$db->createCommand()->delete(\App\Layout\Media::TABLE_NAME_MEDIA, ['key' => $key])->execute();
				\App\Log::error("Moves an uploaded file to a new location failed: {$uploadFilePath}");
				$attach[] = ['name' => $file->getName(true), 'error' => ''];
			}
		}

		return $attach;
	}

	/** {@inheritdoc} */
	public function getStorageFileDir(): string
	{
		return 'public_html/storage/Media/Images';
	}
}
