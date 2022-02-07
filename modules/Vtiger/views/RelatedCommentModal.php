<?php

/**
 * Update comment for related record.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_RelatedCommentModal_View extends Vtiger_BasicModal_View
{
	/**
	 * Checking permissions.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$relatedRecord = $request->getByType('relid', 'Alnum');

		if (!$recordId || !$relatedRecord || !\App\Privilege::isPermitted($request->getModule(), 'DetailView', $recordId) || (is_numeric($relatedRecord) && !\App\Privilege::isPermitted($request->getByType('relmodule', 2), 'DetailView', $relatedRecord))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		$relatedRecord = $request->getByType('relid', 'Alnum');
		$relatedModuleName = $request->getByType('relmodule', 2);

		$relatedCommentModal = Vtiger_RelatedCommentModal_Model::getInstance($record, $moduleName, $relatedRecord, $relatedModuleName);
		if (!$relatedCommentModal->isEditable()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RELATED_RECORD', $relatedRecord);
		$viewer->assign('RELATED_MODULE', $relatedModuleName);
		$viewer->assign('COMMENT', $relatedCommentModal->getComment());

		$this->preProcess($request);
		$viewer->view('RelatedCommentModal.tpl', $moduleName);
		$this->postProcess($request);
	}
}
