<?php

/**
 * Special function to display the meeting URL.
 *
 * @package Textparser
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Users_MeetingUrl_Textparser extends \App\TextParser\Base
{
	/** @var string Class name */
	public $name = 'LBL_MEETING_URL';

	/** @var string Parser type */
	public $type = 'mail';

	/**
	 * Process.
	 *
	 * @return string
	 */
	public function process()
	{
		return $this->textParser->getParam('meetingUrl');
	}
}
