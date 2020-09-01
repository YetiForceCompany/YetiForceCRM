<?php
/**
 * The file contains: Get user detail class.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license	YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author	Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\Users;

use OpenApi\Annotations as OA;

/**
 * Get user detail class.
 */
class Record extends \Api\Core\BaseAction
{
	/**
	 * {@inheritdoc}
	 */
	public $allowedMethod = ['GET'];
	/**
	 * User record model.
	 *
	 * @var \Vtiger_Record_Model
	 */
	public $recordModel;

	/**
	 * Check permission to method, access for administrators only.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return bool
	 */
	public function checkPermission()
	{
		parent::checkPermission();
		if ($this->controller->request->isEmpty('record', true) || !\App\User::isExists($this->controller->request->getInteger('record'), false)) {
			throw new \Api\Core\Exception('User doesn\'t exist', 404);
		}
		if (!\App\User::getCurrentUserModel()->isAdmin()) {
			throw new \Api\Core\Exception('Access denied, access for administrators only', 403);
		}
		$this->recordModel = \Users_Record_Model::getInstanceById($this->controller->request->getInteger('record'), 'Users');
	}

	/**
	 * Get user detail.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/Users/Record/{userId}",
	 *		summary="Get data for the user",
	 *		tags={"Users"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : "", "token" : ""}
	 *		},
	 *		@OA\RequestBody(
	 *			required=false,
	 *			description="The content of the request is empty.",
	 *		),
	 *		@OA\Parameter(
	 *			name="userId",
	 *			description="User id",
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
	 *		@OA\Response(
	 *			response=200,
	 *			description="Gets data for the user",
	 *			@OA\JsonContent(ref="#/components/schemas/Users_Get_Record_Response"),
	 *			@OA\XmlContent(ref="#/components/schemas/Users_Get_Record_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="Access denied, access for administrators only"
	 *		),
	 *		@OA\Response(
	 *			response=404,
	 *			description="User doesn't exist"
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="Users_Get_Record_Response",
	 *		title="Users - Response body for user",
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
	 *			description="User data",
	 *			type="object",
	 *			@OA\Property(property="name", description="User label", type="string", example="System Admin"),
	 *			@OA\Property(property="id", description="User Id", type="integer", example=1),
	 *			@OA\Property(
	 * 				property="fields",
	 *				description="Field name items",
	 *				type="object",
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
			'name' => $this->recordModel->getName(),
			'id' => $this->recordModel->getId(),
			'fields' => $fieldsLabel,
			'data' => $displayData,
			'privileges' => [
				'isEditable' => false,
				'moveToTrash' => false
			]
		];
		if (1 === (int) $this->controller->headers['x-raw-data']) {
			$response['rawData'] = $rawData;
		}
		return $response;
	}
}
