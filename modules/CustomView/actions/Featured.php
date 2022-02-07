<?php

/**
 * Custom view featured action.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class CustomView_Featured_Action extends \App\Controller\Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!isset(CustomView_Record_Model::getAll($request->getByType('sorceModuleName', 2))[$request->getInteger('cvid')])) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$addOrRemove = $request->getByType('actions', \App\Purifier::STANDARD);
		$recordModel = CustomView_Record_Model::getInstanceById($request->getInteger('cvid'));
		$member = 'Users:' . \App\User::getCurrentUserId();
		$result = false;
		if ('add' === $addOrRemove) {
			$result = $recordModel->setFeaturedForMember($member);
		} elseif ('remove' === $addOrRemove) {
			$result = $recordModel->removeFeaturedForMember($member);
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
