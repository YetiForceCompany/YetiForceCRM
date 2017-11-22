<?php

/**
 * List records action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_List_Action extends Vtiger_Mass_Action
{

	/**
	 * {@inheritDoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('calculate');
	}

	/**
	 * {@inheritDoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function to add relation for specified source record id and related record id list
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function calculate(\App\Request $request)
	{
		$queryGenerator = self::getQuery($request);
		$fieldQueryModel = $queryGenerator->getQueryField($request->getByType('fieldName', 2));
		$fieldModel = $fieldQueryModel->getField();
		if (!$fieldModel->isCalculateField()) {
			throw new \App\Exceptions\Security('LBL_NOT_SUPPORTED_FIELD', 406);
		}
		$columnName = $fieldQueryModel->getColumnName();
		switch ($request->getByType('calculateType')) {
			case 'sum':
				$value = $queryGenerator->createQuery()->sum($columnName);
				break;
			default:
				throw new \App\Exceptions\NotAllowedMethod('LBL_PERMISSION_DENIED', 406);
				break;
		}
		$response = new Vtiger_Response();
		$response->setResult($fieldModel->getDisplayValue($value));
		$response->emit();
	}
}
