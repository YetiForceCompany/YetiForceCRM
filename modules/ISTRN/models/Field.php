<?php

/**
 * Field Class for ISTRN.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class ISTRN_Field_Model extends Vtiger_Field_Model
{
	public function isAjaxEditable()
	{
		$edit = parent::isAjaxEditable();
		if ($edit && $this->getName() === 'istrn_status') {
			$edit = false;
		}
		return $edit;
	}
}
