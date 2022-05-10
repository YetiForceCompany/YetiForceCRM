<?php
/**
 * Webservice premium container - Gets the record data from sources file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebservicePremium\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Webservice premium container - Gets the record data from sources class.
 */
class SourceBasedData extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['PUT'];

	/**
	 * Put method - Gets the record data from sources.
	 *
	 * @return array
	 *
	 * @OA\Put(
	 *		path="/webservice/WebservicePremium/{moduleName}/SourceBasedData",
	 *		summary="Gets the record data from sources",
	 *		description="Get the record by sources",
	 *		tags={"BaseModule"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\RequestBody(required=true, description="Contents of the request contains an associative array with the data record.",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Put_SourceBasedData_Request"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Put_SourceBasedData_Request"),
	 *		),
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(response=200, description="Source-based data response",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Put_SourceBasedData_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseModule_Put_SourceBasedData_Response"),
	 *		),
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseModule_Put_SourceBasedData_Request",
	 *		title="Base module - Source-based data request",
	 *		type="object",
	 *		oneOf={
	 *			@OA\Schema(type="object", title="Create a record from a related module",
	 *				required={"sourceModule", "sourceRecord"},
	 * 				@OA\Property(property="sourceModule", type="string", description="Source module", example="Accounts"),
	 * 				@OA\Property(property="sourceRecord", type="integer", description="Source record ID", example=221),
	 *			),
	 *			@OA\Schema(type="object", title="Create a record from a reference field",
	 *				required={"sourceRecordData"},
	 * 				@OA\Property(property="sourceRecordData", title="Record data", type="object", ref="#/components/schemas/Record_Edit_Details"),
	 *			),
	 *		},
	 *	),
	 *	@OA\Schema(
	 *		schema="BaseModule_Put_SourceBasedData_Response",
	 *		title="Base module - Source-based data response",
	 *		description="Data to auto-complete in compose view",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(property="result", type="object", title="Returns record data",
	 *			required={"data", "rawData"},
	 *			@OA\Property(property="data", title="Record data", type="object", ref="#/components/schemas/Record_Display_Details"),
	 *			@OA\Property(property="rawData", description="Raw record data", type="object", ref="#/components/schemas/Record_Raw_Details"),
	 *		),
	 *	),
	 */
	public function put(): array
	{
		$moduleName = $this->controller->request->getModule();
		$recordModel = \Vtiger_Record_Model::getCleanInstance($moduleName);
		$raw = $data = [];
		foreach ($recordModel->getModule()->getValuesFromSource($this->controller->request) as $fieldName => $value) {
			$recordModel->set($fieldName, $value);
			$raw[$fieldName] = $recordModel->getRawValue($fieldName);
		}
		foreach (array_keys($raw) as $fieldName) {
			$data[$fieldName] = $recordModel->getModule()->getFieldByName($fieldName)->getUITypeModel()->getApiDisplayValue($recordModel->get($fieldName), $recordModel);
		}
		return [
			'data' => $data,
			'rawData' => $raw
		];
	}
}
