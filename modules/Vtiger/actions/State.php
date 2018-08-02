<?php

/**
 * Record state action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_State_Action extends \App\Controller\Action
{
	/**
	 * Record model instance.
	 *
	 * @var Vtiger_Record_Model
	 */
	protected $record;

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$this->record = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		if ($request->getByType('state') === 'Archived' && !$this->record->privilegeToArchive()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if ($request->getByType('state') === 'Trash' && !$this->record->privilegeToMoveToTrash()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if ($request->getByType('state') === 'Active' && !$this->record->privilegeToActivate()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		if (!in_array($request->getByType('state'), ['Active', 'Trash', 'Archived'])) {
			throw new \App\Exceptions\NoPermitted('ERR_ILLEGAL_VALUE', 406);
		}
		$this->record->changeState($request->getByType('state'));
		$response = new Vtiger_Response();
		if ($request->getByType('sourceView') === 'List') {
			$response->setResult(['notify' => ['type' => 'success', 'text' => \App\Language::translate('LBL_CHANGES_SAVED')]]);
		} else {
			$response->setResult("index.php?module={$request->getModule()}&view=Detail&record={$request->getInteger('record')}");
		}
		$response->emit();
	}
}
