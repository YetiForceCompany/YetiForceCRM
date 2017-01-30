<?php

/**
 * Users link to forgot password parser class
 * @package YetiForce.TextParser
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Users_UsersLinkToForgotPassword_TextParser extends \App\TextParser\Base
{

	/** @var string Class name */
	public $name = 'LBL_USERS_LINK_TO_FORGOT_PASSWORD';

	/** @var mixed Parser type */
	public $type = 'mail';

	/**
	 * Process
	 * @return string
	 */
	public function process()
	{
		return '<a target="_blank" href=' . $this->textParser->getParam('trackURL') . '>' . $this->textParser->getParam('trackURL') . '</a>';
	}
}
