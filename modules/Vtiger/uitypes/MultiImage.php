<?php
/**
 * UIType MultiImage Field Class.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Lorencik <m.lorencik@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * UIType MultiImage Field Class.
 */
class Vtiger_MultiImage_UIType extends Vtiger_MultiAttachment_UIType
{
	/** @var int Default attachments limit. */
	public const LIMIT = 10;

	/** @var string Name of the action to handle the files */
	public const FILE_ACTION_NAME = 'MultiImage';

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
			if ((empty($item['name']) && empty($item['baseContent'])) && (empty($item['key']) || empty($item['name']) || empty($item['size']) || 50 !== App\TextUtils::getTextLength($item['key']))) {
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
				if ($file->getExtension(true) === strtolower($format)) {
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
	public function getDBValue($value, $recordModel = false)
	{
		return empty($value) ? '' : \App\Json::encode($value);
	}

	/**
	 * Get image url.
	 *
	 * @param string $key
	 * @param int    $record
	 *
	 * @return string
	 */
	public function getImageUrl($key, $record)
	{
		return "file.php?module={$this->getFieldModel()->getModuleName()}&action=" . static::FILE_ACTION_NAME . "&field={$this->getFieldModel()->getName()}&record={$record}&key={$key}";
	}

	/**
	 * Get display value as string in JSON format.
	 *
	 * @param string   $value
	 * @param bool|int $length
	 * @param mixed    $record
	 *
	 * @return string
	 */
	public function getDisplayValueEncoded($value, $record, $length = false)
	{
		$value = \App\Json::decode($value);
		if (!\is_array($value) || empty($value)) {
			return '[]';
		}
		$imagesCount = \count($value);
		if (!empty($length) && $imagesCount > $length) {
			$len = $length;
		}
		if (empty($len)) {
			$len = $imagesCount;
		}
		for ($i = 0; $i < $len; ++$i) {
			$value[$i]['imageSrc'] = $this->getImageUrl($value[$i]['key'], $record);
			if (!is_numeric($value[$i]['size'])) {
				$value[$i]['size'] = \vtlib\Functions::parseBytes($value[$i]['size']);
			}
			$value[$i]['sizeDisplay'] = \vtlib\Functions::showBytes($value[$i]['size']);
			unset($value[$i]['path']);
		}
		return \App\Purifier::encodeHtml(\App\Json::encode($value));
	}

	/** {@inheritdoc} */
	public function getHistoryDisplayValue($value, Vtiger_Record_Model $recordModel, $rawText = false)
	{
		if (\in_array(\App\Anonymization::MODTRACKER_DISPLAY, $this->getFieldModel()->getAnonymizationTarget())) {
			return '****';
		}
		$value = \App\Json::decode($value);
		if (!\is_array($value)) {
			return '';
		}
		$value = array_map(fn ($v) => $v['name'], $value);
		$result = implode(', ', $value);
		return trim($result, "\n\t, ");
	}

	/** {@inheritdoc} */
	public function getTextParserDisplayValue($value, Vtiger_Record_Model $recordModel, $params)
	{
		$value = \App\Json::decode($value);
		if (!$value) {
			return '';
		}
		$images = $style = '';
		if ($params) {
			[$width, $height, $style] = array_pad(explode('|', $params, 3), 3, '');
			if ($width) {
				$style .= "max-width:$width;";
			}
			if ($height) {
				$style .= "max-height:$height;";
			}
		} else {
			$width = 100 / \count($value);
			$style .= "max-width:$width%;";
			$images .= '<div style="width:100%">';
		}
		foreach ($value as $item) {
			$base64 = base64_encode(file_get_contents($item['path']));
			$images .= "<img src=\"data:image/jpeg;base64,$base64\" style=\"$style\"/>";
		}
		if (!$params) {
			$images .= '</div>';
		}
		return $images;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$value = \App\Json::decode($value);
		if (!$value) {
			return '';
		}
		$imageCount = (int) ($this->getFieldModel()->getFieldParams()['imageCount'] ?? 0);
		$countValue = \count($value);
		$len = ($imageCount <= $countValue) && ($imageCount > 0) ? $imageCount : $countValue;
		if (!$record && $recordModel) {
			$record = $recordModel->getId();
		}
		if ($rawText || !$record) {
			$result = '';
			if (!\is_array($value)) {
				return '';
			}
			for ($i = 0; $i < $len; ++$i) {
				$val = $value[$i];
				$result .= $val['name'] . ', ';
			}
			return \App\Purifier::encodeHtml($length ? \App\TextUtils::textTruncate($result, $length) : $result, "\n\t ,");
		}
		if (!\is_array($value)) {
			return '';
		}
		$result = '<div class="c-multi-image__result" style="width:100%">';
		$width = 1 / $len * 100;
		for ($i = 0; $i < $len; ++$i) {
			$result .= '<div class="d-inline-block mr-1 c-multi-image__preview-img" style="background-image:url(' . $this->getImageUrl($value[$i]['key'], $record) . ')" style="width:' . $width . '%"></div>';
		}
		return trim($result, "\n\t ") . '</div>';
	}

	/** {@inheritdoc} */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		$value = \App\Json::decode($value);
		if (!$value) {
			return '';
		}
		$imageCount = (int) ($this->getFieldModel()->getFieldParams()['imageCount'] ?? 0);
		$countValue = \count($value);
		$len = ($imageCount <= $countValue) && ($imageCount > 0) ? $imageCount : $countValue;
		if (!$record && $recordModel) {
			$record = $recordModel->getId();
		}
		if ($rawText || !$record) {
			$result = '';
			if (!\is_array($value)) {
				return '';
			}
			for ($i = 0; $i < $len; ++$i) {
				$val = $value[$i];
				$result .= $val['name'] . ', ';
			}
			return \App\Purifier::encodeHtml(trim($result, "\n\t ,"));
		}
		if (!\is_array($value)) {
			return '';
		}
		$result = '<div class="c-multi-image__result text-center">';
		for ($i = 0; $i < $len; ++$i) {
			$result .= '<div class="d-inline-block mr-1 c-multi-image__preview-img middle js-show-image-preview" style="background-image:url(' . $this->getImageUrl($value[$i]['key'], $record) . ')"></div>';
		}
		return $result . '</div>';
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		$value = \App\Json::decode($value);
		$id = false;
		if ($recordModel) {
			$id = $recordModel->getId();
		}
		if (!$id && \App\Request::_has('record')) {
			$id = \App\Request::_get('record');
		}
		if (\is_array($value)) {
			foreach ($value as &$item) {
				$item['imageSrc'] = $this->getImageUrl($item['key'], $id);
				if (!is_numeric($item['size'])) {
					$item['size'] = \vtlib\Functions::parseBytes($item['size']);
				}
				$item['sizeDisplay'] = \vtlib\Functions::showBytes($item['size']);
				unset($item['path']);
			}
		} else {
			$value = [];
		}
		return \App\Purifier::encodeHtml(\App\Json::encode($value));
	}

	/** {@inheritdoc} */
	public function getApiDisplayValue($value, Vtiger_Record_Model $recordModel, array $params = [])
	{
		$value = \App\Json::decode($value);
		if (!$value || !\is_array($value)) {
			return [];
		}
		$multiMode = 'multiImage' === $this->getFieldModel()->getFieldDataType();
		$id = $recordModel->getId();
		$return = [];
		foreach ($value as $item) {
			$path = \App\Fields\File::getLocalPath($item['path']);
			if (!file_exists($path)) {
				throw new \Api\Core\Exception('File does not exist: ' . $path, 404);
			}
			$file = \App\Fields\File::loadFromInfo([
				'path' => $path,
				'name' => $item['name'],
			]);
			$file = [
				'name' => $item['name'],
				'type' => $file->getMimeType(),
				'size' => $file->getSize(),
				'path' => 'Files',
				'postData' => [
					'module' => $this->getFieldModel()->getModuleName(),
					'actionName' => 'MultiImage',
					'field' => $this->getFieldModel()->getName(),
					'record' => $id,
					'key' => $item['key'],
				],
			];
			if ($multiMode) {
				$return[] = $file;
			} else {
				$return = $file;
				break;
			}
		}
		return $return;
	}

	/** {@inheritdoc} */
	public function getValueToExport($value, int $recordId)
	{
		$multiMode = 'multiImage' === $this->getFieldModel()->getFieldDataType();
		if (\is_string($value)) {
			$value = \App\Json::isEmpty($value) ? [] : \App\Json::decode($value);
		} elseif (!\is_array($value)) {
			$value = [];
		}
		$return = [];
		foreach ($value as $item) {
			$path = \App\Fields\File::getLocalPath($item['path']);
			if (!file_exists($path)) {
				throw new \App\Exceptions\AppException('File does not exist: ' . $path, 404);
			}
			$fileInstance = \App\Fields\File::loadFromInfo([
				'path' => $path,
				'name' => $item['name'],
			]);
			$file = [
				'name' => $item['name'],
				'type' => $fileInstance->getMimeType(),
				'size' => $fileInstance->getSize(),
				'baseContent' => base64_encode($fileInstance->getContents()),
			];
			$return[] = $file;
			if (!$multiMode) {
				break;
			}
		}
		return $return ? \App\Json::encode($return) : '';
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/MultiImage.tpl';
	}

	/** {@inheritdoc} */
	public function getDetailViewTemplateName()
	{
		return 'Detail/Field/MultiImage.tpl';
	}

	/** {@inheritdoc} */
	public function getFieldInfo(): array
	{
		$fieldInfo = $this->getFieldModel()->loadFieldInfo();
		$params = $this->getFieldModel()->getFieldParams();
		$fieldInfo['limit'] = $params['limit'] ?? static::LIMIT;
		$fieldInfo['formats'] = $params['formats'] ?? \App\Fields\File::$allowedFormats['image'];
		$maxUploadSize = App\Config::getMaxUploadSize();
		if (isset($params['maxFileSize']) && $params['maxFileSize'] < $maxUploadSize) {
			$maxUploadSize = $params['maxFileSize'];
		}
		$fieldInfo['maxFileSize'] = $maxUploadSize;
		$fieldInfo['maxFileSizeDisplay'] = \vtlib\Functions::showBytes($maxUploadSize);
		return $fieldInfo;
	}

	/**
	 * Provide a filter in the file select dialog box.
	 *
	 * @return string
	 */
	public function getAcceptFormats(): string
	{
		$formats = [];
		foreach ($this->getFieldModel()->getFieldInfo()['formats'] ?? [] as $format) {
			$formats[] = "image/{$format}";
		}
		return $formats ? implode(',', $formats) : 'image/*';
	}

	/**
	 * Upload and save attachment.
	 *
	 * @param array       $files
	 * @param int         $recordId
	 * @param string|null $hash
	 *
	 * @return array
	 */
	public function uploadTempFile(array $files, int $recordId, ?string $hash = null)
	{
		$db = \App\Db::getInstance();
		$attach = [];
		$type = 'image';
		$maxSize = $this->getFieldInfo()['maxFileSize'];
		foreach (\App\Fields\File::transform($files, true) as $key => $transformFiles) {
			foreach ($transformFiles as $fileDetails) {
				$additionalNotes = '';
				$file = \App\Fields\File::loadFromRequest($fileDetails);
				if ($file->getSize() > $maxSize) {
					$attach[] = ['name' => $file->getName(), 'error' => \App\Language::translateSingleMod('ERR_FILE_SIZE_EXCEEDED', 'Other.Exceptions'), 'hash' => $hash];
					continue;
				}
				if (!$file->validate($type)) {
					if (!\App\Fields\File::secureFile($file)) {
						$attach[] = ['name' => $file->getName(), 'error' => $file->validateError, 'hash' => $hash];
						continue;
					}
					$fileDetails['size'] = filesize($fileDetails['tmp_name']);
					$file = \App\Fields\File::loadFromRequest($fileDetails);
					if (!$file->validate($type)) {
						$attach[] = ['name' => $file->getName(), 'error' => $file->validateError, 'hash' => $hash];
						continue;
					}
					$additionalNotes = \App\Language::translate('LBL_FILE_HAS_BEEN_MODIFIED');
				}
				$uploadFilePath = \App\Fields\File::initStorageFileDirectory($this->getStorageFileDir());
				$key = $file->generateHash(true, $uploadFilePath);
				$tempValue = [
					'name' => $file->getName(true),
					'size' => $file->getSize(),
					'path' => $file->getPath(),
					'key' => $key,
					'type' => $file->getMimeType(),
				];
				$this->validate([$tempValue]);

				$tempAdd = $file->insertTempFile(['path' => $uploadFilePath, 'fieldname' => $this->getFieldModel()->getName(), 'key' => $key, 'crmid' => $recordId]);
				if ($tempAdd && move_uploaded_file($file->getPath(), $uploadFilePath . $key)) {
					$attach[] = [
						'name' => $file->getName(true),
						'size' => $file->getSize(),
						'sizeDisplay' => \vtlib\Functions::showBytes($file->getSize()),
						'key' => $key,
						'icon' => \App\Layout\Icon::getIconByFileType($file->getMimeType()),
						'type' => $file->getMimeType(),
						'info' => $additionalNotes,
						'hash' => $hash,
					];
				} else {
					$db->createCommand()->delete(\App\Fields\File::TABLE_NAME_TEMP, ['key' => $key])->execute();
					\App\Log::error("Moves an uploaded file to a new location failed: {$uploadFilePath}");
					$attach[] = ['name' => $file->getName(true), 'error' => ''];
				}
			}
		}
		return $attach;
	}

	/** {@inheritdoc} */
	public function getTilesDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		$rawValue = $value;
		if (!$value || !($value = \App\Json::decode($value))) {
			return '';
		}
		if (!$record && $recordModel) {
			$record = $recordModel->getId();
		}
		$result = '';
		if ($rawText || !$record) {
			if (!\is_array($value)) {
				$result .= '</div></div>';
				return $result;
			}
			$len = \count($value);
			for ($i = 0; $i < $len; ++$i) {
				$val = $value[$i];
				$result .= $val['name'] . ', ';
			}
			return \App\Purifier::encodeHtml(trim($result, "\n\t ,"));
		}

		if (!\is_array($value)) {
			$result .= '</div></div>';
			return $result;
		}
		if (1 === \count($value)) {
			return $this->getListViewDisplayValue($rawValue, $record, $recordModel, $rawText);
		}
		if ($record) {
			$carouselId = App\Layout::getUniqueId("IC{$record}-");
			$result = '<div id="' . $carouselId . '" class="carousel slide m-auto" data-interval="false">
				<div class="carousel-inner">';
			foreach ($value as $itemNumber => $item) {
				if ($record) {
					$active = 0 === $itemNumber ? 'active' : '';
					$result .= '<div class="carousel-item ' . $active . '">
				<img class="d-block carousel-image img-fluid js-show-image-preview" src="' . $this->getImageUrl($item['key'], $record) . '" alt="Carousel image">
			  </div>';
				} else {
					$result .= \App\Purifier::encodeHtml($item['name']) . ', ';
				}
			}
			$result .= '</div>
			<a class="carousel-control-prev noLinkBtn" href="#' . $carouselId . '" role="button" data-slide="prev">
				<span class="carousel-control-prev-icon" aria-hidden="true"></span>
				<span class="sr-only">Previous</span>
			</a>
			<a class="carousel-control-next noLinkBtn" href="#' . $carouselId . '" role="button" data-slide="next">
				<span class="carousel-control-next-icon" aria-hidden="true"></span>
				<span class="sr-only">Next</span>
			</a></div>';
		}
		return $result;
	}
}
