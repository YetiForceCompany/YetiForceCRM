<?php

/**
 * Contacts portal pass parser class
 * @package YetiForce.TextParser
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Contacts_ContactsPortalPass_TextParser extends \App\TextParser\Base
{

	/** @var string Class name */
	public $name = 'LBL_CONTACTS_PORTAL_PASS';

	/** @var mixed Parser type */
	public $type = 'mail';

	/**
	 * Process
	 * @return string
	 */
	public function process()
	{
		return '';
	}
}
