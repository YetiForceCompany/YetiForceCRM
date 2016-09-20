<?php

/**
 *
 * @package YetiForce.models
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Assets_Field_Model extends Vtiger_Field_Model
{

	/**
	 * Function to check whether the current field is editable
	 * @return <Boolean> - true/false
	 */
	public function isEditable()
	{

		$notEditableFields = $this->getEditabeFields();
		if (!$this->isEditEnabled() ||
			( ((int) $this->get('displaytype')) != 1 && ((int) $this->get('displaytype')) != 10 ) ||
			$this->isReadOnly() == true || $this->get('uitype') == 4 || in_array($this->get('column'), $notEditableFields)) {
			return false;
		}
		return true;
	}

	/**
	 * Function that returns uneditable fields
	 * @return <array>
	 */
	public function getEditabeFields()
	{
		return ['sum_time'];
	}

	public function isAjaxEditable()
	{
		$edit = parent::isAjaxEditable();
		if ($edit && $this->getName() === 'assetstatus') {
			$edit = false;
		}
		return $edit;
	}
}
