<?php

/**
 * Gets list of records.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\ManageConsents\BaseModule;

/**
 * RecordsList class.
 */
class RecordsList extends \Api\ManageConsents\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];

	/**
	 * Gets consents.
	 *
	 * @return array
	 *
	 * @OA\GET(
	 *		path="/webservice/Approvals/RecordsList",
	 *		summary="Gets the list of consents",
	 *		tags={"Approvals"},
	 *    security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : "", "token" : ""}
	 *    },
	 *		@OA\RequestBody(
	 *				required=false,
	 *				description="The content of the request is empty",
	 *		),
	 *		@OA\Parameter(
	 *				name="x-row-limit",
	 *  		 	description="Limit",
	 *  		 	@OA\Schema(
	 *  		  		type="integer",
	 *  		 			format="int64",
	 *  		 ),
	 *  		 in="header",
	 * 			 example=0,
	 *  		 required=false
	 * 		),
	 *		@OA\Parameter(
	 *		  	name="x-row-offset",
	 * 		  	description="Offset",
	 * 		  	@OA\Schema(
	 * 		    		type="integer",
	 * 		    		format="int64",
	 * 		  	),
	 *  		 	in="header",
	 * 			 	example=0,
	 *  		 	required=false
	 * 		),
	 *		@OA\Parameter(
	 *		   	name="x-raw-data",
	 * 		  	description="Gets raw data",
	 * 		  	@OA\Schema(
	 * 		    	type="integer",
	 * 		    	format="int64",
	 * 		  	),
	 *  		 	in="header",
	 * 			 	example=1,
	 *  		 	required=false
	 * 		),
	 *		@OA\Response(
	 *				response=200,
	 *				description="List of consents",
	 *				@OA\JsonContent(ref="#/components/schemas/ConsentsResponseBody"),
	 *				@OA\MediaType(
	 *						mediaType="text/html",
	 *						@OA\Schema(ref="#/components/schemas/ConsentsResponseBody")
	 *				),
	 *		),
	 *		@OA\Response(
	 *				response=401,
	 *				description="No sent token OR Invalid token",
	 *		),
	 *		@OA\Response(
	 *				response=403,
	 *				description="No permissions for module",
	 *		),
	 *		@OA\Response(
	 *				response=405,
	 *				description="Method Not Allowed",
	 *		),
	 * ),
	 * @OA\SecurityScheme(
	 *		securityScheme="basicAuth",
	 *		type="http",
	 *    in="header",
	 *		scheme="basic"
	 * ),
	 * @OA\SecurityScheme(
	 *		securityScheme="ApiKeyAuth",
	 *   	type="apiKey",
	 *    in="header",
	 * 		name="X-API-KEY",
	 *   	description="Webservice api key"
	 * ),
	 * @OA\SecurityScheme(
	 *		securityScheme="token",
	 *   	type="apiKey",
	 *    in="header",
	 * 		name="X-TOKEN",
	 *   	description="Webservice api token by user"
	 * ),
	 * @OA\Schema(
	 *		schema="ConsentsResponseBody",
	 *		title="List of consents",
	 *		description="List of obtained consents",
	 *		type="object",
	 *		@OA\Property(
	 *				property="status",
	 *				description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *				enum={0, 1},
	 *				type="integer",
	 *        example=1
	 *		),
	 *		@OA\Property(
	 *				property="result",
	 *				description="Specific response",
	 *				type="object",
	 * 				@OA\Property(
	 * 						property="records",
	 * 						type="object",
	 * 						@OA\Property(
	 * 								property="integer",
	 * 								type="object",
	 * 								@OA\Property(property="id", description="Consent ID", type="integer", example=24862),
	 * 								@OA\Property(property="name", description="Text", type="string", example="Consent for email"),
	 * 								@OA\Property(property="approvals_status", description="Status", type="string", example="Active"),
	 * 								@OA\Property(property="number", description="Text", type="string", example="N12"),
	 * 								@OA\Property(property="assigned_user_id", description="Assigned user name", type="string", example="Kowalski Adam"),
	 *								@OA\Property(property="createdtime", type="string", format="date-time", example="2019-10-07 08:32:38"),
	 *								@OA\Property(property="modifiedtime", type="string", format="date-time", example="2019-10-07 08:32:38"),
	 * 								@OA\Property(property="created_user_id", description="Assigned user name", type="string", example="Kowalski Adam"),
	 * 								@OA\Property(property="shownerid", description="Assigned user name", type="string", example="Kowalski Adam"),
	 * 								@OA\Property(property="description", description="Description", type="string", example="I confirm to have read.."),
	 * 						),
	 * 				),
	 * 				@OA\Property(
	 * 						property="rawData",
	 * 						type="object",
	 * 						@OA\Property(
	 * 								property="integer",
	 * 								type="object",
	 * 								@OA\Property(property="id", description="Consent ID", type="integer", example=24862),
	 * 								@OA\Property(property="name", description="Text", type="string", example="Consent for email"),
	 * 								@OA\Property(property="approvals_status", description="Status", type="string", example="PLL_ACTIVE"),
	 * 								@OA\Property(property="number", description="Text", type="string", example="N12"),
	 * 								@OA\Property(property="assigned_user_id", description="Assigned user ID", type="integer", example=245),
	 *								@OA\Property(property="createdtime", type="string", format="date-time", example="2019-10-07 08:32:38"),
	 *								@OA\Property(property="modifiedtime", type="string", format="date-time", example="2019-10-07 08:32:38"),
	 * 								@OA\Property(property="created_user_id", description="Assigned user ID", type="integer", example=245),
	 * 								@OA\Property(property="shownerid", description="Assigned user name", type="string", example="Kowalski Adam"),
	 * 								@OA\Property(property="description", description="Description", type="string", example="I confirm to have read.."),
	 * 						),
	 * 				),
	 * 				@OA\Property(property="isMorePages", description="There are more entries", type="boolean", example="true"),
	 * 		),
	 *	),
	 */
	public function get()
	{
		$rawData = $records = [];
		$queryGenerator = $this->getQuery();

		$limit = $queryGenerator->getLimit() - 1;
		$moduleModel = $queryGenerator->getModuleModel();
		$fields = [];
		foreach ($moduleModel->getFields() as $fieldModel) {
			if ($fieldModel->isViewable() && $fieldModel->getPermissions()) {
				$fields[] = $fieldModel->getName();
			}
		}
		$queryGenerator->setFields(array_merge(['id'], $fields));
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		$count = $dataReader->count();
		while ($row = $dataReader->read()) {
			$recordModel = $moduleModel->getRecordFromArray($row);
			$records[$recordModel->getId()]['id'] = $recordModel->getId();
			foreach ($fields as $fieldName) {
				$records[$recordModel->getId()][$fieldName] = $recordModel->getDisplayValue($fieldName, $recordModel->getId(), true);
			}
			if ($this->isRawData()) {
				$rawData[$recordModel->getId()] = $row;
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
		return 1 === $this->controller->headers['x-raw-data'];
	}
}
