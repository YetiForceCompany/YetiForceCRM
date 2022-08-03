<?php
/**
 * Webservice premium container - Gets widgets' data from the dashboard file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebservicePremium\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Webservice premium container - Gets widgets' data from the dashboard class.
 */
class Dashboard extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/**
	 * Get method - Gets widgets' data from the dashboard.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/WebservicePremium/{moduleName}/Dashboard",
	 *		summary="Gets widgets' data from the dashboard",
	 *		description="Supported widget types: Mini List , Chart Filter",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(response=200, description="Privileges details",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Get_Dashboard_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Get_Dashboard_Response"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_Get_Dashboard_Response",
	 *		title="Base module - Dashboard response schema",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Tabs and widgets data",
	 *			type="object",
	 *			@OA\Property(
	 *				property="types",
	 *				type="object",
	 *				title="Tabs list",
	 *				@OA\AdditionalProperties(
	 *					description="Tab menu item",
	 *					type="object",
	 * 					@OA\Property(property="name", type="string", example="Dashboard"),
	 * 					@OA\Property(property="id", type="integer", example=1),
	 * 					@OA\Property(property="system", type="integer", example=1),
	 * 				),
	 * 			),
	 *			@OA\Property(
	 *				property="widgets",
	 *				type="object",
	 *				title="Tabs list",
	 *				@OA\AdditionalProperties(
	 *					description="Tree item",
	 *					type="object",
	 * 					@OA\Property(property="type", type="string", example="ChartFilter"),
	 * 					@OA\Property(property="data", type="object", title="Widget data"),
	 * 				),
	 * 			),
	 *		),
	 *	),
	 */
	public function get(): array
	{
		$moduleName = $this->controller->request->getModule();
		$dashboardInstance = \Api\WebservicePremium\Dashboard::getInstance($moduleName, 0, $this->controller->app['id']);
		$tabs = $dashboardInstance->getTabs();
		if ($this->controller->request->isEmpty('record', true)) {
			$defaultDbId = \Settings_WidgetsManagement_Module_Model::getDefaultDashboard();
			$dashBoardId = isset($tabs[$defaultDbId]) ? $defaultDbId : (int) array_key_first($tabs);
		} else {
			$dashBoardId = $this->controller->request->getInteger('record');
		}
		$dashboardInstance->setDashboard($dashBoardId);

		return [
			'types' => $tabs,
			'widgets' => $dashboardInstance->getData(),
		];
	}
}
