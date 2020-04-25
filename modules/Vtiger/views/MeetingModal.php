<?php
/**
 * Meeting modal view.
 *
 * @package View
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Vtiger_MeetingModal_View class.
 */
class Vtiger_MeetingModal_View extends \App\Controller\Modal
{
	/**
	 * {@inheritdoc}
	 */
	public $modalIcon = 'AdditionalIcon-VideoConference';
	/**
	 * {@inheritdoc}
	 */
	public $successBtn = '';

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		$fieldModel = Vtiger_Module_Model::getInstance($moduleName)->getFieldByName($request->getByType('field', \App\Purifier::ALNUM));
		if ($request->isEmpty('record', true) || !$fieldModel || !$fieldModel->isViewable() || !\App\Privilege::isPermitted($moduleName, 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		$fieldName = $request->getByType('field', \App\Purifier::ALNUM);
		$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$url = $recordModel->get($fieldName);
		$meeting = \App\MeetingService::getInstance();
		$isActive = $meeting->isActive() && $meeting->validateUrl($url);
		$userRoom = '';

		if ($isActive) {
			$data = $meeting->getDataFromUrl($url);
			$userRoom = $meeting->getUrl($data, \App\User::getCurrentUserRealId(), $recordModel->isEditable());
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('MEETING_URL', $userRoom);
		$viewer->assign('MEETING_GUEST_URL', $url);
		$viewer->assign('SEND_INVITATION', $isActive || 0 !== strpos($url, $meeting->get('url')));
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->view('Modals/MeetingModal.tpl', $request->getModule());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPageTitle(App\Request $request)
	{
		$label = \App\Record::getLabel($request->getInteger('record'));
		return $label ? $label : parent::getPageTitle($request);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModalScripts(App\Request $request)
	{
		return array_merge($this->checkAndConvertJsScripts([
			'libraries.clipboard.dist.clipboard'
		]), parent::getModalScripts($request));
	}
}
