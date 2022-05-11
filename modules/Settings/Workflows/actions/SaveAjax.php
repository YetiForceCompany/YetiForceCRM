<?php

/**
 * Settings Workflows save action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Settings Workflows save action class.
 */
class Settings_Workflows_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('sequenceActions');
		$this->exposeMethod('sequenceTasks');
	}

	/**
	 * Update workflows actions sequence.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function sequenceActions(App\Request $request): void
	{
		$workflowsForSort = $request->getInteger('workflowForSort');
		$workflowBefore = $request->getInteger('workflowBefore');
		$moduleName = $request->getByType('sourceModule');
		Settings_Workflows_Module_Model::updateActionsSequence($workflowsForSort, $workflowBefore, $moduleName);
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('LBL_CHANGES_SAVED', $request->getModule(false)),
		]);
		$response->emit();
	}

	/**
	 * Update workflow tasks sequence.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function sequenceTasks(App\Request $request): void
	{
		$tasks = $request->getArray('tasks', \App\Purifier::INTEGER, [], \App\Purifier::INTEGER);
		Settings_Workflows_Module_Model::updateTasksSequence($tasks);
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('LBL_CHANGES_SAVED', $request->getModule(false)),
		]);
		$response->emit();
	}
}
