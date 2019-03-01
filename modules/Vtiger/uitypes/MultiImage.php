<?php
/**
 * UIType MultiImage Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Lorencik <m.lorencik@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * UIType MultiImage Field Class.
 */
class Vtiger_MultiImage_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function setValueFromRequest(\App\Request $request, Vtiger_Record_Model $recordModel, $requestFieldName = false)
	{
		$fieldName = $this->getFieldModel()->getFieldName();
		if (!$requestFieldName) {
			$requestFieldName = $fieldName;
		}
		[$value, $newValues, $save] = \App\Fields\File::updateUploadFiles($request->getArray($requestFieldName, 'Text'), $recordModel, $this->getFieldModel());
		$this->validate($newValues, true);
		if ($save) {
			if ($request->getBoolean('_isDuplicateRecord')) {
				$this->duplicateValueFromRecord($value, $request);
			}
			$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		$hashValue = is_array($value) ? md5(print_r($value, true)) : $value;
		if (isset($this->validate[$hashValue]) || empty($value)) {
			return;
		}
		if (!$isUserFormat && is_string($value)) {
			$value = \App\Json::decode($value);
		}
		$fieldInfo = $this->getFieldModel()->getFieldInfo();
		foreach ($value as $index => $item) {
			if ((empty($item['name']) && empty($item['baseContent'])) && (empty($item['key']) || empty($item['name']) || empty($item['size']) || App\TextParser::getTextLength($item['key']) !== 50)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . \App\Json::encode($value), 406);
			}
			if ($index > (int) $fieldInfo['limit']) {
				throw new \App\Exceptions\Security('ERR_TO_MANY_FILES||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . \App\Json::encode($value), 406);
			}
			$path = \App\Fields\File::getLocalPath($item['path']);
			if (!file_exists($path)) {
				continue;
			}
			$file = \App\Fields\File::loadFromInfo([
				'path' => $path,
				'name' => $item['name'],
			]);
			$validFormat = $file->validate('image');
			$validExtension = false;
			foreach ($fieldInfo['formats'] as $format) {
				if ($file->getExtension(true) === strtolower($format)) {
					$validExtension = true;
					break;
				}
			}
			if (!$validExtension || !$validFormat) {
				throw new \App\Exceptions\Security('ERR_FILE_WRONG_IMAGE||' . $this->getFieldModel()->getFieldName() . '||' . \App\Json::encode($value), 406);
			}
		}
		$this->validate[$hashValue] = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		return empty($value) ? '' : \App\Json::encode($value);
	}

	/**
	 * Get image url.
	 *
	 * @param {string} $key
	 * @param {int}    $record
	 *
	 * @return string
	 */
	public function getImageUrl($key, $record)
	{
		return "file.php?module={$this->getFieldModel()->getModuleName()}&action=MultiImage&field={$this->getFieldModel()->getFieldName()}&record={$record}&key={$key}";
	}

	/**
	 * Get display value as string in JSON format.
	 *
	 * @param {string} $value
	 * @param bool|int $length
	 *
	 * @return string
	 */
	public function getDisplayValueEncoded($value, $record, $length = false)
	{
		$value = \App\Json::decode($value);
		if (!is_array($value) || empty($value)) {
			return '[]';
		}
		$imagesCount = count($value);
		if (!empty($length) && $imagesCount > $length) {
			$len = $length;
		}
		if (empty($len)) {
			$len = $imagesCount;
		}
		for ($i = 0; $i < $len; $i++) {
			$value[$i]['imageSrc'] = $this->getImageUrl($value[$i]['key'], $record);
			unset($value[$i]['path']);
		}
		return \App\Purifier::encodeHtml(\App\Json::encode($value));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHistoryDisplayValue($value, Vtiger_Record_Model $recordModel)
	{
		$value = \App\Json::decode($value);
		if (!is_array($value)) {
			return '';
		}
		$value = array_map(function ($v) {
			return $v['name'];
		}, $value);
		$result = implode(', ', $value);
		return trim($result, "\n\s\t, ");
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTextParserDisplayValue($value, Vtiger_Record_Model $recordModel, $params)
	{
		$value = \App\Json::decode($value);
		if (!$value) {
			return '';
		}
		$images = $style = '';
		if ($params) {
			list($width, $height) = explode('|', $params, 2);
			if ($width) {
				$style .= "width:$width;";
			}
			if ($height) {
				$style .= "height:$height;";
			}
		} else {
			$width = 100 / count($value);
			$style .= "width:$width%;";
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

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$value = \App\Json::decode($value);
		if (!$value) {
			return '';
		}
		$len = $length ?: count($value);
		if (!$record && $recordModel) {
			$record = $recordModel->getId();
		}
		if ($rawText || !$record) {
			$result = '';
			if (!is_array($value)) {
				return '';
			}
			for ($i = 0; $i < $len; $i++) {
				$val = $value[$i];
				$result .= $val['name'] . ', ';
			}
			return \App\Purifier::encodeHtml(trim($result, "\n\s\t ,"));
		}
		if (!is_array($value)) {
			return '';
		}
		$result = '<div class="c-multi-image__result" style="width:100%">';
		$width = 1 / count($value) * 100;
		for ($i = 0; $i < $len; $i++) {
			if ($record) {
				$src = $this->getImageUrl($value[$i]['key'], $record);
				$result .= '<div class="d-inline-block mr-1 c-multi-image__preview-img" style="background-image:url(' . $src . ')" style="width:' . $width . '%"></div>';
			} else {
				$result .= \App\Purifier::encodeHtml($value[$i]['name']) . ', ';
			}
		}
		return trim($result, "\n\s\t ") . '</div>';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		$value = \App\Json::decode($value);
		if (!$value) {
			return '';
		}
		if (!$record && $recordModel) {
			$record = $recordModel->getId();
		}
		if ($rawText || !$record) {
			$result = '';
			if (!is_array($value)) {
				return '';
			}
			$len = count($value);
			for ($i = 0; $i < $len; $i++) {
				$val = $value[$i];
				$result .= $val['name'] . ', ';
			}
			return \App\Purifier::encodeHtml(trim($result, "\n\s\t ,"));
		}
		if (!is_array($value)) {
			return '';
		}
		$result = '<div class="c-multi-image__result">';
		foreach ($value as $item) {
			if ($record) {
				$result .= '<div class="d-inline-block mr-1 c-multi-image__preview-img" style="background-image:url(' . $this->getImageUrl($item['key'], $record) . ')"></div>';
			} else {
				$result .= \App\Purifier::encodeHtml($item['name']) . ', ';
			}
		}
		return $result . '</div>';
	}

	/**
	 * {@inheritdoc}
	 */
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
		if (is_array($value)) {
			foreach ($value as &$item) {
				$item['imageSrc'] = $this->getImageUrl($item['key'], $id);
				unset($item['path']);
			}
		} else {
			$value = [];
		}
		return \App\Purifier::encodeHtml(\App\Json::encode($value));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/MultiImage.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDetailViewTemplateName()
	{
		return 'Detail/Field/MultiImage.tpl';
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
	 * @param array        $value
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\FieldException
	 */
	public function duplicateValueFromRecord(&$value, \App\Request $request)
	{
		$fieldName = $this->getFieldModel()->getFieldName();
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('_duplicateRecord'), $request->getModule());
		$copyValue = $recordModel->get($fieldName);
		$keyColumn = array_column($value, 'key');
		if ($copyValue && $copyValue !== '[]' && $copyValue !== '""') {
			foreach (\App\Json::decode($copyValue) as $item) {
				$key = array_search($item['key'], $keyColumn);
				if ($key === false) {
					continue;
				}
				$file = \App\Fields\File::loadFromPath($item['path']);
				$dirPath = $file->getDirectoryPath();
				$newKey = $file->generateHash(true, $dirPath);
				$path = $dirPath . DIRECTORY_SEPARATOR . $newKey;
				if (copy($item['path'], $path)) {
					$item['key'] = $newKey;
					$item['path'] = $path;
					$value[$key] = $item;
				} else {
					\App\Log::error("Error during file copy: {$item['path']} >> {$path}");
					throw new \App\Exceptions\FieldException('ERR_CREATE_FILE_FAILURE');
				}
			}
		}
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
		$copyValue = $recordModel->get($this->getFieldModel()->getFieldName());
		if ($copyValue && $copyValue !== '[]' && $copyValue !== '""') {
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

	/**
	 * Delete files for the field MultiImage.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public static function deleteRecord(\Vtiger_Record_Model $recordModel)
	{
		foreach ($recordModel->getModule()->getFieldsByType(['multiImage', 'image']) as $fieldModel) {
			if (!$recordModel->isEmpty($fieldModel->getName()) && !\App\Json::isEmpty($recordModel->get($fieldModel->getName()))) {
				foreach (\App\Json::decode($recordModel->get($fieldModel->getName())) as $image) {
					$path = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $image['path'];
					if (file_exists($path)) {
						unlink($path);
					}
				}
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAllowedColumnTypes()
	{
		return ['text'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOperators()
	{
		return ['y', 'ny'];
	}
}
