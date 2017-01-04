<?php

/**
 * Create View Class for MailSmtp
 * @package YetiForce.Settings.ModalView
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_MailSmtp_Create_View extends Settings_Vtiger_BasicModal_View
{

	/**
	 * Function returns name that defines modal window size
	 * @param Vtiger_Request $request
	 * @return string
	 */
	public function getSize(Vtiger_Request $request)
	{
		return 'modal-lg';
	}

	/**
	 * Function proccess
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$this->preProcess($request);
	
		$record = $request->get('record');
		if (!empty($record)) {
			$recordModel = Settings_MailSmtp_Record_Model::getInstanceById($record);
		} else {
			$recordModel = Settings_MailSmtp_Record_Model::getCleanInstance();
		}
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('QUALIFIED_MODULE', $moduleName);
		$viewer->view('Create.tpl', $moduleName);
		$this->postProcess($request);
	}
}
