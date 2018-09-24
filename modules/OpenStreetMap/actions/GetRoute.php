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

	/**
	 * Main process.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool|void
	 */
	public function process(\App\Request $request)
	{
		$ilon = $request->getByType('ilon', 'Version');
		$ilat = $request->getByType('ilat', 'Version');
		$routeConnector = \App\Map\Route::getInstance();
		$routeConnector->setStart($request->getByType('flat', 'Version'), $request->getByType('flon', 'Version'));
		if (!empty($ilon) && !empty($ilat)) {
			foreach ($ilon as $key => $lon) {
				$routeConnector->addIndirectPoint($ilat[$key], $lon);
			}
		}
		$routeConnector->setEnd($request->getByType('tlat', 'Version'), $request->getByType('tlon', 'Version'));
		$routeConnector->calculate();
		$response = new Vtiger_Response();
		$response->setResult([
			'geoJson' => $routeConnector->getGeoJson(),
			'properties' => [
				'description' => App\Purifier::purifyHtml($routeConnector->getDescription()),
				'traveltime' => $routeConnector->getTravelTime(),
				'distance' => $routeConnector->getDistance(),
			],
		]);
		$response->emit();
	}
}
