<?php

/**
 * Action to get markers
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMap_GetRoute_Action extends Vtiger_BasicAjax_Action
{

	public function process(Vtiger_Request $request)
	{
		$data = $request->get('coordinates');
		$flon = $request->get('flon');
		$flat = $request->get('flat');
		$tlon = $request->get('tlon');
		$tlat = $request->get('tlat');
		$language = vglobal('default_language');
		$url = AppConfig::module('OpenStreetMap', 'ADDRESS_TO_ROUTE') . "?format=geojson&flat=$flat&flon=$flon&tlat=$tlat&tlon=$tlon&lang=$language&instructions=1";
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 3,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
		]);
		$json = curl_exec($curl);
		curl_close($curl);
		$response = new Vtiger_Response();
		$response->setResult($json);
		$response->setEmitType(Vtiger_Response::$EMIT_RAW);
		$response->emit();
	}
}
