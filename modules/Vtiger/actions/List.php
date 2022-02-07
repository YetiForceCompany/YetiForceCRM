<?php

/**
 * List records action class.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_List_Action extends Vtiger_Mass_Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('calculate');
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 403);
		}
	}

	/**
	 * Function for calculating values for a list of records.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\Security
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function calculate(App\Request $request)
	{
		$queryGenerator = self::getQuery($request);
		$fieldQueryModel = $queryGenerator->getQueryField($request->getByType('fieldName', 2));
		$fieldModel = $fieldQueryModel->getField();
		if (!$fieldModel->isViewable()) {
			throw new \App\Exceptions\Security('ERR_NO_ACCESS_TO_THE_FIELD', 403);
		}
		if (!$fieldModel->isCalculateField()) {
			throw new \App\Exceptions\Security('LBL_NOT_SUPPORTED_FIELD', 406);
		}
		$columnName = $fieldQueryModel->getColumnName();
		if ('sum' === $request->getByType('calculateType')) {
			$value = $queryGenerator->createQuery()->sum($columnName);
		} else {
			throw new \App\Exceptions\NotAllowedMethod('LBL_PERMISSION_DENIED', 406);
		}
		$response = new Vtiger_Response();
		$response->setResult($fieldModel->getDisplayValue($value));
		$response->emit();
	}
}
