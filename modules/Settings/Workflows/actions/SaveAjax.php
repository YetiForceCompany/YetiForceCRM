<?php

class Settings_Workflows_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('sequence');
	}

	/**
	 * Update workflows sequence.
	 *
	 * @param \App\Request $request
	 */
	public function sequence(App\Request $request)
	{
		$workflows = $request->getArray('workflows', \App\Purifier::INTEGER, [], \App\Purifier::INTEGER);
		//$workflowModel = Settings_Workflows_Module_Model::getInstance('Settings:Workflows');
		$pageNumber = $request->getInteger('pageNumber');
		$sortType = $request->getByType('sortType');
		//pageNumber, $
		$helper = new \Settings_Workflows_UpdateSequence_Helper($workflows, $pageNumber, $sortType);
//		$workflowModel->updateSequence($workflows, $pageNumber, $sortType);
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('LBL_CHANGES_SAVED', $request->getModule(false)),
		]);
		$response->emit();
	}
}
