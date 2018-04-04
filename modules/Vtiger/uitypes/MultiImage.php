<?php
/**
 * UIType MultiImage Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Michał Lorencik <m.lorencik@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
		$value = \App\Fields\File::updateUploadFiles($request->getArray($requestFieldName, 'Text'), $recordModel, $this->getFieldModel());
		$this->validate($value, true);
		$recordModel->set($fieldName, $this->getDBValue($value, $recordModel));
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if (!$isUserFormat) {
			$value = \App\Json::decode($value);
		}
		foreach ($value as $item) {
			if (empty($item['key']) || empty($item['name']) || empty($item['size']) || App\TextParser::getTextLength($item['key']) !== 50) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . \App\Json::encode($value), 406);
			}
		}
		$params = $this->getFieldModel()->getFieldParams();

		$this->validate = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		return \App\Json::encode($value);
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
		if (!is_array($value)) {
			return '[]';
		}
		$len = $length ?: count($value);
		for ($i = 0; $i < $len; $i++) {
			$value[$i]['imageSrc'] = "file.php?module={$this->getFieldModel()->getModuleName()}&action=MultiImage&field={$this->getFieldModel()->getFieldName()}&record={$record}&key={$value[$i]['key']}";
			unset($value[$i]['path']);
		}
		return \App\Purifier::encodeHtml(\App\Json::encode($value));
	}

	/**
	 * Function to get Display value for ModTracker.
	 *
	 * @param                      $value
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return mixed
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
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$value = \App\Json::decode($value);
		if (!$value) {
			return '';
		}
		$len = $length ? $length : count($value);
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
		$result = '<span class="c-multi-image__result">';
		for ($i = 0; $i < $len; $i++) {
			if ($record) {
				$src = "file.php?module={$this->getFieldModel()->getModuleName()}&action=MultiImage&field={$this->getFieldModel()->getFieldName()}&record={$record}&key={$value[$i]['key']}";
				$result .= '<img class="img-thumbnail" src="' . $src . '">';
			} else {
				$result .= \App\Purifier::encodeHtml($value[$i]['name']) . ', ';
			}
		}
		return trim($result, "\n\s\t ") . '</span>';
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
		$len = $length ? $length : count($value);
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
		$result = '<div class="c-multi-image__result">';
		for ($i = 0; $i < $len; $i++) {
			if ($record) {
				$src = "file.php?module={$this->getFieldModel()->getModuleName()}&action=MultiImage&field={$this->getFieldModel()->getFieldName()}&record={$record}&key={$value[$i]['key']}";
				$result .= '<img class="c-multi-image__preview-img" style="background-image:url(' . $src . ')">';
			} else {
				$result .= \App\Purifier::encodeHtml($value[$i]['name']) . ', ';
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
		if (is_array($value)) {
			foreach ($value as &$item) {
				$item['imageSrc'] = "file.php?module={$this->getFieldModel()->getModuleName()}&action=MultiImage&field={$this->getFieldModel()->getFieldName()}&record={$recordModel->getId()}&key={$item['key']}";
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
		return 'uitypes/MultiImage.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDetailViewTemplateName()
	{
		return 'uitypes/MultiImageDetailView.tpl';
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
}
