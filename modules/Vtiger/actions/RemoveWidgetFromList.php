<?php

/**
 * RemoveWidgetFromList Class
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Adrian Koń  <a.kon@yetiforce.com>
 */
class Vtiger_RemoveWidgetFromList_Action extends Vtiger_IndexAjax_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($request->getModule());
		if (!$permission) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
		return true;
	}

	public function process(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if ($request->has('id')) {
			$id = $request->get('id');
			$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($id, $currentUser->getId());
			if (!$widget->isDefault()) {
				Vtiger_Widget_Model::removeWidgetFromList($id);
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}
