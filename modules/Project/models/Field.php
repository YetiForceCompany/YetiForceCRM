<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class Project_Field_Model extends Vtiger_Field_Model
{

	/**
	 * Function to check whether the current field is editable
	 * @return <Boolean> - true/false
	 */
	public function isEditable()
	{
		$notEditableFields = $this->getProjectNotEditabeFields();
		if (!$this->isEditEnabled() || !$this->isViewable() ||
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
	public function getProjectNotEditabeFields()
	{
		return ['sum_time'];
	}
}
