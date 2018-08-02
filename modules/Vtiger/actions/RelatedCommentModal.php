<?php

/**
 * Update comment for related record.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_RelatedCommentModal_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
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
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		$relatedRecord = $request->getByType('relid', 'Alnum');
		$relatedModuleName = $request->getByType('relmodule', 2);

		$rcmModel = Vtiger_RelatedCommentModal_Model::getInstance($record, $moduleName, $relatedRecord, $relatedModuleName);
		if (!$rcmModel->isEditable()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$rcmModel->save($request->get('comment'));

		$response = new Vtiger_Response();
		$response->setResult(\App\Language::translate('LBL_SAVED_RELATION_COMMENT', $moduleName));
		$response->emit();
	}
}
