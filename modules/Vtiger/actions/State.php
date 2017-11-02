<?php

class Vtiger_State_Action extends Vtiger_Action_Controller
{

	/**
	 * Record model instance
	 * @var Vtiger_Record_Model
	 */
	protected $record = false;

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$this->record = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		if ($request->getByType('state') === 'Archived' && !$this->record->privilegeToArchive()) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if ($request->getByType('state') === 'Deleted' && !$this->record->isDeletable()) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if ($request->getByType('state') === 'Active' && !$this->record->privilegeToActivate()) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Main process
	 * @param \App\Request $request
	 * @return \Vtiger_Response
	 */
	public function process(\App\Request $request)
	{


		header("Location: index.php?module={$request->getModule()}&view=Detail&record={$request->getInteger('record')}");
	}
}
