<?php

/**
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Assets_Field_Model extends Vtiger_Field_Model
{
	public function isAjaxEditable()
	{
		$edit = parent::isAjaxEditable();
		if ($edit && 'assetstatus' === $this->getName()) {
			$edit = false;
		}
		return $edit;
	}
}
