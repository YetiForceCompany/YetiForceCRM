<?php

/**
 * OSSTimeControl InRelation view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSTimeControl_InRelation_View extends Vtiger_RelatedList_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$relatedModuleName = $request->getByType('relatedModule', 2);
		$parentId = $request->getInteger('record');
		$cvId = $request->isEmpty('cvId', true) ? 0 : $request->getByType('cvId', 'Alnum');
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $request->getInteger('relationId'), $cvId);
		$relatedModuleModel = $relationListView->getRelationModel()->getRelationModuleModel();

		$viewer = $this->getViewer($request);
		$viewer->assign('RELATED_SUMMARY', $relatedModuleModel->getRelatedSummary($relationListView->getRelationQuery()));
		$viewer->assign('RELATED_MODULE_NAME', $relatedModuleName);
		$viewer->view('RelatedSummary.tpl', $relatedModuleName);

		return parent::process($request);
	}
}
