<?php

/**
 * Gets list of records.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\ManageConsents\BaseModule;

use OpenApi\Annotations as OA;

/**
 * RecordsList class.
 */
class GetConsentsForEntry extends \Api\ManageConsents\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['POST'];

	/**
	 * Gets consents.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return array
	 *
	 * @OA\Post(
	 *		path="/webservice/ManageConsents/{moduleName}/GetConsentsForEntry",
	 *		summary="Gets the list of consents for specific entry",
	 *		tags={"BaseModule"},
	 *		security={
	 *			{"basicAuth" : {}, "ApiKeyAuth" : {}, "token" : {}}
	 *	},
	 *		@OA\RequestBody(
	 *				required=true,
	 *				description="Required data for communication",
	 *				@OA\JsonContent(ref="#/components/schemas/BaseModule_Post_GetConsentsForEntry_Request"),
	 *			@OA\MediaType(
	 *				mediaType="multipart/form-data",
	 *				@OA\Schema(ref="#/components/schemas/BaseModule_Post_GetConsentsForEntry_Request")
	 *			),
	 *		@OA\MediaType(
	 *				mediaType="application/x-www-form-urlencoded",
	 *			@OA\Schema(ref="#/components/schemas/BaseModule_Post_GetConsentsForEntry_Request")
	 *		),
	 *		),
	 *		@OA\Parameter(name="moduleName", in="path", @OA\Schema(type="string"), description="Module name", required=true, example="Contacts"),
	 *		@OA\Response(
	 *			response=200,
	 *			description="List of consents for specific entry",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseModule_Post_GetConsentsForEntry_Response"),
	 *		),
	 *		@OA\Response(
	 *			response=401,
	 *			description="`No sent token` OR `Invalid token`",
	 *		),
	 *		@OA\Response(
	 *			response=403,
	 *			description="No permissions for module",
	 *		),
	 *		@OA\Response(
	 *			response=404,
	 *			description="Not Found",
	 *		),
	 *		@OA\Response(
	 *			response=405,
	 *			description="Method Not Allowed",
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseModule_Post_GetConsentsForEntry_Request",
	 *		title="Request body for GetConsentsForEntry",
	 *		type="object",
	 *		@OA\Property(
	 *			property="token",
	 *			description="Entry unique ID (Token type field in the module is required)",
	 *			type="string"
	 *		),
	 *	),
	 * @OA\Schema(
	 *		schema="BaseModule_Post_GetConsentsForEntry_Response",
	 *		title="Response body for GetConsentsForEntry",
	 *		type="object",
	 *		@OA\Property(property="status", type="integer", enum={0, 1}, description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error"),
	 *		@OA\Property(
	 *			property="result",
	 *			description="Specific response",
	 *			type="object",
	 *			@OA\Property(
	 *				property="id",
	 *				type="integer",
	 *				description="Record ID",
	 *				example=24842
	 *			),
	 *			@OA\Property(
	 *				property="consents",
	 * 				type="object",
	 *				description="Get the edit value in display view",
	 *				@OA\AdditionalProperties(description="Data from the associated module", type="integer", example=24862),
	 *			)
	 *		),
	 *	),
	 */
	public function post()
	{
		$relatedModule = 'Approvals';
		$referenceFieldModel = null;
		$queryGenerator = (new \App\QueryGenerator($this->controller->request->getModule()));
		$fieldToken = current($queryGenerator->getModuleModel()->getFieldsByType('token', true));
		foreach ($queryGenerator->getModuleModel()->getFieldsByType('multiReference', true) as $fieldModel) {
			if ($fieldModel->isActiveField() && $fieldModel->getReferenceList() === [$relatedModule]) {
				$referenceFieldModel = $fieldModel;
				break;
			}
		}
		if (!$referenceFieldModel
			|| !$fieldToken
			|| empty($recordData = $queryGenerator->setFields(['id', $referenceFieldModel->getName()])
				->addCondition($fieldToken->getName(), $this->controller->request->getByType('token', \App\Purifier::ALNUM), 'e')
				->createQuery()
				->one())
		) {
			throw new \Api\Core\Exception('Not Found', 404);
		}

		$referenceUiTypeModel = $referenceFieldModel->getUITypeModel();
		if ($consents = $referenceUiTypeModel->getArrayValues($recordData[$referenceFieldModel->getName()]) ?: []) {
			foreach ($consents as $key => $recordId) {
				if (!\App\Record::isExists($recordId)) {
					unset($consents[$key]);
				}
			}
		}

		return [
			'id' => $recordData['id'],
			'consents' => $consents
		];
	}
}
