<?php

/**
 * Field Class for KnowledgeBase
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class KnowledgeBase_Field_Model extends Vtiger_Field_Model
{

	function isAjaxEditable()
	{
		$edit = parent::isAjaxEditable();
		if ($edit && $this->getName() === 'istdn_status') {
			$edit = false;
		}
		return $edit;
	}
}
