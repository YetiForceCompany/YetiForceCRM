<?php
/**
 * Modal action file responsible for products stocktaking import.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Modal action class responsible for products stocktaking import.
 */
class Products_StocktakingModal_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc}  */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('analyzeFile');
		$this->exposeMethod('compare');
		$this->exposeMethod('import');
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userPrivilegesModel->hasModuleActionPermission($request->getModule(), 'Import') || !$userPrivilegesModel->hasModuleActionPermission($request->getModule(), 'CreateView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Analyze CSV file.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function analyzeFile(App\Request $request): void
	{
		if (empty($_FILES['file']['name'])) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$fileInstance = \App\Fields\File::loadFromRequest($_FILES['file']);
		if (!$fileInstance->validate() || 'csv' !== $fileInstance->getExtension()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		$response = new Vtiger_Response();
		$response->setResult(Products_Stocktaking_Model::load($fileInstance->getPath())->analyzeFile());
		$response->emit();
	}

	/**
	 * Compare stock levels.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function compare(App\Request $request): void
	{
		$response = new Vtiger_Response();
		$response->setResult(Products_Stocktaking_Model::loadByKey($request->getByType('randomKey', \App\Purifier::ALNUM))->compare($request));
		$response->emit();
	}

	/**
	 * Import stock levels.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function import(App\Request $request): void
	{
		$response = new Vtiger_Response();
		$response->setResult(Products_Stocktaking_Model::loadByKey($request->getByType('randomKey', \App\Purifier::ALNUM))->import($request));
		$response->emit();
	}
}
