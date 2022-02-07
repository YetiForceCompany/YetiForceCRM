<?php

/**
 * Conflict of interest action file.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Conflict of interest action class.
 */
class Vtiger_InterestsConflict_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('confirmation');
		$this->exposeMethod('unlock');
		$this->exposeMethod('usersCancel');
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		switch ($request->getMode()) {
			case 'usersCancel':
				if (!\App\Privilege::isPermitted($request->getModule(), 'InterestsConflictUsers') || !\App\Privilege::isPermitted($request->getByType('sourceModuleName', 'Alnum'), 'DetailView', $request->getInteger('sourceRecord'))) {
					throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
				}
				break;
		}
	}

	/**
	 * Cancel user confirmation.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function usersCancel(App\Request $request): void
	{
		$sourceRecord = $request->getInteger('sourceRecord');
		$baseRecord = $request->getInteger('baseRecord');
		$responseMessage = '';
		$responseType = 'success';
		if ($parent = \App\Components\InterestsConflict::getParent($sourceRecord, $request->getModule())) {
			if ($parent['id'] !== $baseRecord || $parent['moduleName'] !== $request->getByType('baseModuleName', 'Alnum')) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
			\App\Components\InterestsConflict::setCancel($request->getInteger('id'), $baseRecord, $request->getByType('comment', 'Text'));
			$responseMessage = 'LBL_INTERESTS_CONFLICT_UNLOCK_RESP';
		} else {
			$responseMessage = 'LBL_RELATION_NOT_FOUND';
			$responseType = 'error';
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate($responseMessage),
			'type' => $responseType,
		]);
		$response->emit();
	}

	/**
	 * Unlock access request.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function unlock(App\Request $request): void
	{
		$sourceRecord = $request->getInteger('sourceRecord');
		$baseRecord = $request->getInteger('baseRecord');
		$responseType = 'success';
		if ($parent = \App\Components\InterestsConflict::getParent($sourceRecord, $request->getModule())) {
			if ($parent['id'] !== $baseRecord || $parent['moduleName'] !== $request->getByType('baseModuleName', 'Alnum')) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
			\App\Components\InterestsConflict::unlock($baseRecord, $sourceRecord, $request->getByType('comment', 'Text'));
			$responseMessage = 'LBL_SENT';
		} else {
			$responseMessage = 'LBL_RELATION_NOT_FOUND';
			$responseType = 'error';
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate($responseMessage),
			'type' => $responseType,
		]);
		$response->emit();
	}

	/**
	 * Confirmation request.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function confirmation(App\Request $request): void
	{
		$sourceRecord = $request->getInteger('sourceRecord');
		$baseRecord = $request->getInteger('baseRecord');
		$responseType = 'success';
		if ($parent = \App\Components\InterestsConflict::getParent($sourceRecord, $request->getModule())) {
			if ($parent['id'] !== $baseRecord || $parent['moduleName'] !== $request->getByType('baseModuleName', 'Alnum')) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
			$value = $request->getInteger('value');
			if ($value > 1) {
				throw new \App\Exceptions\IllegalValue("ERR_NOT_ALLOWED_VALUE||value||$value", 406);
			}
			\App\Components\InterestsConflict::confirmation($baseRecord, $sourceRecord, $value);
			$responseMessage = 'LBL_SENT';
		} else {
			$responseMessage = 'LBL_RELATION_NOT_FOUND';
			$responseType = 'error';
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate($responseMessage),
			'type' => $responseType,
		]);
		$response->emit();
	}
}
