<?php
/**
 * Meeting modal view.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Users_MeetingModal_View class.
 */
class Users_MeetingModal_View extends Vtiger_MeetingModal_View
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_VIDEO_CONFERENCE';

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$userId = \App\User::getCurrentUserRealId();
		if ($userId !== $request->getInteger('record') || !\App\MeetingService::getInstance()->isActive() || !\App\Privilege::isPermitted($request->getModule(), 'MeetingUrl', false, $userId)) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_PERMISSION_DENIED', 406);
		}
	}

	public function initMeetingData(App\Request $request)
	{
		$meeting = \App\MeetingService::getInstance();
		$this->meetingUrl = $meeting->getUrl(['exp' => strtotime('+48 hours')]);
		$this->moderator = true;
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		return \App\Language::translate($this->pageTitle, $request->getModule());
	}
}
