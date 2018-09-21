<?php

/**
 * Record state action class.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Vtiger_State_Action.
 */
class Vtiger_State_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;
	/**
	 * Record model instance.
	 *
	 * @var Vtiger_Record_Model
	 */
	protected $record;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('open');
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$this->record = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		if (!(($request->getByType('state') === 'Archived' && $this->record->privilegeToArchive()) ||
			($request->getByType('state') === 'Trash' && $this->record->privilegeToMoveToTrash()) ||
			($request->getByType('state') === 'Active' && $this->record->privilegeToActivate()) ||
			($request->getMode() === 'open' && !$this->record->checkLockFields() && $this->record->isPermitted('OpenRecord') && $this->record->isPermitted('EditView')))
		) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		if ($mode = $request->getMode()) {
			$this->invokeExposedMethod($mode, $request);
		} else {
			$this->record->changeState($request->getByType('state'));
		}
		$response = new Vtiger_Response();
		if ($request->getByType('sourceView') === 'List') {
			$response->setResult(['notify' => ['type' => 'success', 'text' => \App\Language::translate('LBL_CHANGES_SAVED')]]);
		} else {
			$response->setResult($this->record->getDetailViewUrl());
		}
		$response->emit();
	}

	/**
	 * Function to open record.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function open(\App\Request $request)
	{
		$lockFields = array_merge_recursive(
			$this->record->getEntity()->getLockFields(),
			\App\Fields\Picklist::getCloseStates($this->record->getModule()->getId())
		);
		foreach ($lockFields as $fieldName => $values) {
			if ($request->has($fieldName)) {
				$this->record->getField($fieldName)->getUITypeModel()->setValueFromRequest($request, $this->record);
				if (in_array($this->record->get($fieldName), $values)) {
					throw new \App\Exceptions\NoPermitted('ERR_ILLEGAL_VALUE', 406);
				}
			}
		}
		if (!$this->record->getPreviousValue()) {
			throw new \App\Exceptions\NoPermitted('ERR_ILLEGAL_VALUE', 406);
		}
		$this->record->save();
	}
}
