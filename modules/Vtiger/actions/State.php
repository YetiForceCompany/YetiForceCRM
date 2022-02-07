<?php

/**
 * Record state action class.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Vtiger_State_Action.
 */
class Vtiger_State_Action extends \App\Controller\Action
{
	/**
	 * Record model instance.
	 *
	 * @var Vtiger_Record_Model
	 */
	protected $record;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$this->record = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		if (!(('Archived' === $request->getByType('state') && $this->record->privilegeToArchive()) ||
			('Trash' === $request->getByType('state') && $this->record->privilegeToMoveToTrash()) ||
			('Active' === $request->getByType('state') && $this->record->privilegeToActivate()))
		) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$this->record->changeState($request->getByType('state'));
		$response = new Vtiger_Response();
		if ('List' === $request->getByType('sourceView')) {
			$response->setResult(['notify' => ['type' => 'success', 'text' => \App\Language::translate('LBL_CHANGES_SAVED')]]);
		} else {
			$response->setResult($this->record->getDetailViewUrl());
		}
		$response->emit();
	}
}
