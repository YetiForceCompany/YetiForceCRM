<?php

/**
 * Action to create widget
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WidgetsManagement_CreateChart_Action extends Settings_Vtiger_IndexAjax_View
{

	public function process(Vtiger_Request $request)
	{
		$db = PearDatabase::getInstance();
		$linkId = $request->get('linkId');
		$chartName = $request->get('chartName');
		$blockid = $request->get('blockid');
		$isDefault = $request->get('isDefault');
		$width = $request->get('width');
		$height = $request->get('height');
		$size = \App\Json::encode(['width' => $width, 'height' => $height]);
		$data = \App\Json::encode(['reportId' => $request->get('reportId')]);
		$paramsToInsert = [
			'linkid' => $linkId,
			'blockid' => $blockid,
			'filterid' => 0,
			'title' => $chartName,
			'isdefault' => $isDefault,
			'size' => $size,
			'data' => $data
		];
		$db->insert('vtiger_module_dashboard', $paramsToInsert);
		$id = $db->getLastInsertID();
		$result = [];
		$result['success'] = true;
		$result['widgetId'] = $id;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
