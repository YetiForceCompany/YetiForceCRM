<?php

/**
 * Custom view featured action.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class CustomView_Featured_Action extends \App\Controller\Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!isset(CustomView_Record_Model::getAll($request->getByType('sorceModuleName', 2))[$request->getInteger('cvid')])) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		CustomView_Record_Model::setFeaturedFilterView($request->getInteger('cvid'), 'Users:' . \App\User::getCurrentUserId(), $request->getByType('actions', 2));
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
