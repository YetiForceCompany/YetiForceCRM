<?php
/**
 * Workflows sort actions modal file.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Workflows sort actions modal class.
 */
class Settings_Workflows_SortActionsModal_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_SORT_WORKFLOW_ACTIONS';

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-sort';

	/** {@inheritdoc} */
	public $showFooter = true;

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		$sourceModule = $request->getByType('sourceModule', \App\Purifier::STANDARD);
		return App\Language::translate($sourceModule, $sourceModule) . ' - ' . App\Language::translate('LBL_SORT_WORKFLOW_ACTIONS', $request->getModule(false));
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$sourceModule = $request->getByType('sourceModule', \App\Purifier::STANDARD);
		$moduleWorkflowActions = Settings_Workflows_Module_Model::getWorkflowActionsForModule($sourceModule);
		$viewer->assign('WORKFLOW_ACTIONS', $moduleWorkflowActions);
		$viewer->view('SortActionsModal.tpl', $request->getModule(false));
	}
}
