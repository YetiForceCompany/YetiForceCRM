<?php

/**
 * Gets list of records.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\ManageConsents\Approvals;

use OpenApi\Annotations as OA;

/**
 * RecordsList class.
 */
class RecordsList extends \Api\ManageConsents\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET'];
	/** {@inheritdoc}  */
	public $allowedHeaders = ['x-raw-data', 'x-row-offset', 'x-row-limit', 'x-condition'];

	/**
	 * Gets consents.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/ManageConsents/Approvals/RecordsList",
	 *		summary="Gets the list of consents",
	 *		tags={"Approvals"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="x-row-limit", in="header", @OA\Schema(type="integer"), description="Get rows limit, default: 1000", required=false, example=1000),
	 *		@OA\Parameter(name="x-row-offset", in="header", @OA\Schema(type="integer"), description="Offset, default: 0", required=false, example=0),
	 *		@OA\Parameter(name="x-raw-data", in="header", @OA\Schema(type="integer", enum={0, 1}), description="Gets raw data", required=false, example=1),
	 *		@OA\Parameter(name="x-condition", in="header", description="Conditions [Json format]", required=false,
	 *			@OA\JsonContent(ref="#/components/schemas/Approvals_Get_RecordsList_Request"),
	 *		),
	 *		@OA\Response(response=200, description="List of consents",
	 *			@OA\JsonContent(ref="#/components/schemas/Approvals_Get_RecordsList_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Approvals_Get_RecordsList_Response"),
	 *		),
	 *		@OA\Response(response=401, description="`No sent token` OR `Invalid token`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=403, description="No permissions for module",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(response=405, description="Method Not Allowed",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="Approvals_Get_RecordsList_Request",
	 *		title="Conditions",
	 *		description="The list is based on fields in the Consent register module. fieldName - Field name, value - Value, operator - Specific operator, group - true/false. ",
	 *		type="object",
	 * 		example={"fieldName" : "approvals_status", "value" : "PLL_ACTIVE", "operator" : "e"}
	 *	),
	 *	@OA\SecurityScheme(
	 *		type="http",
	 *		securityScheme="basicAuth",
	 *		scheme="basic",
	 *   	description="Basic Authentication header"
	 *	),
	 *	@OA\SecurityScheme(
	 * 		name="X-API-KEY",
	 *   	type="apiKey",
	 *    	in="header",
	 *		securityScheme="ApiKeyAuth",
	 *   	description="Webservice api key header"
	 *	),
	 *	@OA\SecurityScheme(
	 * 		name="X-TOKEN",
	 *   	type="apiKey",
	 *   	in="header",
	 *		securityScheme="token",
	 *   	description="Webservice api token by user header"
	 *	),
	 * @OA\Schema(
	 *		schema="Approvals_Get_RecordsList_Response",
	 *		title="List of consents",
	 *		description="List of obtained consents",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *				property="result",
	 *				description="Specific response",
	 *				type="object",
	 * 				@OA\Property(
	 * 						property="records",
	 * 						type="object",
	 * 						@OA\Property(
	 * 								property="24862",
	 * 								type="object",
	 * 								@OA\Property(property="id", description="Consent ID", type="integer", example=24862),
	 * 								@OA\Property(property="name", description="Text", type="string", example="Consent for email"),
	 * 								@OA\Property(property="approvals_status", description="Status", type="string", example="Active"),
	 * 								@OA\Property(property="number", description="Text", type="string", example="N12"),
	 * 								@OA\Property(property="assigned_user_id", description="Assigned user name", type="string", example="Kowalski Adam"),
	 *								@OA\Property(property="createdtime", type="string", example="2019-10-07 08:32:38"),
	 *								@OA\Property(property="modifiedtime", type="string", example="2019-10-07 08:32:38"),
	 * 								@OA\Property(property="created_user_id", description="Assigned user name", type="string", example="Kowalski Adam"),
	 * 								@OA\Property(property="shownerid", description="Assigned user name", type="string", example="Kowalski Adam"),
	 * 								@OA\Property(property="description", description="Description", type="string", example="I confirm to have read.."),
	 * 						),
	 * 				),
	 * 				@OA\Property(
	 * 						property="rawData",
	 * 						type="object",
	 * 						@OA\Property(
	 * 								property="24862",
	 * 								type="object",
	 * 								@OA\Property(property="id", description="Consent ID", type="integer", example=24862),
	 * 								@OA\Property(property="name", description="Text", type="string", example="Consent for email"),
	 * 								@OA\Property(property="approvals_status", description="Status", type="string", example="PLL_ACTIVE"),
	 * 								@OA\Property(property="number", description="Text", type="string", example="N12"),
	 * 								@OA\Property(property="assigned_user_id", description="Assigned user ID", type="integer", example=245),
	 *								@OA\Property(property="createdtime", type="string", example="2019-10-07 08:32:38"),
	 *								@OA\Property(property="modifiedtime", type="string", example="2019-10-07 08:32:38"),
	 * 								@OA\Property(property="created_user_id", description="Assigned user ID", type="integer", example=245),
	 * 								@OA\Property(property="shownerid", description="Assigned user name", type="string", example="Kowalski Adam"),
	 * 								@OA\Property(property="description", description="Description", type="string", example="I confirm to have read.."),
	 * 						),
	 * 				),
	 * 				@OA\Property(property="isMorePages", description="There are more entries", type="boolean", example=true),
	 * 		),
	 *	),
	 *	@OA\Schema(
	 *		schema="Exception",
	 *		title="General - Error exception",
	 *		type="object",
	 *  	@OA\Property(
	 * 			property="status",
	 *			description="0 - error",
	 * 			enum={0},
	 *			type="integer",
	 *			example=0
	 * 		),
	 *		@OA\Property(
	 * 			property="error",
	 *     	 	description="Error  details",
	 *    	 	type="object",
	 *   		@OA\Property(property="message", type="string", example="Invalid method", description="To show more details turn on: config\Debug.php apiShowExceptionMessages = true"),
	 *   		@OA\Property(property="code", type="integer", example=405),
	 *   		@OA\Property(property="file", type="string", example="api\webservice\WebservicePremium\BaseAction\Files.php", description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
	 *   		@OA\Property(property="line", type="integer", example=101, description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
	 * 			@OA\Property(property="backtrace", type="string", example="#0 api\webservice\WebservicePremium\BaseAction\Files.php (101) ....", description="default disabled to enable set: config\Debug.php apiShowExceptionBacktrace = true"),
	 *    	),
	 *	),
	 */
	public function get()
	{
		$rawData = $records = [];
		$showRaw = $this->isRawData();
		$queryGenerator = $this->getQuery();

		$limit = $queryGenerator->getLimit() - 1;
		$moduleModel = $queryGenerator->getModuleModel();
		$fields = [];
		foreach ($moduleModel->getFields() as $fieldModel) {
			if ($fieldModel->isViewable() && $fieldModel->getPermissions()) {
				$fields[$fieldModel->getName()] = $fieldModel;
			}
		}
		$queryGenerator->setFields(array_merge(['id'], array_keys($fields)));
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		$count = $dataReader->count();
		while ($row = $dataReader->read()) {
			$recordModel = $moduleModel->getRecordFromArray($row);
			$records[$recordModel->getId()]['id'] = $recordModel->getId();
			foreach ($fields as $fieldName => $fieldModel) {
				$records[$recordModel->getId()][$fieldName] = $fieldModel->getUITypeModel()->getApiDisplayValue($row[$fieldName], $recordModel);
				if ($showRaw) {
					$rawData[$recordModel->getId()] = $recordModel->getRawValue($fieldName);
				}
			}

			if ($limit === $count) {
				break;
			}
		}
		$dataReader->close();
		return [
			'records' => $records,
			'rawData' => $rawData,
			'isMorePages' => $count > $limit,
		];
	}

	/**
	 * Get query record list.
	 *
	 * @return \App\QueryGenerator
	 */
	public function getQuery()
	{
		$queryGenerator = new \App\QueryGenerator($this->controller->request->getModule());
		$limit = 1000;
		if ($requestLimit = $this->controller->request->getHeader('x-row-limit')) {
			$limit = (int) $requestLimit;
		}
		$offset = 0;
		if ($requestOffset = $this->controller->request->getHeader('x-row-offset')) {
			$offset = (int) $requestOffset;
		}
		if ($conditions = $this->controller->request->getHeader('x-condition')) {
			$conditions = \App\Json::decode($conditions);
			if (isset($conditions['fieldName'])) {
				$queryGenerator->addCondition($conditions['fieldName'], $conditions['value'], $conditions['operator'], $conditions['group'] ?? true, true);
			} else {
				foreach ($conditions as $condition) {
					$queryGenerator->addCondition($condition['fieldName'], $condition['value'], $condition['operator'], $condition['group'] ?? true, true);
				}
			}
		}
		$queryGenerator->setLimit(++$limit);
		$queryGenerator->setOffset($offset);

		return $queryGenerator;
	}

	/**
	 * Check if you send raw data.
	 *
	 * @return bool
	 */
	protected function isRawData(): bool
	{
		return 1 === (int) ($this->controller->headers['x-raw-data'] ?? 0);
	}
}
