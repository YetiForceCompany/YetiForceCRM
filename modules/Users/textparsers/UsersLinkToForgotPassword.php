<?php

/**
 * Users link to forgot password parser class
 * @package YetiForce.TextParser
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
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
