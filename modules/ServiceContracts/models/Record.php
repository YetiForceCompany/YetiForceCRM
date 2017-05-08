<?php

/**
 * Service contracts record model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class ServiceContracts_Record_Model extends Vtiger_Record_Model
{

	/**
	 * Function to save record
	 */
	public function saveToDb()
	{
		parent::saveToDb();
		$forModule = \App\Request::_get('return_module');
		$forCrmid = \App\Request::_get('return_id');
		if (\App\Request::_get('return_action') && $forModule && $forCrmid && $forModule === 'HelpDesk') {
			CRMEntity::getInstance($forModule)->save_related_module($forModule, $forCrmid, \App\Request::_get('module'), $this->getId());
		}
	}
}
