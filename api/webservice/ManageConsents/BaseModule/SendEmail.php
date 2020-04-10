<?php

/**
 * Send e-mail.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\ManageConsents\BaseModule;

use OpenApi\Annotations as OA;

/**
 * SendEmail class.
 */
class SendEmail extends \Api\ManageConsents\BaseAction
{
	/**
	 * {@inheritdoc}
	 */
	public $allowedMethod = ['POST'];

	/**
	 * Send e-mail.
	 *
	 * @return array
	 *
	 * @OA\POST(
	 *		path="/webservice/{moduleName}/SendEmail",
	 *		summary="Send e-mail",
	 *		tags={"Consents"},
	 *    security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : "", "token" : ""}
	 *    },
	 *		@OA\RequestBody(
	 *				required=true,
	 *				description="Required data for communication",
	 *				@OA\JsonContent(ref="#/components/schemas/SendEmailRequestBody"),
	 *     		@OA\MediaType(
	 *         		mediaType="multipart/form-data",
	 *         		@OA\Schema(ref="#/components/schemas/SendEmailRequestBody")
	 *     		),
	 *     		@OA\MediaType(
	 *         		mediaType="application/x-www-form-urlencoded",
	 *         		@OA\Schema(ref="#/components/schemas/SendEmailRequestBody")
	 *     		),
	 *		),
	 *		@OA\Parameter(
	 *				name="moduleName",
	 *  		 	description="Module name",
	 *  		 	@OA\Schema(
	 *  		  		type="string"
	 *  		 ),
	 *  		 in="path",
	 * 			 example="Contacts",
	 *  		 required=true
	 * 		),
	 *		@OA\Response(
	 *				response=200,
	 *				description="List of consents for specific entry",
	 *				@OA\JsonContent(ref="#/components/schemas/SendEmailResponseBody"),
	 *				@OA\MediaType(
	 *						mediaType="text/html",
	 *						@OA\Schema(ref="#/components/schemas/SendEmailResponseBody")
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
	 *				response=404,
	 *				description="Not Found",
	 *		),
	 *		@OA\Response(
	 *				response=405,
	 *				description="Method Not Allowed",
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="SendEmailRequestBody",
	 *		title="Request body for SendEmail",
	 *		type="object",
	 *		@OA\Property(
	 *				property="e-mail",
	 *				description="E-mail address (Token type field in the module is required)",
	 *				type="string"
	 *		),
	 *		@OA\Property(
	 *				property="templateId",
	 *				description="Specific ID of the email template",
	 *				type="integer"
	 *		),
	 *	),
	 * @OA\Schema(
	 *		schema="SendEmailResponseBody",
	 *		title="Response body for SendEmail",
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
	 *				description="Added mail to quote for send: true - success , false - fail",
	 *				type="bool",
	 * 				example=true
	 * 		),
	 *	),
	 */
	public function post()
	{
		$moduleName = $this->controller->request->getModule();
		$templateId = $this->controller->request->getInteger('templateId');
		$email = $this->controller->request->getByType('e-mail', 'Email');
		$recordId = '';
		if (!\App\Privilege::isPermitted('EmailTemplates', 'DetailView', $templateId)) {
			throw new \Api\Core\Exception('No permissions for email template: ' . $templateId, 403);
		}
		$queryGenerator = (new \App\QueryGenerator($moduleName));
		$emailFields = $queryGenerator->getModuleModel()->getFieldsByType('email', true);
		if ($emailFields && $email) {
			foreach ($emailFields as $fieldModel) {
				$queryGenerator->addCondition($fieldModel->getName(), $email, 'e', false);
			}
			$recordId = $queryGenerator->setFields(['id'])->createQuery()->scalar();
		}
		if (!$recordId) {
			throw new \Api\Core\Exception('Not Found', 404);
		}

		return \App\Mailer::sendFromTemplate([
			'moduleName' => $moduleName,
			'template' => $templateId,
			'to' => $email,
			'recordId' => $recordId
		]);
	}
}
