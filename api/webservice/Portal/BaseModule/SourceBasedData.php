<?php
/**
 * Portal container - Get the source-based data file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\BaseModule;

use OpenApi\Annotations as OA;

/**
 * Portal container - Get the source-based data class.
 */
class SourceBasedData extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['PUT'];

	/**
	 * Get the source-based data.
	 *
	 * @return array
	 *
	 * @OA\Put(
	 *		path="/webservice/Portal/{moduleName}/SourceBasedData",
	 *		summary="Get the source-based data",
	 *		description="Get the record data from sources",
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
			$raw[$fieldName] = $value;
		}
		foreach ($raw as $fieldName => $value) {
			$data[$fieldName] = $recordModel->getModule()->getFieldByName($fieldName)->getUITypeModel()->getApiDisplayValue($value, $recordModel);
		}
		return [
			'data' => $data,
			'rawData' => $raw,
		];
	}
}
