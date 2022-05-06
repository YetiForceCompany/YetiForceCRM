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
		$this->exposeMethod('sequence');
		$this->exposeMethod('sequenceTasks');
	}

	/**
	 * Update workflows actions sequence.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function sequence(App\Request $request): void
	{
		$workflows = $request->getArray('workflows', \App\Purifier::INTEGER, [], \App\Purifier::INTEGER);
		$pageNumber = $request->getInteger('pageNumber');
		$sortType = $request->getByType('sortType');
		$moduleName = $request->getByType('sourceModule');
		new \Settings_Workflows_UpdateSequence_Helper($workflows, $pageNumber, $sortType, $moduleName);
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
