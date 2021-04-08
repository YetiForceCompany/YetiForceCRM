<?php
/**
 * The file contains: Get record detail class.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license	YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author	Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author	Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author	Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Get record detail class.
 */
class Record extends \Api\Core\BaseAction
{
	/**
	 * {@inheritdoc}
	 */
	public $allowedMethod = ['GET', 'DELETE', 'PUT', 'POST'];
	/**
	 * {@inheritdoc}
	 */
	public $allowedHeaders = ['x-parent-id'];
	/**
	 * Record model.
	 *
	 * @var \Vtiger_Record_Model
	 */
	public $recordModel;

	/**
	 * Check permission to method.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return bool
	 */
	public function checkPermission()
	{
		parent::checkPermission();
		$moduleName = $this->controller->request->getModule();
		$method = $this->controller->method;
		if ('POST' === $method) {
			$this->recordModel = \Vtiger_Record_Model::getCleanInstance($moduleName);
			if (!$this->recordModel->isCreateable()) {
				throw new \Api\Core\Exception('No permissions to create record', 403);
			}
		} else {
			if ($this->controller->request->isEmpty('record', true) || !\App\Record::isExists($this->controller->request->getInteger('record'), $moduleName)) {
				throw new \Api\Core\Exception('Record doesn\'t exist', 404);
			}
			$this->recordModel = \Vtiger_Record_Model::getInstanceById($this->controller->request->getInteger('record'), $moduleName);
			switch ($method) {
				case 'DELETE':
					if (!$this->recordModel->privilegeToMoveToTrash()) {
						throw new \Api\Core\Exception('No permissions to remove record', 403);
					}
					break;
				case 'GET':
					if (!$this->recordModel->isViewable()) {
						throw new \Api\Core\Exception('No permissions to view record', 403);
					}
					break;
				case 'PUT':
					if (!$this->recordModel->isEditable()) {
						throw new \Api\Core\Exception('No permissions to edit record', 403);
					}
					break;
				default:
					break;
			}
		}
	}

	/**
	 * Get record detail.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/{moduleName}/Record/{recordId}",
	 *		summary="Get data for the record",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : "", "token" : ""}
	 *		},
	 *		@OA\RequestBody(
	 *			required=false,
	 *			description="The content of the request is empty.",
	 *		),
	 *		@OA\Parameter(
	 *			name="moduleName",
	 *			description="Module name",
	 *			@OA\Schema(type="string"),
	 *			in="path",
	 *			example="Contacts",
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="recordId",
	 *			description="Record id",
	 *			@OA\Schema(type="integer"),
	 *			in="path",
	 *			example=116,
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 *			@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *		),
	 *		@OA\Parameter(
	 *			name="x-raw-data",
	 *			description="Gets raw data",
	 *			@OA\Schema(type="integer", enum={0, 1}),
	 *			in="header",
	 *			example=1,
	 *			required=false
	 *		),
	 *		@OA\Parameter(
	 *			name="x-parent-id",
	 *			description="Gets parent id",
	 *			@OA\Schema(type="integer"),
	 *			in="header",
	 *			required=false
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Gets data for the record",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Get_Record_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Get_Record_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="No permissions to remove record OR No permissions to view record OR No permissions to edit record"
	 *		),
	 *		@OA\Response(
	 *			response=404,
	 *			description="Record doesn't exist"
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_Get_Record_Response",
	 *		title="Base module - Response body for Record",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={0, 1},
	 *			type="integer",
	 *        	example=1
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Record data",
	 *			type="object",
	 *			@OA\Property(property="name", description="Record name", type="string", example="Driving school"),
	 *			@OA\Property(property="id", description="Record Id", type="integer", example=152),
	 *			@OA\Property(
	 * 					property="fields",
	 *					description="Field name items",
	 *					type="object",
	 *			),
	 *			@OA\Property(
	 *					property="data",
	 *					description="Record data",
	 *					type="object",
	 *			),
	 *			@OA\Property(
	 *					property="privileges",
	 *					description="Parameters determining checking of editing rights and moving to the trash",
	 * 					type="object",
	 *					@OA\Property(property="isEditable", description="Check if record is editable", type="boolean", example=true),
	 *					@OA\Property(property="moveToTrash", description="Permission to delete", type="boolean", example=false),
	 *				),
	 *			@OA\Property(
	 *					property="inventory",
	 *					description="Value inventory data",
	 * 					type="object",
	 *				),
	 *			@OA\Property(
	 *					property="summaryInventory",
	 *					description="Value summary inventory data",
	 * 					type="object",
	 *				),
	 *			@OA\Property(property="rawData", description="Raw record data", type="object"),
	 *			@OA\Property(property="rawInventory", description="Inventory data", type="object"),
	 *		),
	 * ),
	 * @OA\Tag(
	 *		name="BaseModule",
	 *		description="Access to record methods"
	 * )
	 */
	public function get(): array
	{
		$moduleName = $this->controller->request->get('module');
		$rawData = $this->recordModel->getData();

		$displayData = $fieldsLabel = [];
		foreach ($this->recordModel->getModule()->getFields() as $fieldModel) {
			if (!$fieldModel->isActiveField()) {
				continue;
			}
			$uiTypeModel = $fieldModel->getUITypeModel();
			$value = $this->recordModel->get($fieldModel->getName());
			$displayData[$fieldModel->getName()] = $uiTypeModel->getApiDisplayValue($value, $this->recordModel);
			$fieldsLabel[$fieldModel->getName()] = \App\Language::translate($fieldModel->get('label'), $moduleName);
			if ($fieldModel->isReferenceField()) {
				$referenceModule = $uiTypeModel->getReferenceModule($value);
				$rawData[$fieldModel->getName() . '_module'] = $referenceModule ? $referenceModule->getName() : null;
			}
			if ('taxes' === $fieldModel->getFieldDataType()) {
				$rawData[$fieldModel->getName() . '_info'] = \Vtiger_Taxes_UIType::getValues($rawData[$fieldModel->getName()]);
			}
		}
		$response = [
			'name' => $this->recordModel->getName(),
			'id' => $this->recordModel->getId(),
			'fields' => $fieldsLabel,
			'data' => $displayData,
			'privileges' => [
				'isEditable' => $this->recordModel->isEditable(),
				'moveToTrash' => $this->recordModel->privilegeToDelete()
			]
		];
		if ($this->recordModel->getModule()->isInventory()) {
			$rawInventory = $this->recordModel->getInventoryData();
			$inventory = $summaryInventory = [];
			$inventoryModel = \Vtiger_Inventory_Model::getInstance($moduleName);
			$inventoryFields = $inventoryModel->getFields();
			foreach ($rawInventory as $row) {
				$inventoryRow = [];
				foreach ($inventoryFields as $name => $field) {
					$inventoryRow[$name] = $field->getDisplayValue($row[$name], $row, true);
				}
				$inventory[] = $inventoryRow;
			}
			foreach ($inventoryFields as $name => $field) {
				if ($field->isSummary()) {
					$summaryInventory[$name] = \CurrencyField::convertToUserFormat($field->getSummaryValuesFromData($rawInventory), null, true);
				}
			}
			$response['inventory'] = $inventory;
			$response['summaryInventory'] = $summaryInventory;
		}

		if (1 === (int) $this->controller->headers['x-raw-data']??0) {
			$response['rawData'] = $rawData;
			if ($rawInventory) {
				$response['rawInventory'] = $rawInventory;
			}
		}
		return $response;
	}

	/**
	 * Delete record.
	 *
	 * @return bool
	 *
	 * @OA\Delete(
	 *		path="/webservice/{moduleName}/Record/{recordId}",
	 *		summary="Delete record (move to the trash)",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : "", "token" : ""}
	 *		},
	 *		@OA\RequestBody(
	 *			required=false,
	 *			description="The content of the request is empty.",
	 *		),
	 *		@OA\Parameter(
	 *			name="moduleName",
	 *			description="Module name",
	 *			@OA\Schema(type="string"),
	 *			in="path",
	 *			example="Contacts",
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="recordId",
	 *			description="Record id",
	 *			@OA\Schema(type="integer"),
	 *			in="path",
	 *			example=116,
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 *			@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="List of records moved to the trash",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Delete_Record_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Delete_Record_Response"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_Delete_Record_Response",
	 *		title="Base module - Transfer to the trash",
	 *		description="List of records moved to the trash",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={"0", "1"},
	 * 			type="integer",
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Status of successful transfer of the record to the recycle bin",
	 *			type="boolean",
	 *		),
	 * ),
	 */
	public function delete(): bool
	{
		$this->recordModel->changeState('Trash');
		return true;
	}

	/**
	 * Edit record.
	 *
	 * @return array
	 *
	 * @OA\Put(
	 *		path="/webservice/{moduleName}/Record/{recordId}",
	 *		summary="Edit record",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : "", "token" : ""}
	 *		},
	 *		@OA\RequestBody(
	 *			required=true,
	 *			description="Contents of the request contains an associative array with the data record.",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Record_Request"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Record_Request"),
	 *		),
	 *		@OA\Parameter(
	 *			name="moduleName",
	 *			description="Module name",
	 *			@OA\Schema(type="string"),
	 *			in="path",
	 *			example="Contacts",
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="recordId",
	 *			description="Record id",
	 *			@OA\Schema(type="integer"),
	 *			in="path",
	 *			example=116,
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 * 			in="header",
	 *			required=true,
	 *			@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Contents of the response contains only id",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Put_Record_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Put_Record_Response"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_Put_Record_Response",
	 *		title="Base module - Response body for Record",
	 *		description="Contents of the response contains only id",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={"0", "1"},
	 *			type="integer",
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			title="Gets data for the record",
	 *			description="Updated record id.",
	 *			type="object",
	 *			@OA\Property(property="id", description="Id of the newly created record", type="integer", example=22),
	 *			@OA\Property(property="skippedData", description="List of parameters passed in the request that were skipped in the write process", type="object"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_Record_Request",
	 *		title="Base module - Request body for record create or update",
	 *		description="Contents of the request contains an associative array with the data record.",
	 *		type="object",
	 *		example={"firstname" : "Tom", "lastname" : "Kowalski"},
	 * ),
	 */
	public function put()
	{
		return $this->post();
	}

	/**
	 * Create record.
	 *
	 * @return array
	 *
	 * @OA\Post(
	 *		path="/webservice/{moduleName}/Record",
	 *		summary="Create record",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : "", "token" : ""}
	 *		},
	 *		@OA\RequestBody(
	 *			required=true,
	 *			description="Contents of the request contains an associative array with the data record.",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Record_Request"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Record_Request"),
	 *		),
	 *		@OA\Parameter(
	 *			name="moduleName",
	 *			description="Module name",
	 *			@OA\Schema(type="string"),
	 *			in="path",
	 *			example="Contacts",
	 *			required=true
	 *		),
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 *			@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Contents of the response contains only id",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Post_Record_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Post_Record_Response"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_Post_Record_Response",
	 *		title="Base module - Created records",
	 *		description="Contents of the response contains only id",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={"0", "1"},
	 *			type="integer",
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			title="Gets data for the record",
	 *			description="Created record id.",
	 *			type="object",
	 *			@OA\Property(property="id", description="Id of the newly created record", type="integer", example=22),
	 *			@OA\Property(property="skippedData", description="List of parameters passed in the request that were skipped in the write process", type="object"),
	 *		),
	 * ),
	 */
	public function post()
	{
		$saveModel = new \Api\Portal\Save();
		$saveModel->init($this);
		$saveModel->saveRecord($this->controller->request);
		$return = ['id' => $this->recordModel->getId()];
		if ($saveModel->skippedData) {
			$return['skippedData'] = $saveModel->skippedData;
		}
		return  $return;
	}
}
