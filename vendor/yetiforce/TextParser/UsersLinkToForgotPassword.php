<?php
namespace App\TextParser;

/**
 * Users link to forgot password parser class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class UsersLinkToForgotPassword extends Base
{

	/** @var string Class name */
	public $name = 'LBL_USERS_LINK_TO_FORGOT_PASSWORD';

	/** @var array Allowed modules */
	public $allowedModules = ['Users'];

	/**
	 * Process
	 * @return string
	 */
	public function process()
	{
		return '<a target="_blank" href=' . $this->textParser->getParam('trackURL') . '>' . $this->textParser->getParam('trackURL') . '</a>';
	}
}
