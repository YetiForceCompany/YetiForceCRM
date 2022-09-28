<?php
/**
 * Conflict of interests index view file.
 *
 * @package   Controller
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Controller\Components\Action;

/**
 * Conflict of interests index view class.
 */
class Pbx extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('performCall');
		$this->exposeMethod('saveCalls');
	}

	/** {@inheritdoc} */
	public function checkPermission(\App\Request $request)
	{
		switch ($request->getMode()) {
			case 'performCall':
			case 'saveCalls':
				if (!\App\Integrations\Pbx::isActive()) {
					throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
				}
				break;
			default:
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
				break;
		}
		return true;
	}

	/**
	 * Perform phone call.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function performCall(\App\Request $request): void
	{
		$pbx = \App\Integrations\Pbx::getDefaultInstance();
		$pbx->loadUserPhone();
		$pbx->performCall($request->getByType('phone', 'Phone'), $request->getInteger('record'));
		$response = new \Vtiger_Response();
		$response->setResult(\App\Language::translate('LBL_PHONE_CALL_SUCCESS'));
		$response->emit();
	}

	/**
	 * Save phone calls.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function saveCalls(\App\Request $request): void
	{
		$pbx = \App\Integrations\Pbx::getDefaultInstance();
		$connector = $pbx->getConnector();
		if (empty($connector)) {
			throw new \App\Exceptions\AppException('No PBX connector found');
		}
		$result = $connector->saveCalls($request);

		$response = new \Vtiger_Response();
		$response->setResult(array_merge(['type' => $pbx->get('type')], $result));
		$response->emit();
	}
}
