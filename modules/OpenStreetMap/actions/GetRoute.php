<?php

/**
 * Action to get markers.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OpenStreetMap_GetRoute_Action extends Vtiger_BasicAjax_Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function process(\App\Request $request)
	{
		$flon = $request->get('flon');
		$flat = $request->get('flat');
		$tlon = $request->get('tlon');
		$tlat = $request->get('tlat');
		$ilon = $request->get('ilon');
		$ilat = $request->get('ilat');

		$track = [];
		$startLat = $flat;
		$startLon = $flon;
		if (!empty($ilon)) {
			foreach ($ilon as $key => $tempLon) {
				if (!empty($tempLon)) {
					$endLon = $ilon[$key];
					$endLat = $ilat[$key];
					$tracks[] = [
						'startLat' => $startLat,
						'startLon' => $startLon,
						'endLat' => $endLat,
						'endLon' => $endLon,
					];
					$startLat = $endLat;
					$startLon = $endLon;
				}
			}
		}
		$tracks[] = [
			'startLat' => $startLat,
			'startLon' => $startLon,
			'endLat' => $tlat,
			'endLon' => $tlon,
		];
		$coordinates = [];
		$travel = 0;
		$description = '';
		$urlToRoute = AppConfig::module('OpenStreetMap', 'ADDRESS_TO_ROUTE');
		try {
			foreach ($tracks as $track) {
				$url = $urlToRoute . '?format=geojson&flat=' . $track['startLat'] . '&flon=' . $track['startLon'] . '&tlat=' . $track['endLat'] . '&tlon=' . $track['endLon'] . '&lang=' . App\Language::getLanguage() . '&instructions=1';
				$response = Requests::get($url);
				$json = \App\Json::decode($response->body);
				$coordinates = array_merge($coordinates, $json['coordinates']);
				$description .= $json['properties']['description'];
				$travel = $travel + $json['properties']['traveltime'];
				$distance = $distance + $json['properties']['distance'];
			}
			$result = [
				'type' => 'LineString',
				'coordinates' => $coordinates,
				'properties' => [
					'description' => $description,
					'traveltime' => $travel,
					'distance' => $distance,
				],
			];
		} catch (Exception $ex) {
			\App\Log::warning($ex->getMessage());
			$result = false;
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
