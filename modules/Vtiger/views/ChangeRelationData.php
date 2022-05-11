<?php
/**
 * Change relation data modal.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Class ChangeRelationData.
 */
class Vtiger_ChangeRelationData_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $modalSize = '';

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'));
		if (!$recordModel->isEditable() || !Vtiger_Record_Model::getInstanceById($request->getInteger('fromRecord'))->isEditable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		return \App\Language::translate('LBL_CHANGE_RELATION_DATA', $request->getModule());
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		$parentRecordId = $request->getInteger('fromRecord');
		$relationId = $request->getInteger('relationId');
		$cvId = $request->isEmpty('cvId', true) ? 0 : $request->getByType('cvId', 'Alnum');
		$parentRecord = \Vtiger_Record_Model::getInstanceById($parentRecordId);
		$relationView = \Vtiger_RelationListView_Model::getInstance($parentRecord, $moduleName, $relationId, $cvId);
		$relationModel = $relationView->getRelationModel()->getTypeRelationModel();
		$data = $relationModel->getRelationData($parentRecordId, $recordId);
		$fieldModels = [];

		foreach ($relationModel->getFields(true) as $fieldModel) {
			$fieldModel->set('fieldvalue', $data[$fieldModel->getName()] ?? '');
			$fieldModels[$fieldModel->getName()] = $fieldModel;
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('FIELDS', $fieldModels);
		$viewer->assign('RECORD', $recordId);
		$viewer->assign('RELATION_ID', $relationId);
		$viewer->assign('FROM_RECORD', $parentRecord->getId());
		$viewer->view('Modals/ChangeRelationData.tpl', $moduleName);
	}
}
