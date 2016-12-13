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
		$forModule = AppRequest::get('return_module');
		$forCrmid = AppRequest::get('return_id');
		if (AppRequest::get('return_action') && $forModule && $forCrmid && $forModule === 'HelpDesk') {
			CRMEntity::getInstance($forModule)->save_related_module($forModule, $forCrmid, AppRequest::get('module'), $this->getId());
		}
	}
}
