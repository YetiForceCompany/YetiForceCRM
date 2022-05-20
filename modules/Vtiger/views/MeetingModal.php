<?php
/**
 * Meeting modal view.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Vtiger_MeetingModal_View class.
 */
class Vtiger_MeetingModal_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $modalIcon = 'mdi mdi-card-account-phone';
	/** {@inheritdoc} */
	public $successBtn = '';
	/** {@inheritdoc} */
	public $showFooter = false;
	/**
	 * @var string Meeting URL
	 */
	protected $meetingUrl = '';
	/**
	 * @var bool Moderator
	 */
	protected $moderator = false;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		$fieldModel = Vtiger_Module_Model::getInstance($moduleName)->getFieldByName($request->getByType('field', \App\Purifier::ALNUM));
		if ($request->isEmpty('record', true) || !$fieldModel || !$fieldModel->isViewable() || !\App\Privilege::isPermitted($moduleName, 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$this->initMeetingData($request);
		$url = $this->meetingUrl;
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');

		$meeting = \App\MeetingService::getInstance();
		$isActive = $meeting->isActive() && $meeting->validateUrl($url);
		$templateData = $userRoomUrl = '';
		$simpleUrl = $meeting->isEmpty('url') || 0 !== strpos($url, $meeting->get('url'));
		if ($isActive) {
			$data = $meeting->getDataFromUrl($url);
			$userRoomUrl = $meeting->getUrl($data, \App\User::getCurrentUserRealId(), $this->moderator);
			if (!empty($data['exp'])) {
				$date = new \DateTime();
				$date->setTimestamp($data['exp']);
				$date->setTimezone(new \DateTimeZone(\App\User::getCurrentUserModel()->getDetail('time_zone')));
				$expires = $date->format('Y-m-d H:i:s (T P)');
			}
		}
		$sendInvitation = ($isActive || $simpleUrl) && \App\Mail::checkInternalMailClient();
		$templateId = \App\Config::component('MeetingService', 'emailTemplateModule', [])[$moduleName] ?? \App\Config::component('MeetingService', 'emailTemplateDefault', 0);
		if ($sendInvitation && $templateId && ($template = \App\Mail::getTemplate($templateId, false))) {
			$textParser = \App\TextParser::getInstanceById($recordId, $moduleName);
			$textParser->setParam('meetingUrl', $url);
			$textParser->setParam('meetingExpires', $expires ?? '');
			$templateData = $textParser->setContent(\App\Utils\Completions::decode(\App\Purifier::purifyHtml($template['content'])))->parse()->getContent();
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('MEETING_URL', $userRoomUrl);
		$viewer->assign('MEETING_GUEST_URL', $url);
		$viewer->assign('SEND_INVITATION', $sendInvitation && $templateData);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('SIMPLE_URL', $simpleUrl);
		$viewer->assign('EMAIL_TEMPLATE', $templateData ? $templateId : 0);
		$viewer->assign('EMAIL_TEMPLATE_DATA', $templateData);
		$viewer->assign('TEMPLATE_PARAMS', \App\Json::encode(['meetingUrl' => $url, 'meetingExpires' => $expires ?? '']));
		$viewer->view('Modals/MeetingModal.tpl', $request->getModule());
	}

	/**
	 * Initiation.
	 *
	 * @param App\Request $request
	 */
	public function initMeetingData(App\Request $request)
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		$fieldName = $request->getByType('field', \App\Purifier::ALNUM);
		$this->meetingUrl = $recordModel->get($fieldName);
		$this->moderator = $recordModel->isEditable();
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		$label = \App\Record::getLabel($request->getInteger('record'));
		return $label ? $label : parent::getPageTitle($request);
	}

	/** {@inheritdoc} */
	public function getModalScripts(App\Request $request)
	{
		return array_merge($this->checkAndConvertJsScripts([
			'libraries.clipboard.dist.clipboard'
		]), parent::getModalScripts($request));
	}
}
