<?php

/**
 *
 * @package YetiForce.models
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Assets_Field_Model extends Vtiger_Field_Model
{

	public function isAjaxEditable()
	{
		$edit = parent::isAjaxEditable();
		if ($edit && $this->getName() === 'assetstatus') {
			$edit = false;
		}
		return $edit;
	}
}
