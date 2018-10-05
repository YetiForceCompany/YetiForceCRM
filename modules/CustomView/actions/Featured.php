<?php
/**
 * Custom view featured action.
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
		$cvId = $request->getInteger('cvid');
		$recordModel = CustomView_Record_Model::getInstanceById($cvId);
		if (!isset($recordModel->getAll($recordModel->getModule()->get('name'))[$cvId])) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if ($currentUser->isAdminUser()) {
			CustomView_Record_Model::setFeaturedFilterView($request->getInteger('cvid'), 'Users:' . $currentUser->getId(), $request->get('actions'));
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('Saving CustomView', $request->getModule(false)),
		]);
		$response->emit();
	}
}
