<?php
/**
 * UIType MultiImage Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Michał Lorencik <m.lorencik@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * UIType MultiImage Field Class.
 */
class Vtiger_MultiImage_UIType extends Vtiger_Base_UIType
{
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
	 * Function to get the Template name for the current UI Type object.
	 *
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/MultiImage.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$imageIcons = '<div class="multiImageContenDiv">';
		if ($record) {
			if (!AppConfig::performance('ICON_MULTIIMAGE_VIEW')) {
				$images = $this->getMultiImageQuery($value, ['name'], false)->column('name');

				return implode(', ', $images);
			}
			$images = $this->getMultiImageQuery($value, [], $length);
			foreach ($images->all() as $attach) {
				$imageIcons .= '<div class="contentImage" title="' . $attach['name'] . '">'
					. '<button type="button" class="btn btn-sm btn-default imageFullModal hide"><span class="fas fa-expand-arrows-alt"></span></button>'
					. '<img src="' . $this->getImagePath($attach['attachmentid'], $record) . '" class="multiImageListIcon"></div>';
			}
		}
		$imageIcons .= '</div>';

		return $imageIcons;
	}

	/**
	 * Get patch for image.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function getImagePath($value, $recordId)
	{
		$field = $this->getFieldModel();
		$moduleName = $field->getModuleName();

		return "file.php?module=$moduleName&action=MultiImage&record=$recordId&attachment=$value&field={$field->getId()}";
	}

	/**
	 * Function to get the List Display Value.
	 *
	 * @param string              $value
	 * @param int                 $record
	 * @param Vtiger_Record_Model $recordModel
	 * @param bool                $rawText
	 *
	 * @return string
	 */
	public function getListViewDisplayValue($value, $record = false, $recordModel = false, $rawText = false)
	{
		$images = $this->getDisplayValue($value, $record, $recordModel, true);

		return !AppConfig::performance('ICON_MULTIIMAGE_VIEW') ? \App\TextParser::textTruncate($images, $this->getFieldModel()->get('maxlengthtext')) : $images;
	}

	/**
	 * Function to get the edit value in display view.
	 *
	 * @param mixed               $value
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return mixed
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		return $recordModel ? $this->getMultiImageQuery($value, [], false)->all() : [];
	}

	/**
	 * Get query for attachments.
	 *
	 * @param string $value
	 * @param array  $fields
	 * @param bool   $limit
	 *
	 * @return type
	 */
	public function getMultiImageQuery($value, $fields = [], $limit = true)
	{
		$query = (new App\Db\Query());
		if ($fields) {
			$query->select($fields);
		}
		$query->from('u_#__attachments')->where(['attachmentid' => explode(',', $value)]);
		if ($limit) {
			$query->limit(AppConfig::performance('MAX_MULTIIMAGE_VIEW'));
		}

		return $query;
	}
}
