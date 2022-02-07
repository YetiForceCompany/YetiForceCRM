<?php

/**
 * Time control InRelation view file.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Time control InRelation view class.
 */
class OSSTimeControl_InRelation_View extends Vtiger_RelatedList_View
{
	/** {@inheritdoc} */
	public function loadView()
	{
		$relationListView = $this->viewer->getTemplateVars('VIEW_MODEL');
		$relatedModuleModel = $this->viewer->getTemplateVars('RELATED_MODULE');
		$this->viewer->assign('RELATED_SUMMARY', $relatedModuleModel->getRelatedSummary($relationListView->getRelationQuery()));
		$this->viewer->assign('RELATED_MODULE_NAME', $relatedModuleModel->getName());
		$this->viewer->view('RelatedSummary.tpl', $relatedModuleModel->getName());

		return parent::loadView();
	}
}
