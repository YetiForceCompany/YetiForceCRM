<?php
/**
 * The file contains: Reception operations.
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
 * Reception class.
 */
class Reception extends \Api\SMS\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['POST'];

	/** @var string Module name */
	private $moduleName = 'SMSNotifier';

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
		parent::checkPermission();
		if ($this->controller->request->isEmpty('MsgId', true)) {
			throw new \Api\Core\Exception('No permission - wrong data', 401);
		}
	}

	/** {@inheritdoc}  */
	protected function checkPermissionToModule(): void
	{
		if (!\Api\Core\Module::checkModuleAccess($this->moduleName) || !\App\Privilege::isPermitted($this->moduleName, 'CreateView') || !($provider = \App\Integrations\SMSProvider::getDefaultProvider()) || 'SMSAPI' !== $provider->getName()) {
			throw new \Api\Core\Exception('No permissions for module', 403);
		}
	}

	/**
	 * Add record.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/SMS/SMSAPI/Reception",
	 *		summary="Receipt of SMS",
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
	 *				@OA\JsonContent(ref="#/components/schemas/SMS_SMSAPI_Post_Reception")
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
	 *		schema="SMS_SMSAPI_Post_Reception",
	 *		title="Response",
	 *		description="Response",
	 *		type="string",
	 *		example="OK"
	 *	),
	 */
	public function post()
	{
		$msgId = $this->controller->request->getByType('MsgId', \App\Purifier::ALNUM);
		$message = $this->controller->request->getByType('sms_text', \App\Purifier::HTML);
		$smsFrom = $this->controller->request->getByType('sms_from', \App\Purifier::DIGITS);

		$provider = \App\Integrations\SMSProvider::getProviderByName('SMSAPI');
		$queryGenerator = (new \App\QueryGenerator($this->moduleName));
		$recordId = $queryGenerator->setFields(['id'])->addCondition('msgid', $msgId, 'e')->createQuery()->scalar();

		if ($recordId && \App\Record::isExists($recordId, $this->moduleName)
			&& ($recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $this->moduleName))
			&& $smsFrom === $provider->setPhone($recordModel->get('phone'))->get('to')
		) {
			$newRecordModel = \Vtiger_Record_Model::getCleanInstance($this->moduleName);
			$msgField = $newRecordModel->getField('message');
			$newRecordModel->set($msgField->getName(), $msgField->getDBValue($message))
				->set('parentid', $recordModel->getId())
				->set('related_to', $recordModel->get('related_to'))
				->set('smsnotifier_status', 'PLL_REPLY')
				->set('phone', $recordModel->get('phone'));
			$newRecordModel->save();
		}

		echo 'OK';
	}
}
