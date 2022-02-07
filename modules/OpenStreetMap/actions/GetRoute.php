<?php

/**
 * Action to get markers.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	public function checkPermission(App\Request $request)
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
	public function process(App\Request $request)
	{
		$response = new Vtiger_Response();
		try {
			$ilon = $request->getByType('ilon', 'float');
			$ilat = $request->getByType('ilat', 'float');
			$routingConnector = \App\Map\Routing::getInstance();
			$routingConnector->setStart($request->getByType('flat', 'float'), $request->getByType('flon', 'float'));
			if (!empty($ilon) && !empty($ilat)) {
				foreach ($ilon as $key => $lon) {
					$routingConnector->addIndirectPoint($ilat[$key], $lon);
				}
			}
			$routingConnector->setEnd($request->getByType('tlat', 'float'), $request->getByType('tlon', 'float'));
			$routingConnector->calculate();
			$response->setResult([
				'geoJson' => $routingConnector->getGeoJson(),
				'properties' => [
					'description' => App\Purifier::purifyHtml($routingConnector->getDescription()),
					'traveltime' => $routingConnector->getTravelTime(),
					'distance' => $routingConnector->getDistance(),
				],
			]);
		} catch (\Throwable $th) {
			\App\Log::error($th->getMessage(), __CLASS__);
			$response->setException($th);
		}
		$response->emit();
	}
}
