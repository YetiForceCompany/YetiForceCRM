<?php

class Settings_Workflows_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('sequence');
		$this->exposeMethod('sequenceTasks');
	}

	/**
	 * Update workflows actions sequence.
	 *
	 * @param \App\Request $request
	 */
	public function sequence(App\Request $request)
	{
		$workflows = $request->getArray('workflows', \App\Purifier::INTEGER, [], \App\Purifier::INTEGER);
		$pageNumber = $request->getInteger('pageNumber');
		$sortType = $request->getByType('sortType');
		new \Settings_Workflows_UpdateSequence_Helper($workflows, $pageNumber, $sortType);
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
	 */
	public function sequenceTasks(App\Request $request)
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
