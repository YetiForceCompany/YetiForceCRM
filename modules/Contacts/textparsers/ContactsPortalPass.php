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
		if (isset($this->textParser->record)) {
			$password = (new \App\Db\Query())->select(['password_sent'])
					->from('vtiger_portalinfo')->where(['id' => $this->textParser->record])->limit(1)->scalar();
			if ($password) {
				return $password;
			}
		}
		return '';
	}
}
