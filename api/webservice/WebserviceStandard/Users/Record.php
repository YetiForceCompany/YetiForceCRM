<?php
/**
 * Webservice standard container - Get user record detail file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license	YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author	Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author  Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\WebserviceStandard\Users;

use OpenApi\Annotations as OA;

/**
 * Webservice standard container - Get user record detail class.
 */
class Record extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET', 'POST'];

	/** @var \Users_Record_Model User record model. */
	public $recordModel;

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
		parent::checkPermission();
		$moduleName = $this->controller->request->getModule();
		if ('POST' === $this->controller->method) {
			$this->recordModel = \Users_Record_Model::getCleanInstance($moduleName);
			if (!$this->recordModel->isCreateable()) {
				throw new \Api\Core\Exception('No permissions to create user', 403);
			}
		} else {
			if ($this->controller->request->isEmpty('record', true) || !\App\User::isExists($this->controller->request->getInteger('record'), false)) {
				throw new \Api\Core\Exception('User doesn\'t exist', 404);
			}
			if (!\App\User::getCurrentUserModel()->isAdmin()) {
				throw new \Api\Core\Exception('Access denied, access for administrators only', 403);
			}
			$this->recordModel = \Users_Record_Model::getInstanceById($this->controller->request->getInteger('record'), 'Users');
		}
	}

	/**
	 * Get user detail.
	 *
	 * @api
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/WebserviceStandard/Users/Record/{userId}",
	 *		description="Gets details about the user",
	 *		summary="Data for the user",
	 *		tags={"Users"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		operationId="getUser",
	 *		@OA\Parameter(
	 *			name="userId",
	 *			description="User id",
	 *			@OA\Schema(type="integer"),
	 *			in="path",
	 *			example=116,
	 *			required=true
	 *		),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Parameter(name="x-raw-data", in="header", @OA\Schema(type="integer", enum={0, 1}), description="Gets raw data", required=false, example=1),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Gets data for the user",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_Get_Record_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_Get_Record_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="Access denied, access for administrators only",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 *		@OA\Response(
	 *			response=404,
	 *			description="User doesn't exist",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="Users_Get_Record_Response",
	 *		title="Users module - Response body for user",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			description="User data",
	 *			type="object",
	 *			@OA\Property(property="name", description="User label", type="string", example="System Admin"),
	 *			@OA\Property(property="id", description="User Id", type="integer", example=1),
	 *			@OA\Property(property="fields", type="object", title="System field names and field labels", example={"field_name_1" : "Field label 1", "field_name_2" : "Field label 2"},
	 * 				@OA\AdditionalProperties(type="string", description="Field label"),
	 *			),
	 *			@OA\Property(
	 *				property="data",
	 *				description="User data",
	 *				type="object",
	 *			),
	 *			@OA\Property(
	 *				property="privileges",
	 *				description="Parameters determining checking of editing rights and moving to the trash",
	 * 				type="object",
	 *				@OA\Property(property="isEditable", description="Check if user is editable", type="boolean", example=true),
	 *				@OA\Property(property="moveToTrash", description="Permission to delete", type="boolean", example=false),
	 *			),
	 *			@OA\Property(property="rawData", description="Raw user data", type="object"),
	 *		),
	 * ),
	 */
	public function get(): array
	{
		$rawData = $this->recordModel->getData();
		$displayData = $fieldsLabel = [];
		foreach ($this->recordModel->getModule()->getFields() as $fieldModel) {
			$uiTypeModel = $fieldModel->getUITypeModel();
			$value = $this->recordModel->get($fieldModel->getName());
			$displayData[$fieldModel->getName()] = $uiTypeModel->getApiDisplayValue($value, $this->recordModel);
			$fieldsLabel[$fieldModel->getName()] = \App\Language::translate($fieldModel->get('label'), 'Users');
			if ($fieldModel->isReferenceField()) {
				$referenceModule = $uiTypeModel->getReferenceModule($value);
				$rawData[$fieldModel->getName() . '_module'] = $referenceModule ? $referenceModule->getName() : null;
			}
			if ('taxes' === $fieldModel->getFieldDataType()) {
				$rawData[$fieldModel->getName() . '_info'] = \Vtiger_Taxes_UIType::getValues($rawData[$fieldModel->getName()]);
			}
		}
		unset($fieldsLabel['user_password'],$fieldsLabel['confirm_password'],$fieldsLabel['accesskey'],$displayData['user_password'],$displayData['confirm_password'],$displayData['accesskey'],$rawData['user_password'],$rawData['confirm_password'],$rawData['accesskey']);
		$response = [
			'name' => \App\Purifier::decodeHtml($this->recordModel->getName()),
			'id' => $this->recordModel->getId(),
			'fields' => $fieldsLabel,
			'data' => $displayData,
			'privileges' => [
				'isEditable' => false,
				'moveToTrash' => false,
			],
		];
		if (1 === (int) $this->controller->headers['x-raw-data'] ?? 0) {
			$response['rawData'] = $rawData;
		}
		return $response;
	}

	/**
	 * Create record.
	 *
	 * @api
	 *
	 * @return array
	 *
	 * @OA\Post(
	 *		path="/webservice/WebserviceStandard/Users/Record",
	 *		description="Create new user",
	 *		summary="Create user",
	 *		tags={"Users"},
	 *		security={{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}},
	 *		@OA\RequestBody(required=true, description="Contents of the request contains an associative array with the user data.",
	 *			@OA\JsonContent(ref="#/components/schemas/User_Create_Details"),
	 *			@OA\XmlContent(ref="#/components/schemas/User_Create_Details"),
	 *		),
	 *		@OA\Parameter(name="X-ENCRYPTED", in="header", @OA\Schema(ref="#/components/schemas/Header-Encrypted"), required=true),
	 *		@OA\Response(
	 *			response=200, description="Contents of the response contains only id",
	 *			@OA\JsonContent(ref="#/components/schemas/User_Post_Record_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/User_Post_Record_Response"),
	 *			@OA\Link(link="GetUserById", ref="#/components/links/GetUserById")
	 *		),
	 *		@OA\Response(
	 *			response=406, description="No input data",
	 *			@OA\JsonContent(ref="#/components/schemas/Exception"),
	 *			@OA\XmlContent(ref="#/components/schemas/Exception"),
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="User_Post_Record_Response",
	 *		title="User - Created user",
	 *		description="Contents of the response contains only id and name",
	 *		type="object",
	 *		required={"status", "result"},
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(property="result", type="object", title="User data", description="Created user id and name.",
	 *			required={"id", "name"},
	 *			@OA\Property(property="id", type="integer", description="Id of the newly created user", example=22),
	 *			@OA\Property(property="name", type="string", description="Id of the newly created user", example="YetiForce Name"),
	 *			@OA\Property(property="skippedData", type="object", description="List of parameters passed in the request that were skipped in the write process"),
	 *		),
	 * ),
	 *	@OA\Schema(
	 *		schema="User_Create_Details",
	 *		title="General - User create details",
	 *		description="User data in user format for create view",
	 *		type="object",
	 *		example={"user_name" : "tom", "first_name" : "Tom", "last_name" : "Kowalski", "roleid" : "H38", "password" : "MyFunP@ssword", "confirm_password" : "MyFunP@ssword", "email1" : "my@email.com", "language" : "en-US"},
	 *	),
	 *	@OA\Link(
	 *		link="GetUserById",
	 *		description="The `id` value returned in the response can be used as the `userId` parameter in `GET /webservice/Users/Record/{userId}`.",
	 *		operationId="getUser",
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
}
