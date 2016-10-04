<?php

/**
 * Field Class for IGIN
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class IGIN_Field_Model extends Vtiger_Field_Model
{

	public function isAjaxEditable()
	{
		$edit = parent::isAjaxEditable();
		if ($edit && $this->getName() === 'igin_status') {
			$edit = false;
		}
		return $edit;
	}
}
