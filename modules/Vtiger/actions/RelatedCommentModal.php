<?php

/**
 * Update comment for related record
 * @package YetiForce.ModalView
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_RelatedCommentModal_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $record);
		if (!$recordPermission) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$relatedRecord = $request->get('relid');
		$relatedModuleName = $request->get('relmodule');

		$rcmModel = Vtiger_RelatedCommentModal_Model::getInstance($record, $moduleName, $relatedRecord, $relatedModuleName);
		if (!$rcmModel->isEditable()) {
			throw new \Exception\NoPermitted(vtranslate('LBL_PERMISSION_DENIED'));
		}
		$rcmModel->save($request->get('comment'));

		$response = new Vtiger_Response();
		$response->setResult(vtranslate('LBL_SAVED_RELATION_COMMENT', $moduleName));
		$response->emit();
	}
}
