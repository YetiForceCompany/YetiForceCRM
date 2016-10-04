<?php

/**
 * Save Application
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WebserviceApps_SaveAjax_Action extends Settings_Vtiger_Index_Action
{

	public function process(Vtiger_Request $request)
	{
		$keyLength = 32;
		$id = $request->get('id');
		$status = $request->get('status');
		$nameServer = $request->get('name');
		$url = $request->get('url');
		$db = PearDatabase::getInstance();
		$pass = $request->get('pass');
		$accounts = $request->get('accounts');
		if (empty($id)) {
			$type = $request->get('type');
			$key = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $keyLength);
			$db->insert('w_yf_servers', [
				'name' => $nameServer,
				'acceptable_url' => $url,
				'api_key' => $key,
				'status' => $status == 'true' ? 1 : 0,
				'type' => $type,
				'pass' => $pass,
				'accounts_id' => $accounts,
			]);
		} else {
			$updates = [
				'status' => $status == 'true' ? 1 : 0,
				'name' => $nameServer,
				'acceptable_url' => $url,
				'pass' => $pass,
				'accounts_id' => $accounts,
			];
			$db->update('w_yf_servers', $updates, 'id = ?', [$id]);
		}
	}
}
