<?php

/**
 * Action to create widget.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WidgetsManagement_CreateChart_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Main process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$db = App\Db::getInstance();
		$db->createCommand()->insert('vtiger_module_dashboard', [
			'linkid' => $request->getInteger('linkId'),
			'blockid' => $request->getInteger('blockid'),
			'filterid' => 0,
			'title' => $request->get('chartName'),
			'isdefault' => $request->get('isDefault'),
			'size' => \App\Json::encode(['width' => $request->getInteger('width'), 'height' => $request->getInteger('height')]),
			'data' => \App\Json::encode(['reportId' => $request->getInteger('reportId')]),
		])->execute();
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'widgetId' => $db->getLastInsertID('vtiger_module_dashboard_id_seq'),
		]);
		$response->emit();
	}
}
