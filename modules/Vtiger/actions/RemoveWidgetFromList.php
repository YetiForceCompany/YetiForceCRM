<?php

/**
 * RemoveWidgetFromList Class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Koń  <a.kon@yetiforce.com>
 */
class Vtiger_RemoveWidgetFromList_Action extends Vtiger_IndexAjax_View
{

	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if ($request->has('id')) {
			$id = $request->getInteger('id');
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
