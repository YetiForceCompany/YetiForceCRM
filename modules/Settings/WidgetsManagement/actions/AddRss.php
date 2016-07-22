<?php

/**
 * Action to create widget
 * @package YetiForce.actions
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WidgetsManagement_AddRss_Action extends Settings_Vtiger_Basic_Action
{

	function process(Vtiger_Request $request)
	{
		$db = PearDatabase::getInstance();
		$linkId = $request->get('linkId');
		$widgetTitle = $request->get('widgetTitle');
		$blockid = $request->get('blockid');
		$isDefault = $request->get('isDefault');
		$width = $request->get('width');
		$height = $request->get('height');
		$size = \includes\utils\Json::encode(['width' => $width, 'height' => $height]);
		$data = \includes\utils\Json::encode(['channels' => $request->get('channelRss')]);
		$paramsToInsert = [
			'linkid' => $linkId,
			'blockid' => $blockid,
			'filterid' => 0,
			'title' => $widgetTitle,
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
