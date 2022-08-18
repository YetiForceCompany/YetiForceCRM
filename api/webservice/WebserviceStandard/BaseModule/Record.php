<?php
/**
 * Webservice standard container - Get record detail file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license	YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author	Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author	Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author	Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\WebserviceStandard\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Webservice standard container - Get record detail class.
 */
class Record extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET', 'DELETE', 'PUT', 'POST'];

	/** {@inheritdoc}  */
	public $allowedHeaders = ['x-parent-id', 'x-fields-params'];

	/** @var \Vtiger_Record_Model Record model instance. */
	public $recordModel;

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
		parent::checkPermission();
		$moduleName = $this->controller->request->getModule();
		if ('POST' === $this->controller->method) {
			$this->recordModel = \Vtiger_Record_Model::getCleanInstance($moduleName);
			if (!$this->recordModel->isCreateable()) {
				throw new \Api\Core\Exception('No permissions to create record', 403);
			}
		} else {
			if ($this->controller->request->isEmpty('record')) {
				throw new \Api\Core\Exception('No record id', 404);
			}
			if (!\App\Record::isExists($this->controller->request->getInteger('record'), $moduleName)) {
				throw new \Api\Core\Exception('Record doesn\'t exist', 404);
			}
			$this->recordModel = \Vtiger_Record_Model::getInstanceById($this->controller->request->getInteger('record'), $moduleName);
			switch ($this->controller->method) {
				case 'DELETE':
					if (!$this->recordModel->privilegeToMoveToTrash()) {
						\App\Log::error('Privilege::isPermittedLevel: ' . \App\Privilege::$isPermittedLevel, 'API');
						throw new \Api\Core\Exception('No permissions to remove record', 403);
					}
					break;
				case 'GET':
					if (!$this->recordModel->isViewable()) {
						\App\Log::error('Privilege::isPermittedLevel: ' . \App\Privilege::$isPermittedLevel, 'API');
						throw new \Api\Core\Exception('No permissions to view record', 403);
					}
					break;
				case 'PUT':
					if (!$this->recordModel->isEditable()) {
						\App\Log::error('Privilege::isPermittedLevel: ' . \App\Privilege::$isPermittedLevel, 'API');
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
	 * @api
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/WebserviceStandard/{moduleName}/Record/{recordId}",
	 *		description="Gets the details of a record",
	 *		summary="Data for the record",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		operationId="getRecord",
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="recordId", in="path", @OA\Schema(type="integer"), description="Record id", required=true, example=116),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Parameter(name="x-raw-data", in="header", @OA\Schema(type="integer", enum={0, 1}), description="Gets raw data", required=false, example=1),
	 *		@OA\Parameter(name="x-parent-id", in="header", @OA\Schema(type="integer"), description="Parent record id", required=false, example=5),
	 * 		@OA\Parameter(name="x-fields-params", in="header", description="JSON array - list of fields to be returned in the specified way", required=false,
	 *			@OA\JsonContent(ref="#/components/schemas/Fields-Settings"),
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Gets data for the record",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Get_Record_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Get_Record_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="`No permissions to remove record` OR `No permissions to view record` OR `No permissions to edit record`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=404,
	 *			description="`No record id` OR `Record doesn't exist`",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="Fields-Settings",
	 *		title="Custom field settings",
	 *		description="A list of custom parameters that can affect the return value of a given field.",
	 *		type="object",
	 * 		example={"password" : {"showHiddenData" : true}}
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_Get_Record_Response",
	 *		title="Base module - Response body for Record",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			title="Record data",
	 *			type="object",
	 *			required={"name", "id", "fields", "data"},
	 *			@OA\Property(property="name", description="Record name", type="string", example="Driving school"),
	 *			@OA\Property(property="id", description="Record Id", type="integer", example=152),
	 *			@OA\Property(property="fields", type="object", title="System field names and field labels", example={"field_name_1" : "Field label 1", "field_name_2" : "Field label 2", "assigned_user_id" : "Assigned user", "createdtime" : "Created time"},
	 * 				@OA\AdditionalProperties(type="string", description="Field label"),
	 *			),
	 *			@OA\Property(property="data", title="Record data", type="object", ref="#/components/schemas/Record_Display_Details"),
	 *			@OA\Property(
	 *				property="privileges",
	 *				title="Parameters determining checking of editing rights and moving to the trash",
	 * 				type="object",
	 * 				required={"isEditable", "moveToTrash"},
	 *				@OA\Property(property="isEditable", description="Check if record is editable", type="boolean", example=true),
	 *				@OA\Property(property="moveToTrash", description="Permission to delete", type="boolean", example=false),
	 *			),
	 *			@OA\Property(property="inventory", description="Value inventory data", type="object"),
	 *			@OA\Property(property="summaryInventory", description="Value summary inventory data", type="object"),
	 *			@OA\Property(property="rawData", description="Raw record data", type="object", ref="#/components/schemas/Record_Raw_Details"),
	 *			@OA\Property(property="rawInventory", description="Inventory data", type="object"),
	 *		),
	 * ),
	 */
	public function get(): array
	{
		$displayData = $fieldsLabel = [];
		$moduleName = $this->controller->request->get('module');
		$setRawData = 1 === (int) ($this->controller->headers['x-raw-data'] ?? 0);
		$fieldParams = \App\Json::decode($this->controller->request->getHeader('x-fields-params')) ?: [];

		\Api\WebserviceStandard\Fields::loadWebserviceFields($this->recordModel->getModule(), $this);
		foreach ($this->recordModel->getModule()->getFields() as $fieldModel) {
			if (!$fieldModel->isActiveField() || !$fieldModel->isViewable()) {
				continue;
			}
			$uiTypeModel = $fieldModel->getUITypeModel();
			$value = $this->recordModel->get($fieldModel->getName());
			$displayData[$fieldModel->getName()] = $uiTypeModel->getApiDisplayValue($value, $this->recordModel, $fieldParams[$fieldModel->getName()] ?? []);
			$fieldsLabel[$fieldModel->getName()] = \App\Language::translate($fieldModel->get('label'), $moduleName);
		}
		$response = [
			'name' => \App\Purifier::decodeHtml($this->recordModel->getName()),
			'id' => $this->recordModel->getId(),
			'fields' => $fieldsLabel,
			'data' => $displayData,
			'privileges' => [
				'isEditable' => $this->recordModel->isEditable(),
				'moveToTrash' => $this->recordModel->privilegeToDelete(),
			],
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
					$summaryInventory[$name] = \App\Fields\Currency::formatToDisplay($field->getSummaryValuesFromData($rawInventory), null, true);
				}
			}
			$response['inventory'] = $inventory;
			$response['summaryInventory'] = $summaryInventory;
			if ($setRawData) {
				$response['rawInventory'] = $rawInventory;
			}
		}
		if ($setRawData) {
			$rawData = [];
			foreach ($this->recordModel->getData() as $key => $value) {
				if ('id' === $key || 'record_module' === $key || (($fieldModel = $this->recordModel->getField($key)) && $fieldModel->isViewable())) {
					$rawData[$key] = $this->recordModel->getRawValue($key);
				}
			}
			$response['rawData'] = $rawData;
		}
		return $response;
	}

	/**
	 * Delete record.
	 *
	 * @api
	 *
	 * @return bool
	 *
	 * @OA\Delete(
	 *		path="/webservice/WebserviceStandard/{moduleName}/Record/{recordId}",
	 *		description="Changes the state of a record, moving it to the trash",
	 *		summary="Delete record",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="recordId", in="path", @OA\Schema(type="integer"), description="Record id", required=true, example=116),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
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
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(property="result", type="boolean", description="Status of successful transfer of the record to the recycle bin"),
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
	 * @api
	 *
	 * @return array
	 *
	 * @OA\Put(
	 *		path="/webservice/WebserviceStandard/{moduleName}/Record/{recordId}",
	 *		description="Retrieves data for editing a record",
	 *		summary="Edit record",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\RequestBody(
	 *			required=true,
	 *			description="Contents of the request contains an associative array with the data record.",
	 *			@OA\JsonContent(ref="#/components/schemas/Record_Edit_Details"),
	 *			@OA\XmlContent(ref="#/components/schemas/Record_Edit_Details"),
	 *		),
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="recordId", in="path", @OA\Schema(type="integer"), description="Record id", required=true, example=116),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200, description="Contents of the response contains only id",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Put_Record_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Put_Record_Response"),
	 *			@OA\Link(link="GetRecordById", ref="#/components/links/GetRecordById")
	 *		),
	 *		@OA\Response(
	 *			response=406, description="No input data",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_Put_Record_Response",
	 *		title="Base module - Response body for Record",
	 *		description="Contents of the response contains only id",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(property="result", type="object", title="Record data", description="Created record id and name.",
	 *			required={"id", "name"},
	 *			@OA\Property(property="id", type="integer", description="Id of the newly created record", example=22),
	 *			@OA\Property(property="name", type="string", description="Id of the newly created record", example="YetiForce Name"),
	 *			@OA\Property(property="skippedData", type="object", description="List of parameters passed in the request that were skipped in the write process"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="Record_Edit_Details",
	 *		title="General - Record edit details",
	 *		description="Record data in user format for edit view",
	 *		type="object",
	 *		example={"field_name_1" : "Tom", "field_name_2" : "Kowalski", "assigned_user_id" : 1, "createdtime" : "2014-09-24 20:51:12"},
	 * ),
	 * @OA\Schema(
	 *		schema="Record_Raw_Details",
	 *		title="General - Record raw details",
	 *		description="Record data in the system format as stored in a database",
	 *		type="object",
	 *		example={"id" : 11, "field_name_1" : "Tom", "field_name_2" : "Kowalski", "assigned_user_id" : 1, "createdtime" : "2014-09-24 20:51:12"},
	 * ),
	 * @OA\Schema(
	 *		schema="Record_Display_Details",
	 *		title="General - Record display details",
	 *		description="Record data in user format for preview",
	 *		type="object",
	 *		example={"id" : 11, "field_name_1" : "Tom", "field_name_2" : "Kowalski", "assigned_user_id" : "YetiForce Administrator", "createdtime" : "2014-09-24 20:51"},
	 * ),
	 */
	public function put(): array
	{
		\Api\WebserviceStandard\Fields::loadWebserviceFields($this->recordModel->getModule(), $this);
		$saveModel = new \Api\WebserviceStandard\Save();
		$saveModel->init($this);
		$saveModel->saveRecord($this->controller->request);
		$return = [
			'id' => $this->recordModel->getId(),
			'name' => $this->recordModel->getName(),
		];
		if ($saveModel->skippedData) {
			$return['skippedData'] = $saveModel->skippedData;
		}
		return $return;
	}

	/**
	 * Create record.
	 *
	 * @api
	 *
	 * @return array
	 *
	 * @OA\Post(
	 *		path="/webservice/WebserviceStandard/{moduleName}/Record",
	 *		description="Create new record",
	 *		summary="Create record",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\RequestBody(
	 *			required=true,
	 *			description="Contents of the request contains an associative array with the data record.",
	 *			@OA\JsonContent(ref="#/components/schemas/Record_Edit_Details"),
	 *			@OA\XmlContent(ref="#/components/schemas/Record_Edit_Details"),
	 *		),
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200, description="Contents of the response contains only id",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Post_Record_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Post_Record_Response"),
	 *			@OA\Link(link="GetRecordById", ref="#/components/links/GetRecordById")
	 *		),
	 *		@OA\Response(
	 *			response=406, description="No input data",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_Post_Record_Response",
	 *		title="Base module - Created records",
	 *		description="Contents of the response contains only id",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(property="result", type="object", title="Record data", description="Created record id and name.",
	 *			required={"id", "name"},
	 *			@OA\Property(property="id", type="integer", description="Id of the newly created record", example=22),
	 *			@OA\Property(property="name", type="string", description="Id of the newly created record", example="YetiForce Name"),
	 *			@OA\Property(property="skippedData", type="object", description="List of parameters passed in the request that were skipped in the write process"),
	 *		),
	 * ),
	 *	@OA\Link(
	 *		link="GetRecordById",
	 *		description="The `id` value returned in the response can be used as the `recordId` parameter in `GET /webservice/{moduleName}/Record/{recordId}`.",
	 *		operationId="getRecord",
	 *		parameters={
	 *			"recordId" = "$response.body#/result/id"
	 *		}
	 *	)
	 */
	public function post(): array
	{
		if (1 !== $this->getUserData('type')) {
			foreach ($this->recordModel->getModule()->getFieldsByType('serverAccess') as $fieldName => $fieldModel) {
				if ($fieldModel->getFieldParams() == $this->getUserData('server_id')) {
					$this->recordModel->set($fieldName, 1);
					break;
				}
			}
		}
		return $this->put();
	}
}
