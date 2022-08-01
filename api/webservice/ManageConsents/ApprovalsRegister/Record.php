<?php
/**
 * The file contains: Record operations.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\ManageConsents\ApprovalsRegister;

use OpenApi\Annotations as OA;

/**
 * Record class.
 */
class Record extends \Api\ManageConsents\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['POST'];

	/**
	 * Record model.
	 *
	 * @var \Vtiger_Record_Model
	 */
	protected $recordModel;

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
		parent::checkPermission();
		$moduleName = $this->controller->request->getModule();
		$this->recordModel = \Vtiger_Record_Model::getCleanInstance($moduleName);
		if (!$this->recordModel->isCreateable()) {
			throw new \Api\Core\Exception('No permissions to create record', 403);
		}
	}

	/**
	 * Add record.
	 *
	 * @return array
	 *
	 * @OA\Post(
	 *		path="/webservice/ManageConsents/ApprovalsRegister/Record",
	 *		summary="Adds an consent entry",
	 *		tags={"ApprovalsRegister"},
	 *    security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *    },
	 *		@OA\RequestBody(
	 *				required=true,
	 *				description="Required data for communication",
	 *				@OA\JsonContent(ref="#/components/schemas/ApprovalsRegister_Post_Record_Request"),
	 *     		@OA\MediaType(
	 *         		mediaType="multipart/form-data",
	 *         		@OA\Schema(ref="#/components/schemas/ApprovalsRegister_Post_Record_Request")
	 *     		),
	 *     		@OA\MediaType(
	 *         		mediaType="application/x-www-form-urlencoded",
	 *         		@OA\Schema(ref="#/components/schemas/ApprovalsRegister_Post_Record_Request")
	 *     		),
	 *		),
	 *		@OA\Response(
	 *				response=200,
	 *				description="Result of adding entry",
	 *				@OA\JsonContent(ref="#/components/schemas/ApprovalsRegister_Post_Record_Response"),
	 *				@OA\XmlContent(ref="#/components/schemas/ApprovalsRegister_Post_Record_Response"),
	 *		),
	 *		@OA\Response(
	 *				response=401,
	 *				description="`No sent token` OR `Invalid token`",
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
	 * @OA\Schema(
	 *		schema="ApprovalsRegister_Post_Record_Request",
	 *		title="A list of fields required while creating an entry",
	 *		description="The list is based on fields in the Consent register module. Accepting or declining consent takes place based on the value in the approvals_register_status field.",
	 *		type="object",
	 * 		example={
	 * 			"subject" : "Text",
	 *			"approvalsid" : "123",
	 *			"contactid" : "321",
	 *			"approvals_register_type" : "PLL_ACCEPTANCE",
	 *			"approvals_register_status" : "PLL_FOR_VERIFICATION",
	 *			"registration_date" : "2019-11-12 12:00"
	 * 		}
	 *	),
	 * @OA\Schema(
	 *		schema="ApprovalsRegister_Post_Record_Response",
	 *		title="Adding an entry",
	 *		description="Result of adding entry",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *				property="result",
	 *				description="Result of adding entry",
	 *				type="object",
	 * 				@OA\Property(
	 * 						property="id",
	 * 						type="integer",
	 * 						description="New entry ID. Empty value means that the operation was unsuccessful.",
	 * 						example=24842
	 * 				),
	 * 				@OA\Property(
	 * 						property="error",
	 * 						type="string",
	 * 						example="",
	 * 						description="Error message. The variable exists when some of the provided data is incomplete and the entry could not be added.",
	 * 				)
	 * 		),
	 *	),
	 */
	public function post()
	{
		$response = ['id' => ''];
		$message = '';
		(new \Api\ManageConsents\Save($this->controller->app['id']))
			->setRecordModel($this->recordModel)
			->setDataFromRequest($this->controller->request);
		$this->recordModel->set('assigned_user_id', \App\User::getCurrentUserId());
		foreach ($this->recordModel->getModule()->getMandatoryFieldModels() as $fieldModel) {
			if ($this->recordModel->isEmpty($fieldModel->getName())) {
				$message = 'Mandatory fields are empty';
				break;
			}
		}
		if ($message) {
			$response['error'] = $message;
		} else {
			$this->recordModel->save();
			$response['id'] = $this->recordModel->getId();
		}
		return $response;
	}
}
