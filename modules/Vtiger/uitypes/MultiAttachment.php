<?php
/**
 * UIType Multi Attachment Field File.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * UIType Multi Attachment Field Class.
 */
class Vtiger_MultiAttachment_UIType extends Vtiger_Base_UIType
{
	/** @var int Default attachments limit. */
	public const LIMIT = 3;

	/** @var string Name of the action to handle the files */
	public const FILE_ACTION_NAME = 'MultiAttachment';

	/** {@inheritdoc} */
	public function setValueFromRequest(App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getName();
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		[$value, $newValues, $save] = $this->updateUploadFiles($request->getArray($requestFieldName, 'Text'), $recordModel);
		$this->validate($newValues, true);
		if (($value && $request->getBoolean('_isDuplicateRecord') && $this->duplicateValueFromRecord($value, $request)) || $save) {
			$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
		}
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
		foreach ($value as $item) {
			if (5 !== \count($item) || empty($item['key']) || empty($item['name']) || empty($item['size']) || empty($item['path']) || !\array_key_exists('type', $item) || 50 !== App\TextUtils::getTextLength($item['key'])) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . \App\Json::encode($value), 406);
			}
			if (\count($value) > (int) $fieldInfo['limit']) {
				throw new \App\Exceptions\Security('ERR_TO_MANY_FILES||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . \App\Json::encode($value), 406);
			}
			if ($item['size'] > (int) $fieldInfo['maxFileSize']) {
				throw new \App\Exceptions\Security('ERR_FILE_SIZE_EXCEEDED||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . \App\Json::encode($item), 406);
			}
			$formats = $fieldInfo['formats'];
			$type = $item['type'] ?? '';
			$validFormat = false;
			foreach ($formats as $format) {
				if ($type === $format || ('/*' === substr($format, -2) && strstr($format, '/', true) == strstr($type, '/', true))) {
					$validFormat = true;
					break;
				}
			}
			if ($formats && !$validFormat) {
				throw new \App\Exceptions\Security('ERR_FILE_TYPE||' . $this->getFieldModel()->getName() . '||' . \App\Json::encode($value), 406);
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
	 * Get file url.
	 *
	 * @param string $key
	 * @param int    $record
	 *
	 * @return string
	 */
	public function getFileUrl($key, $record)
	{
		return "file.php?module={$this->getFieldModel()->getModuleName()}&action=" . static::FILE_ACTION_NAME . "&field={$this->getFieldModel()->getName()}&record={$record}&key={$key}";
	}

	/** {@inheritdoc} */
	public function getHistoryDisplayValue($value, Vtiger_Record_Model $recordModel, $rawText = false)
	{
		if (\in_array(\App\Anonymization::MODTRACKER_DISPLAY, $this->getFieldModel()->getAnonymizationTarget())) {
			return '****';
		}
		$result = '';
		$value = \App\Json::decode($value);
		if (\is_array($value)) {
			$result = implode(', ', array_column($value, 'name'));
		}
		return \App\Purifier::encodeHtml($result);
	}

	/** {@inheritdoc} */
	public function getTextParserDisplayValue($value, Vtiger_Record_Model $recordModel, $params)
	{
		return $this->getDisplayValue($value, $recordModel->getId(), $recordModel, true);
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$valueArr = \App\Json::decode($value);
		if (!$valueArr) {
			return '';
		}
		if (!$record && $recordModel) {
			$record = $recordModel->getId();
		}
		$names = array_column($valueArr, 'name');
		if ($rawText) {
			return implode(', ', $names);
		}

		$length = $length ?: ($this->getFieldModel()->get('maxlengthtext') ?: null);
		$text = \App\Purifier::encodeHtml(\App\TextUtils::textTruncate(implode(', ', $names), $length));

		$value = $this->getValueEncoded($value, $record);
		$result = '<div class="js-multi-attachment c-multi-attachment"><div class="js-multi-attachment__result js-multi-attachment__values" data-value="' . $value . '"></div></div>';
		$icon = $this->getFieldModel()->getIcon()['name'] ?? '';
		$title = "<span class=\"{$icon} mr-1\"></span>" . $this->getFieldModel()->getFullLabelTranslation() . ': ' . ($record ? \App\Record::getLabel($record) : '');

		return '<a href="#" class="js-show-modal-content" data-content="' . \App\Purifier::encodeHtml($result) . '" data-class="" data-js="modal" data-title="' . \App\Purifier::encodeHtml($title) . '">' . $text . '</a>';
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return $this->getValueEncoded($value, $recordModel ? $recordModel->getId() : null);
	}

	/**
	 * Get value as string in JSON format.
	 *
	 * @param string   $value
	 * @param int|null $record
	 * @param bool|int $length
	 *
	 * @return string
	 */
	public function getValueEncoded($value, $record)
	{
		$value = \App\Json::decode($value);
		if (\is_array($value)) {
			foreach ($value as &$item) {
				if ($record) {
					$item['url'] = $this->getFileUrl($item['key'], $record);
				}
				$item['icon'] = \App\Layout\Icon::getIconByFileType($item['type'] ?? '');
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
					'actionName' => static::FILE_ACTION_NAME,
					'field' => $this->getFieldModel()->getName(),
					'record' => $id,
					'key' => $item['key'],
				],
			];
			$return[] = $file;
		}
		return $return;
	}

	/** {@inheritdoc} */
	public function getValueToExport($value, int $recordId)
	{
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
		}
		return $return ? \App\Json::encode($return) : '';
	}

	/** {@inheritdoc} */
	public function getValueFromImport($value, $defaultValue = null)
	{
		$new = [];
		$value = $value && !\App\Json::isEmpty($value) ? \App\Json::decode($value) : [];
		foreach ($value as $item) {
			if (isset($item['baseContent'])) {
				$new[] = $this->saveFromBase($item);
			}
		}
		return \App\Json::encode($new);
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/MultiAttachment.tpl';
	}

	/**
	 * If the field is editable by ajax.
	 *
	 * @return bool
	 */
	public function isAjaxEditable()
	{
		return false;
	}

	/**
	 * If the field is active in search.
	 *
	 * @return bool
	 */
	public function isActiveSearchView()
	{
		return false;
	}

	/**
	 * If the field is sortable in ListView.
	 *
	 * @return bool
	 */
	public function isListviewSortable()
	{
		return false;
	}

	/**
	 * Duplicate value from record.
	 *
	 * @param array       $value
	 * @param App\Request $request
	 *
	 * @throws \App\Exceptions\FieldException
	 *
	 * @return bool
	 */
	public function duplicateValueFromRecord(&$value, App\Request $request): bool
	{
		$fieldName = $this->getFieldModel()->getName();
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('_duplicateRecord'), $request->getModule());
		$copyValue = $recordModel->get($fieldName);
		$keyColumn = array_column($value, 'key');
		$createCopy = false;
		if (!\App\Json::isEmpty($copyValue)) {
			foreach (\App\Json::decode($copyValue) as $item) {
				$key = array_search($item['key'], $keyColumn);
				if (false === $key) {
					continue;
				}
				$file = \App\Fields\File::loadFromPath($item['path']);
				$dirPath = $file->getDirectoryPath();
				$newKey = $file->generateHash(true, $dirPath);
				$path = $dirPath . DIRECTORY_SEPARATOR . $newKey;
				if (copy($item['path'], $path)) {
					$createCopy = true;
					$item['key'] = $newKey;
					$item['path'] = $path;
					$value[$key] = $item;
				} else {
					\App\Log::error("Error during file copy: {$item['path']} >> {$path}");
					throw new \App\Exceptions\FieldException('ERR_CREATE_FILE_FAILURE');
				}
			}
		}
		return $createCopy;
	}

	/**
	 * Duplicate value from record.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @throws \App\Exceptions\FieldException
	 *
	 * @return string
	 */
	public function getDuplicateValue(Vtiger_Record_Model $recordModel)
	{
		$value = [];
		$copyValue = $recordModel->get($this->getFieldModel()->getName());
		if (!\App\Json::isEmpty($copyValue)) {
			foreach (\App\Json::decode($copyValue) as $item) {
				$file = \App\Fields\File::loadFromPath($item['path']);
				$dirPath = $file->getDirectoryPath();
				$newKey = $file->generateHash(true, $dirPath);
				$path = $dirPath . DIRECTORY_SEPARATOR . $newKey;
				if (copy($item['path'], $path)) {
					$item['key'] = $newKey;
					$item['path'] = $path;
					$value[] = $item;
				} else {
					\App\Log::error("Error during file copy: {$item['path']} >> {$item['path']}");
					throw new \App\Exceptions\FieldException('ERR_CREATE_FILE_FAILURE');
				}
			}
		}
		return $this->getDBValue($value, $recordModel);
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['text'];
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['y', 'ny'];
	}

	/**
	 * Provide a filter in the file select dialog box.
	 *
	 * @return string
	 */
	public function getAcceptFormats(): string
	{
		$formats = $this->getFieldModel()->getFieldInfo()['formats'] ?? [];
		return $formats ? implode(',', $formats) : '';
	}

	/** {@inheritdoc} */
	public function getFieldInfo(): array
	{
		$fieldInfo = $this->getFieldModel()->loadFieldInfo();
		$params = $this->getFieldModel()->getFieldParams();
		$fieldInfo['limit'] = $params['limit'] ?? static::LIMIT;
		$fieldInfo['formats'] = $params['formats'] ?? [];
		$maxUploadSize = App\Config::getMaxUploadSize();
		if (isset($params['maxFileSize']) && $params['maxFileSize'] < $maxUploadSize) {
			$maxUploadSize = $params['maxFileSize'];
		}
		$fieldInfo['maxFileSize'] = $maxUploadSize;
		$fieldInfo['maxFileSizeDisplay'] = \vtlib\Functions::showBytes($maxUploadSize);
		return $fieldInfo;
	}

	/**
	 * Upload and save attachment.
	 *
	 * @param array $files
	 * @param int   $recordId
	 *
	 * @return array
	 */
	public function uploadTempFile(array $files, int $recordId)
	{
		$db = \App\Db::getInstance();
		$attach = [];
		$maxSize = $this->getFieldInfo()['maxFileSize'];
		foreach (\App\Fields\File::transform($files, true) as $key => $transformFiles) {
			foreach ($transformFiles as $fileDetails) {
				$file = \App\Fields\File::loadFromRequest($fileDetails);
				if ($file->getSize() > $maxSize || !$file->validate()) {
					$attach[] = ['name' => $file->getName(true), 'error' => $file->validateError];
					continue;
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

	/**
	 * Update upload files.
	 *
	 * @param array               $value
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return void
	 */
	public function updateUploadFiles(array $value, Vtiger_Record_Model $recordModel)
	{
		$previousValue = $recordModel->get($this->getFieldModel()->getName());
		$previousValue = ($previousValue && !\App\Json::isEmpty($previousValue)) ? \App\Fields\File::parse(\App\Json::decode($previousValue)) : [];
		$value = \App\Fields\File::parse($value);
		$new = [];
		$save = false;
		foreach ($value as $key => $item) {
			if (isset($item['key'], $previousValue[$item['key']])) {
				$value[$item['key']] = $previousValue[$item['key']];
			} elseif (!empty($item['baseContent'])) {
				$base = $this->saveFromBase($item);
				$new[] = $value[$base['key']] = $base;
				unset($value[$key]);
				$save = true;
			} elseif (!empty($item['key']) ? ($uploadFile = \App\Fields\File::getUploadFile($item['key'])) : null) {
				$new[] = $value[$item['key']] = [
					'name' => $uploadFile['name'],
					'size' => $item['size'],
					'path' => $uploadFile['path'] . $item['key'],
					'key' => $item['key'],
					'type' => $item['type'] ?? '',
				];
				$save = true;
			}
		}
		foreach ($previousValue as $item) {
			if (isset($item['key']) && !isset($value[$item['key']])) {
				$save = true;
				break;
			}
		}
		if (!$save && $previousValue && $value) {
			$save = $previousValue == $value && $previousValue !== $value;
		}
		return [array_values($value), $new, $save];
	}

	/**
	 * Save file from base64 encoded string.
	 *
	 * @param string $raw base64 string
	 *
	 * @return array
	 */
	public function saveFromBase($raw): array
	{
		$file = \App\Fields\File::loadFromContent(\base64_decode($raw['baseContent']), $raw['name']);
		$savePath = \App\Fields\File::initStorageFileDirectory($this->getStorageFileDir());
		$key = $file->generateHash(true, $savePath);
		if ($file->insertTempFile(['path' => $savePath . $key, 'key' => $key, 'fieldname' => $this->getFieldModel()->getName()]) && $file->moveFile($savePath . $key)) {
			return [
				'name' => $file->getName(true),
				'size' => $file->getSize(),
				'path' => $savePath . $key,
				'key' => $key,
				'type' => $file->getMimeType(),
			];
		}
		$file->delete();
		return [];
	}

	/**
	 * Get storage file sufix directory.
	 *
	 * @return string
	 */
	public function getStorageFileDir(): string
	{
		return static::FILE_ACTION_NAME . DIRECTORY_SEPARATOR . $this->getFieldModel()->getModuleName() . DIRECTORY_SEPARATOR . $this->getFieldModel()->getName();
	}
}
