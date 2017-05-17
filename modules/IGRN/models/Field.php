<?php

/**
 * Field Class for IGRN
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class IGRN_Field_Model extends Vtiger_Field_Model
{

	public function isAjaxEditable()
	{
		$edit = parent::isAjaxEditable();
		if ($edit && $this->getName() === 'igrn_status') {
			$edit = false;
		}
		return $edit;
	}
}
