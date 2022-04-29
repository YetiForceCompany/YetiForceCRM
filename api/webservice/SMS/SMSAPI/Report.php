<?php
/**
 * The file contains: Report operations.
 *
 * @package Api
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
	public $allowedMethod = ['GET'];

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
		if (!$this->controller->request->getByType('MsgId', \App\Purifier::ALNUM) || !$this->controller->request->getInteger('status') || !$this->controller->request->getByType('to', \App\Purifier::ALNUM)) {
			throw new \Api\Core\Exception('No permission - wrong data', 401);
		}
	}

	/** {@inheritdoc}  */
	protected function checkPermissionToModule(): void
	{
		if (!\Api\Core\Module::checkModuleAccess($this->moduleName)) {
			throw new \Api\Core\Exception('No permissions for module', 403);
		}
	}

	/**
	 * Add record.
	 *
	 * @return array
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
	 *				@OA\JsonContent(ref="#/components/schemas/SMS_SMSAPI_Post_Report")
	 *		),
	 *		@OA\Response(
	 *				response=401,
	 *				description="`No sent token` OR `Invalid token` or `data provided in the request`",
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
	public function get()
	{
		$recordIds = $this->controller->request->getArray('idx', \App\Purifier::INTEGER);
		$statuses = $this->controller->request->getArray('status', \App\Purifier::INTEGER);
		$phoneNumbers = $this->controller->request->getArray('to', \App\Purifier::ALNUM);
		$provider = \App\Integrations\SMSProvider::getProviderByName('SMSAPI');
		foreach ($recordIds as $key => $recordId) {
			$phoneNumber = $phoneNumbers[$key] ?? null;
			if (\App\Record::isExists($recordId, $this->moduleName)
				&& ($recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $this->moduleName))->isEditable()
				&& ($recordModel->isEmpty('phone') || $phoneNumber === $provider->setPhone($recordModel->get('phone'))->get('to'))
				&& $recordModel->set('smsnotifier_status', static::STATUSES[$statuses[$key]] ?? 'PLL_UNDEFINED')->getPreviousValue()
			) {
				$recordModel->save();
			}
		}

		echo 'OK';
	}
}
