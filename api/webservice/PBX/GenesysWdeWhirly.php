<?php
/**
 * PBX Genesys WDE by Whirly file to handle communication via web services.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\PBX;

use OpenApi\Annotations as OA;

/**
 * PBX Genesys WDE by Whirly class to handle communication via web services.
 *
 * @OA\Info(
 * 		title="YetiForce API for PBX. Type: PBX",
 * 		description="",
 * 		version="0.1",
 * 		termsOfService="https://yetiforce.com/",
 *   	@OA\Contact(email="devs@yetiforce.com", name="Devs API Team", url="https://yetiforce.com/"),
 *   	@OA\License(name="YetiForce Public License", url="https://yetiforce.com/en/yetiforce/license"),
 * )
 * @OA\Server(description="Demo server of the development version", url="https://gitdeveloper.yetiforce.com")
 * @OA\Server(description="Demo server of the latest stable version", url="https://gitstable.yetiforce.com")
 * @OA\Schema(
 *		schema="PBX_Genesys_Error",
 *		title="Response for Genesys errors",
 *		type="object",
 *		required={"status", "description"},
 *		@OA\Property(
 *			property="status",
 *			type="integer",
 *			description="That indicates whether the communication is valid. 1 - success , 0 - error",
 *			example=1
 *		),
 *		@OA\Property(property="description", type="string", description="Error description", example="No data"),
 * ),
 */
class GenesysWdeWhirly extends \Api\Core\BaseAction
{
	/** {@inheritdoc}  */
	public $allowedMethod = ['POST'];
	/** @var \Vtiger_Record_Model Interaction record model */
	private $interactionModel;
	/** @var int User ID */
	private $userId;
	/** @var int Contact ID */
	private $contact;
	/** @var string Phone. */
	private $phone;
	/** @var string Telephone country code. */
	private $phoneCountry;
	/** @var array Additional parameters in the URL to open. */
	private $urlParams = [];
	/** @var string[] Value mapping for the type field. */
	public const MEDIA_TYPE_MAP = [
		'voice in' => 'Incoming',
		'voice out' => 'Outgoing',
		'callback' => 'Outgoing - callback',
		'outbound' => 'Outgoing - outbound',
		'chat' => 'Chat',
		'email out' => 'E-mail out',
		'email in' => 'E-mail in',
		'messenger' => 'Messenger',
		'facebook' => 'FB Wall',
		'facebookprivatemessage' => 'Messenger',
	];
	/** @var string[] Create contacts for call types only. */
	public const CREATE_CONTACTS = ['messenger'];

	/** {@inheritdoc}  */
	protected function checkPermission(): void
	{
		\App\User::setCurrentUserId(\Users::getActiveAdminId());
		$this->userId = \App\User::getCurrentUserId();
	}

	/** {@inheritdoc} */
	protected function checkPermissionToModule(): void
	{
		if (!\in_array(\App\Process::$processName, ['registerInteraction', 'registerInteractionCampaign', 'updateInteraction'])) {
			throw new \Api\Core\Exception('Method Not Found', 404);
		}
	}

	/** {@inheritdoc} */
	public function updateSession(array $data = []): void
	{
	}

	/**
	 * Api PBX Genesys creating interactions method.
	 *
	 * @return void
	 */
	public function post(): void
	{
		$this->phoneCountry = array_key_first(\App\Fields\Country::getAll());
		$this->findOwner();
		$this->{\App\Process::$processName}();
	}

	/**
	 * Register interaction.
	 *
	 * @return void
	 *
	 * @OA\Post(
	 *		path="/webservice/PBX/GenesysWdeWhirly/registerInteraction",
	 *		summary="PBX Genesys creating interactions",
	 *		tags={"Genesys"},
	 * 		security={{"ApiKeyAuth" : {}}},
	 *		@OA\RequestBody(
	 *			required=true,
	 *			description="Contents of the request contains an associative array with the data.",
	 *			@OA\JsonContent(ref="#/components/schemas/PBX_Genesys_RegisterInteraction_Request"),
	 *		),
	 *		@OA\Response(
	 * 			response=200,
	 * 			description="Correct server response",
	 * 			@OA\JsonContent(ref="#/components/schemas/PBX_Genesys_RegisterInteraction_Response")
	 * 		),
	 *		@OA\Response(response=401, description="Invalid api key"),
	 *		@OA\Response(response=404, description="Method Not Found"),
	 *		@OA\Response(response=500, description="Error", @OA\JsonContent(ref="#/components/schemas/PBX_Genesys_Error")),
	 * ),
	 * @OA\Schema(
	 * 		schema="PBX_Genesys_RegisterInteraction_Request",
	 * 		title="Request for creating interactions",
	 *		type="object",
	 *		required={"GenesysIDInteraction", "InteractionStartDateTime", "MediaType"},
	 *  	@OA\Property(property="GenesysIDInteraction", type="string", example="00047aHK833X02TB"),
	 *  	@OA\Property(property="OutboundCallID", type="integer"),
	 *  	@OA\Property(property="QueueName", type="string"),
	 *  	@OA\Property(property="QueueTime", type="integer", example=122),
	 *  	@OA\Property(property="ServiceType", type="string"),
	 *  	@OA\Property(property="ServiceValue", type="string"),
	 *  	@OA\Property(property="DialedNumber", type="string"),
	 *  	@OA\Property(property="CustomerEmail", type="string", example="test@yetiforce.com"),
	 *  	@OA\Property(property="CustomerPhoneNumber", type="string", example="+48884998123"),
	 *  	@OA\Property(property="FacebookActorID", type="string", example="4187643884658211"),
	 *  	@OA\Property(property="FacebookActorName", type="string"),
	 *  	@OA\Property(property="CustomerContactName", type="string", example="Tom"),
	 *  	@OA\Property(property="CustomerContactLastName", type="string", example="Kowalski"),
	 *  	@OA\Property(property="CustomerNIP", type="integer"),
	 *  	@OA\Property(property="CustomerAgreements", type="string", example="[]"),
	 *  	@OA\Property(property="AgentName", type="string"),
	 *  	@OA\Property(property="AgentID", type="string"),
	 *  	@OA\Property(
	 * 			property="MediaType",
	 * 			type="string",
	 * 			enum={"voice in", "voice out", "callback", "chat", "InboundNew", "email out", "email in", "outbound", "messenger", "facebook", "facebookprivatemessage"}
	 * 		),
	 *  	@OA\Property(property="CRMPreviewInteractionID", type="string"),
	 *  	@OA\Property(property="InteractionStartDateTime", type="string", example="2022-11-08T14:54:55.9895353Z"),
	 *  	@OA\Property(property="CRMSourceID", type="integer", example=4475),
	 * ),
	 * @OA\Schema(
	 *		schema="PBX_Genesys_RegisterInteraction_Response",
	 *		title="Response for creating interactions",
	 *		type="object",
	 *		required={"status", "interactionId", "url"},
	 *		@OA\Property(
	 *			property="status",
	 *			type="integer",
	 *			description="That indicates whether the communication is valid. 1 - success , 0 - error",
	 *			example=1
	 *		),
	 *		@OA\Property(property="interactionId", type="integer", description="CRM interaction ID", example=3345),
	 *		@OA\Property(
	 *			property="url",
	 *			type="string",
	 *			description="The full URL to call on the Genesys app",
	 *			example="https://gitstable.yetiforce.com/index.php?module=Accounts&view=List",
	 *		),
	 * ),
	 */
	private function registerInteraction(): void
	{
		$request = $this->controller->request;
		$this->interactionModel = $recordModel = \Vtiger_Record_Model::getCleanInstance('CallHistory');
		$recordModel->set('assigned_user_id', $this->userId);
		$recordModel->set('to_number', $this->getPhone('CustomerPhoneNumber'));
		if (!$request->isEmpty('CRMSourceID') && \App\Record::isExists($request->getInteger('CRMSourceID'))) {
			$sourceId = $request->getInteger('CRMSourceID');
			$recordModel->set('source', $sourceId);
		}
		if ($contact = $this->findOrCreateContact()) {
			$recordModel->set('destination', $contact);
		}
		$recordModel->set('gwde_id', $request->getByType('GenesysIDInteraction', 'Alnum'));
		$recordModel->set(
			'gwde_outbound_call_id',
			($request->isEmpty('OutboundCallID') ? null : $request->getByType('OutboundCallID', 'Alnum'))
		);
		$recordModel->set('gwde_queue_name', $request->getByType('QueueName', 'Text'));
		$recordModel->set('gwde_queue_time', $request->getByType('QueueTime', 'Integer'));
		$recordModel->set('gwde_service_type', $request->getByType('ServiceType', 'Text'));
		$recordModel->set('gwde_service_value', $request->getByType('ServiceValue', 'Text'));
		$recordModel->set('gwde_dialed_number', $request->getByType('DialedNumber', 'Text'));
		$recordModel->set('email', $request->getByType('CustomerEmail', 'Text'));
		$recordModel->set(
			'gwde_crm_prev_id',
			($request->isEmpty('CRMPreviewInteractionID') ? 0 : $request->getByType('CRMPreviewInteractionID', 'Integer'))
		);
		$recordModel->set(
			'start_time',
			date('Y-m-d H:i:s', strtotime($request->getByType('InteractionStartDateTime', 'Text')))
		);
		$recordModel->set('gwde_media_type', $request->getByType('MediaType', 'AlnumExtended'));
		$recordModel->set('agent_login', $request->getByType('AgentID', 'Text'));
		$recordModel->set(
			'facebook_actor_id',
			($request->isEmpty('FacebookActorID') ? 0 : $request->getByType('FacebookActorID', 'Alnum'))
		);
		$recordModel->set('ip_address', $_SERVER['REMOTE_ADDR']);
		$recordModel->set('callhistorytype', self::MEDIA_TYPE_MAP[$request->getByType('MediaType', 'AlnumExtended')] ?? '-');
		$recordModel->set(
			'gwde_token',
			($request->isEmpty('CustomerToken') ? null : $request->getByType('CustomerToken', 'AlnumExtended'))
		);
		$recordModel->save();
		$this->createApprovalsRegister($recordModel);
		$this->returnData();
	}

	/**
	 * Create a new one approvals register.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return void
	 */
	private function createApprovalsRegister(\Vtiger_Record_Model $recordModel): void
	{
		$typeMap = [0 => 'PLL_RESIGNATION', 1 => 'PLL_ACCEPTANCE'];
		$request = $this->controller->request;
		if (!$request->isEmpty('CustomerAgreements')) {
			foreach ($request->getArray('CustomerAgreements', 'Alnum') as $value) {
				if (\is_string($value)) {
					$value = \App\Json::decode($value);
				}
				if ($approvalsId = $this->findOrCreateApproval($value)) {
					$recordAppReg = \Vtiger_Record_Model::getCleanInstance('ApprovalsRegister');
					$recordAppReg->set('approvalsid', $approvalsId);
					$recordAppReg->set('assigned_user_id', $this->userId);
					$recordAppReg->set(
						'registration_date',
						date('Y-m-d H:i:s', strtotime($request->getByType('InteractionStartDateTime', 'Text')))
					);
					$recordAppReg->set('formid', $recordModel->getId());
					if (!$recordModel->isEmpty('destination')) {
						$recordAppReg->set('contactid', $recordModel->get('destination'));
					}
					$recordAppReg->set('approvals_register_type', $typeMap[$value['Value']]);
					$recordAppReg->save();
				}
			}
		}
	}

	/**
	 * Find Approval in the system or create a new one.
	 *
	 * @param array $approvalsData
	 *
	 * @return int
	 */
	private function findOrCreateApproval(array $approvalsData): int
	{
		if ((new \App\Db\Query())->from('u_#__approvals')->where(['name' => $approvalsData['Name']])->exists()) {
			return (new \App\Db\Query())->select(['approvalsid'])->from('u_#__approvals')
				->where(['name' => $approvalsData['Name']])->scalar();
		}
		$recordApproval = \Vtiger_Record_Model::getCleanInstance('Approvals');
		$recordApproval->set('approvals_status', 'PLL_DRAFT');
		$recordApproval->set('assigned_user_id', $this->userId);
		$recordApproval->set('name', $approvalsData['Name']);
		$recordApproval->save();
		return $recordApproval->getId();
	}

	/**
	 * Get phone number in system format by name.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	private function getPhone(string $key): string
	{
		if (isset($this->phone[$key])) {
			return $this->phone[$key];
		}
		$number = '';
		if (!$this->controller->request->isEmpty($key)) {
			$phone = $this->controller->request->getByType($key, 'AlnumType2');
			if ('anonymous' !== $phone && \App\Config::component('Phone', 'advancedVerification', false)) {
				$phoneDetails = \App\Fields\Phone::getDetails($phone, $this->phoneCountry);
				if (isset($phoneDetails['number'])) {
					$number = $phoneDetails['number'];
				} else {
					throw new \Api\Core\Exception('Invalid phone number: ' . $key, 404);
				}
			}
		}
		return $this->phone[$key] = $number;
	}

	/**
	 * Find contact in the system or create a new one.
	 *
	 * @param string $emailKey
	 * @param string $phoneKey
	 *
	 * @return int
	 */
	private function findOrCreateContact(string $emailKey = 'CustomerEmail', string $phoneKey = 'CustomerPhoneNumber'): ?int
	{
		if (isset($this->contact)) {
			return $this->contact;
		}
		$request = $this->controller->request;
		$email = $facebookActorID = $phoneNumber = '';
		if (!$request->isEmpty($emailKey)) {
			$email = $request->getByType($emailKey, 'Text');
			$contact = $this->findExistRecord($email, 'email', 'Contacts');
		}
		if (empty($contact) && !$request->isEmpty($phoneKey)) {
			$phoneNumber = $this->getPhone($phoneKey);
			$contact = $this->findExistRecord($phoneNumber, 'phone', 'Contacts');
		}
		if (empty($contact) && !$request->isEmpty('FacebookActorID')) {
			$facebookActorID = $request->getByType('FacebookActorID', 'Alnum');
			$contact = $this->findExistRecord($facebookActorID, 'facebook_actor_id', 'Contacts');
		}
		if (!empty($contact)) {
			$contact = $contact['id'];
		} elseif (null === $contact && \in_array($request->getByType('MediaType', 'AlnumExtended'), self::CREATE_CONTACTS)) {
			$contact = $this->createContact($email, $phoneNumber, $facebookActorID);
		}
		return $this->contact = $contact;
	}

	/**
	 * Create contact in the system.
	 *
	 * @param string $email
	 * @param string $phoneNumber
	 * @param string $facebookActorID
	 *
	 * @return int
	 */
	private function createContact(string $email, string $phoneNumber, string $facebookActorID): int
	{
		if (empty($email) && empty($phoneNumber) && empty($facebookActorID)) {
			return 0;
		}
		$recordModelContact = \Vtiger_Record_Model::getCleanInstance('Contacts');
		$recordModelContact->set('assigned_user_id', $this->userId);
		$recordModelContact->set('email', $email);
		$recordModelContact->set('phone', $phoneNumber);
		$recordModelContact->set('facebook_actor_id', $facebookActorID);
		if (!$this->controller->request->isEmpty('CustomerContactName')) {
			$recordModelContact->set('firstname', $this->controller->request->getByType('CustomerContactName', 'Text'));
		}
		if (!$this->controller->request->isEmpty('CustomerContactLastName')) {
			$recordModelContact->set('lastname', $this->controller->request->getByType('CustomerContactLastName', 'Text'));
		}
		$recordModelContact->save();
		return $recordModelContact->getId();
	}

	/**
	 * Find if there is a record in the system.
	 *
	 * @param string $value
	 * @param string $fieldName
	 * @param string $moduleName
	 * @param array  $expandFields
	 *
	 * @return int|array|null
	 */
	private function findExistRecord(string $value, string $fieldName, string $moduleName, array $expandFields = [])
	{
		$queryGenerator = (new \App\QueryGenerator($moduleName));
		$queryGenerator->permissions = false;
		if ($expandFields) {
			$queryGenerator->setFields(array_merge(['id'], $expandFields));
		} else {
			$queryGenerator->setFields(['id']);
		}
		$queryGenerator->addCondition($fieldName, $value, 'e');
		$queryGenerator->setLimit(2);
		$ids = $queryGenerator->createQuery()->all();
		switch (\count($ids)) {
			case 0:
				$return = null;
				break;
			case 1:
				$return = $expandFields ? $ids[0] : $ids[0]['id'];
				break;
			case 2:
				$list = [
					'Contacts' => 'destination',
				];
				if (isset($list[$moduleName])) {
					$this->urlParams[$list[$moduleName]] = [
						'searchParams' => [$moduleName => [[[$fieldName, 'e', $value]]]],
					];
				}
				$return = 0;
				break;
			default:
				$return = 0;
				break;
		}
		return $return;
	}

	/**
	 * Return data.
	 *
	 * @return void
	 */
	private function returnData(): void
	{
		$view = 'Detail';
		if (!empty($this->contact)) {
			$moduleName = 'Contacts';
			$id = $this->contact;
		} else {
			$moduleName = 'CallHistory';
			$id = $this->interactionModel->getId();
		}
		$params = '';
		if ($this->urlParams) {
			$params = '&' . \http_build_query(['fieldsParams' => \App\Json::encode($this->urlParams)]);
		}
		$this->controller->response->setBody([
			'status' => 1,
			'interactionId' => $this->interactionModel->getId(),
			'url' => \Config\Main::$site_URL . "index.php?module={$moduleName}&view={$view}&record={$id}{$params}",
		]);
	}

	/**
	 * Update interaction.
	 *
	 * @return void
	 *
	 * @OA\Post(
	 *		path="/webservice/PBX/GenesysWdeWhirly/updateInteraction",
	 *		summary="PBX Genesys update interactions",
	 *		tags={"Genesys"},
	 * 		security={{"ApiKeyAuth" : {}}},
	 *		@OA\RequestBody(
	 *			required=true,
	 *			description="Contents of the request contains an associative array with the data.",
	 *			@OA\JsonContent(ref="#/components/schemas/PBX_Genesys_UpdateInteraction_Request"),
	 *		),
	 *		@OA\Response(
	 * 			response=200,
	 * 			description="Correct server response",
	 * 			@OA\JsonContent(ref="#/components/schemas/PBX_Genesys_UpdateInteraction_Response")
	 * 		),
	 *		@OA\Response(response=401, description="Invalid api key"),
	 *		@OA\Response(response=404, description="Method Not Found"),
	 *		@OA\Response(response=500, description="Error", @OA\JsonContent(ref="#/components/schemas/PBX_Genesys_Error")),
	 * ),
	 * @OA\Schema(
	 * 		schema="PBX_Genesys_UpdateInteraction_Request",
	 * 		title="Request for update interactions",
	 *		type="object",
	 *		required={"GenesysIDInteraction", "SessionID", "InteractionEndDateTime", "CallResult", "AgentID"},
	 *  	@OA\Property(property="GenesysIDInteraction", type="string", example="00016aFW01KD009T"),
	 *  	@OA\Property(property="InteractionEndDateTime", type="string", example="2022-10-18T12:55:09.3474109Z"),
	 *  	@OA\Property(property="InteractionHandleTime", type="integer", example=90),
	 *  	@OA\Property(property="DispositionCode", type="string"),
	 *  	@OA\Property(property="AgentName", type="string"),
	 *  	@OA\Property(property="TransferAgentID", type="string"),
	 *  	@OA\Property(property="TransferAgentName", type="string"),
	 *  	@OA\Property(property="CallResult", type="string", example="ended"),
	 *  	@OA\Property(property="PersonalCallback", type="integer"),
	 *  	@OA\Property(property="CRMInteractionID", type="integer"),
	 *  	@OA\Property(property="AgentID", type="string"),
	 *  	@OA\Property(property="CRMPreviousInteractionID", type="integer"),
	 *  	@OA\Property(property="StatusInteraction", type="string"),
	 *  	@OA\Property(property="SessionID", type="string", example="00QG8R2EQS9KT6Q31M0AHG5AES00001P"),
	 * ),
	 * @OA\Schema(
	 *		schema="PBX_Genesys_UpdateInteraction_Response",
	 *		title="Response for update interactions",
	 *		type="object",
	 *		required={"status"},
	 *		@OA\Property(
	 *			property="status",
	 *			type="integer",
	 *			description="That indicates whether the communication is valid. 1 - success , 0 - error",
	 *			example=1
	 *		),
	 * ),
	 */
	private function updateInteraction(): void
	{
		$request = $this->controller->request;
		if (
			!$request->isEmpty('CRMInteractionID')
			&& \App\Record::isExists($request->getInteger('CRMInteractionID'), 'CallHistory')
		) {
			$gwdeId = $request->getInteger('CRMInteractionID');
		} elseif (
			!($gwdeId = $this->findExistRecord($request->getByType('GenesysIDInteraction', 'Alnum'), 'gwde_id', 'CallHistory'))
		) {
			throw new \Api\Core\Exception('Genesys Interaction ID Not Found', 404);
		}
		$this->interactionModel = $recordModel = \Vtiger_Record_Model::getInstanceById($gwdeId, 'CallHistory');
		$recordModel->set(
			'end_time',
			date('Y-m-d H:i:s', strtotime($request->getByType('InteractionEndDateTime', 'Text')))
		);
		$recordModel->set('duration', $request->getByType('InteractionHandleTime', 'Integer'));
		$recordModel->set('gwde_status_interaction', $request->getByType('StatusInteraction', 'Text'));
		$recordModel->set('gwde_personal_callback', $request->getByType('PersonalCallback', 'Text'));
		$recordModel->set('gwde_call_result', $request->getByType('CallResult', 'Text'));
		$recordModel->set('gwde_transfer_agent_id', $request->getByType('TransferAgentID', 'Text'));
		$recordModel->set('agent_login', $request->getByType('AgentID', 'Text'));
		$recordModel->set('sessionid', $request->getByType('SessionID', 'Text'));
		$recordModel->save();
		$this->controller->response->setBody([
			'status' => 1,
		]);
	}

	/**
	 * Register interaction campaign.
	 *
	 * @return void
	 *
	 * @OA\Post(
	 *		path="/webservice/PBX/GenesysWdeWhirly/registerInteractionCampaign",
	 *		summary="PBX Genesys creating interactions for campaign",
	 *		tags={"Genesys"},
	 * 		security={{"ApiKeyAuth" : {}}},
	 *		@OA\RequestBody(
	 *			required=true,
	 *			description="Contents of the request contains an associative array with the data.",
	 *			@OA\JsonContent(ref="#/components/schemas/PBX_Genesys_RegisterInteractionCampaign_Request"),
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="Correct server response",
	 *			@OA\JsonContent(ref="#/components/schemas/PBX_Genesys_RegisterInteractionCampaign_Response"),
	 *		),
	 *		@OA\Response(response=401, description="Invalid api key"),
	 *		@OA\Response(response=404, description="Method Not Found"),
	 *		@OA\Response(response=500, description="Error", @OA\JsonContent(ref="#/components/schemas/PBX_Genesys_Error")),
	 * ),
	 * @OA\Schema(
	 *		schema="PBX_Genesys_RegisterInteractionCampaign_Request",
	 *		title="Request for creating interactions campaign",
	 *		type="object",
	 *		required={"GenesysIDInteraction", "SessionID", "InteractionEndDateTime"},
	 *  	@OA\Property(property="MediaType", type="string", enum={"email", "sms"}, example="email"),
	 *  	@OA\Property(property="ServiceType", type="string"),
	 *  	@OA\Property(property="ServiceValue", type="string"),
	 *  	@OA\Property(property="GenesysIDInteraction", type="string", example="03RGG833ANU72009"),
	 *  	@OA\Property(property="OutboundCallID", type="integer"),
	 *  	@OA\Property(property="DialedNumber", type="string"),
	 *  	@OA\Property(property="CustomerEMail", type="string"),
	 *  	@OA\Property(property="CustomerNIP", type="integer"),
	 *  	@OA\Property(property="TemplateCRMID", type="integer"),
	 *  	@OA\Property(property="InteractionStartDateTime", type="string", example="2022-11-04 09:17:26"),
	 *  	@OA\Property(property="InteractionEndDateTime", type="string", example="2022-11-04 09:17:26"),
	 *
	 * ),
	 * @OA\Schema(
	 * 		schema="PBX_Genesys_RegisterInteractionCampaign_Request",
	 * 		title=" Response for creating interactions campaign",
	 *		type="object",
	 *		required={"status"},
	 *		@OA\Property(
	 *			property="status",
	 *			type="integer",
	 *			description="That indicates whether the communication is valid. 1 - success , 0 - error",
	 *			example=1
	 *		),
	 * ),
	 */
	private function registerInteractionCampaign(): void
	{
		$request = $this->controller->request;
		$this->interactionModel = $recordModel = \Vtiger_Record_Model::getCleanInstance('CallHistory');
		$recordModel->set('assigned_user_id', $this->userId);
		$recordModel->set('from_number', $this->getPhone('DialedNumber'));
		if ($contact = $this->findOrCreateContact('CustomerEMail', 'DialedNumber')) {
			$recordModel->set('contact', $contact);
		}
		$recordModel->set('gwde_id', $request->getByType('GenesysIDInteraction', 'Alnum'));
		$recordModel->set(
			'gwde_outbound_call_id',
			($request->isEmpty('OutboundCallID') ? null : $request->getByType('OutboundCallID', 'Alnum'))
		);
		$recordModel->set('gwde_service_type', $request->getByType('ServiceType', 'Text'));
		$recordModel->set('gwde_service_value', $request->getByType('ServiceValue', 'Text'));
		$recordModel->set('gwde_dialed_number', $request->getByType('DialedNumber', 'Text'));
		$recordModel->set('email', $request->getByType('CustomerEMail', 'Text'));
		$recordModel->set('gwde_customer_nip', ($request->isEmpty('CustomerNIP') ? 0 : $request->getInteger('CustomerNIP')));
		$recordModel->set(
			'start_time',
			date('Y-m-d H:i:s', strtotime($request->getByType('InteractionStartDateTime', 'Text')))
		);
		$recordModel->set(
			'end_time',
			date('Y-m-d H:i:s', strtotime($request->getByType('InteractionEndDateTime', 'Text')))
		);
		$recordModel->set('gwde_media_type', $request->getByType('MediaType', 'AlnumExtended'));
		$recordModel->set('ip_address', $_SERVER['REMOTE_ADDR']);
		$recordModel->save();

		$this->controller->response->setBody([
			'status' => 1,
		]);
	}

	/**
	 * Add log to db.
	 *
	 * @param string $message
	 * @param array  $data
	 *
	 * @return void
	 */
	private function log(string $message, array $data = []): void
	{
		\App\DB::getInstance('log')->createCommand()
			->insert('l_#__pbx', [
				'error' => false,
				'time' => date('Y-m-d H:i:s'),
				'driver' => 'GenesysWdeWhirly',
				'message' => \App\TextUtils::textTruncate($message, 255),
				'params' => $data ? \App\TextUtils::textTruncate(print_r($data, true), 65535) : null,
			])->execute();
	}

	/**
	 * Find an owner for records.
	 *
	 * @return void
	 */
	private function findOwner(): void
	{
		if (!$this->controller->request->isEmpty('TransferAgentID')) {
			$agentID = $this->controller->request->getByType('TransferAgentID', 'Text');
		} elseif (!$this->controller->request->isEmpty('AgentID')) {
			$agentID = $this->controller->request->getByType('AgentID', 'Text');
		}
		if (!empty($agentID) && $id = (new \App\Db\Query())->select(['id'])->from('vtiger_users')
			->where(['gwde_agent_id' => $agentID, 'status' => 'Active'])->scalar()) {
			$this->userId = $id;
		} elseif ('registerInteractionCampaign' === \App\Process::$processName) {
			$this->userId = \App\User::getCurrentUserId();
		} else {
			throw new \Api\Core\Exception('User not found', 404);
		}
	}
}
