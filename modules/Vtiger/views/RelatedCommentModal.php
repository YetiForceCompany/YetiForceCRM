<?php

/**
 * Update comment for related record
 * @package YetiForce.ModalView
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_RelatedCommentModal_View extends Vtiger_BasicModal_View
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

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RELATED_RECORD', $relatedRecord);
		$viewer->assign('RELATED_MODULE', $relatedModuleName);
		$viewer->assign('COMMENT', $rcmModel->getComment());

		$this->preProcess($request);
		$viewer->view('RelatedCommentModal.tpl', $moduleName);
		$this->postProcess($request);
	}
}
