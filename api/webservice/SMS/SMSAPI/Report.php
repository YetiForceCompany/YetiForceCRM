<?php
/**
 * The file contains: Report operations.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\SMS\SMSAPI;

use OpenApi\Annotations as OA;

/**
 * Report class.
 */
class Report extends \Api\SMS\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['GET', 'POST'];

	/** @var string Module name */
	private $moduleName = 'SMSNotifier';

	/**
	 * Get status for record by code.
	 *
	 * Statuses from api:
	 *  402 => 'EXPIRED',
	 *	403 => 'SENT',
	 *	404 => 'DELIVERED',
	 *	405 => 'UNDELIVERED',
	 *	406 => 'FAILED',
	 *	407 => 'REJECTED',
	 *	408 => 'UNKNOWN',
	 *	409 => 'QUEUE',
	 *	410 => 'ACCEPTED',
	 *	411 => 'RENEWAL',
	 *	412 => 'STOP'.
	 */
	private const STATUSES = [
		402 => 'PLL_FAILED',
		403 => 'PLL_SENT',
		404 => 'PLL_DELIVERED',
		405 => 'PLL_FAILED',
		406 => 'PLL_FAILED',
		407 => 'PLL_FAILED',
		408 => 'PLL_SENT',
		410 => 'PLL_DELIVERED',
	];

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
		parent::checkPermission();
		if (!$this->controller->request->getExploded('MsgId', ',', \App\Purifier::ALNUM) || !$this->controller->request->getExploded('status', ',', \App\Purifier::INTEGER) || !$this->controller->request->getExploded('to', ',', \App\Purifier::ALNUM)) {
			throw new \Api\Core\Exception('No permission - wrong data', 401);
		}
	}

	/** {@inheritdoc}  */
	protected function checkPermissionToModule(): void
	{
		if (!\Api\Core\Module::checkModuleAccess($this->moduleName) || !\App\Privilege::isPermitted($this->moduleName, 'EditView') || !($provider = \App\Integrations\SMSProvider::getDefaultProvider()) || 'SMSAPI' !== $provider->getName()) {
			throw new \Api\Core\Exception('No permissions for module', 403);
		}
	}

	/**
	 * Update record status.
	 *
	 * @return void
	 *
	 * @OA\Get(
	 *		path="/webservice/SMS/SMSAPI/Report",
	 *		summary="Report for sms",
	 *		tags={"SMSAPI"},
	 *		externalDocs={
	 *			"description" : "SMSApi Documentation",
	 *			"url" : "https://www.smsapi.pl/docs"
	 * 		},
	 * 		security={
	 *			{"ApiKeyAuth" : {}, "token" : {}}
	 *  	},
	 *		@OA\Response(
	 *				response=200,
	 *				description="Result",
	 *				@OA\JsonContent(ref="#/components/schemas/SMS_SMSAPI_Get_Report")
	 *		),
	 *		@OA\Response(
	 *				response=401,
	 *				description="`No sent token` OR `Invalid token` OR `wrong data provided in the request`",
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
	 *		schema="SMS_SMSAPI_Get_Report",
	 *		title="Response",
	 *		description="Response",
	 *		type="string",
	 *		example="OK"
	 *	),
	 */
	public function get()
	{
		$recordIds = $this->controller->request->getExploded('idx', ',', \App\Purifier::INTEGER);
		$msgIds = $this->controller->request->getExploded('MsgId', ',', \App\Purifier::ALNUM);
		$statuses = $this->controller->request->getExploded('status', ',', \App\Purifier::INTEGER);
		foreach ($recordIds as $key => $recordId) {
			if (\App\Record::isExists($recordId, $this->moduleName)
				&& ($recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $this->moduleName))->isEditable()
				&& !$recordModel->isEmpty('msgid') && \in_array($recordModel->get('msgid'), $msgIds)
				&& $recordModel->set('smsnotifier_status', static::STATUSES[$statuses[$key]] ?? 'PLL_UNDEFINED')->getPreviousValue()
			) {
				$recordModel->save();
			}
		}

		echo 'OK';
	}

	/**
	 * Update record status.
	 *
	 * @return void
	 *
	 * @OA\Post(
	 *		path="/webservice/SMS/SMSAPI/Report",
	 *		summary="Report for sms",
	 *		tags={"SMSAPI"},
	 *		externalDocs={
	 *			"description" : "SMSApi Documentation",
	 *			"url" : "https://www.smsapi.pl/docs"
	 * 		},
	 * 		security={
	 *			{"ApiKeyAuth" : {}, "token" : {}}
	 *  	},
	 *		@OA\Response(
	 *				response=200,
	 *				description="Result",
	 *				@OA\JsonContent(ref="#/components/schemas/SMS_SMSAPI_Post_Report")
	 *		),
	 *		@OA\Response(
	 *				response=401,
	 *				description="`No sent token` OR `Invalid token` OR `wrong data provided in the request`",
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
	 *		schema="SMS_SMSAPI_Post_Report",
	 *		title="Response",
	 *		description="Response",
	 *		type="string",
	 *		example="OK"
	 *	),
	 */
	public function post()
	{
		$this->get();
	}
}
