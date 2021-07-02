<?php
/**
 * Portal container - Get dashboard widgets file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Portal container - Get dashboard widgets class.
 */
class Dashboard extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];

	/**
	 * Get widgets.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/Portal/{moduleName}/Dashboard",
	 *		summary="Get dashboard widgets",
	 *		description="Supported widget types: Mini List , Chart Filter",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(response=200, description="Privileges details",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseAction_Dashboard_ResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseAction_Dashboard_ResponseBody"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseAction_Dashboard_ResponseBody",
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
		if ($this->controller->request->isEmpty('record', true)) {
			$dashBoardId = \Settings_WidgetsManagement_Module_Model::getDefaultDashboard();
		} else {
			$dashBoardId = $this->controller->request->getInteger('record');
		}
		$dashboardInstance = \Api\Portal\Dashboard::getInstance($moduleName, $dashBoardId, $this->controller->app['id']);
		return [
			'types' => $dashboardInstance->getTabs(),
			'widgets' => $dashboardInstance->getData(),
		];
	}
}
