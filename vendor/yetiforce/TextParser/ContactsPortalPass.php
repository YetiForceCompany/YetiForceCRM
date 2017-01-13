<?php
namespace App\TextParser;

/**
 * Contacts portal pass parser class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ContactsPortalPass extends Base
{

	/** @var string Class name */
	public $name = 'LBL_CONTACTS_PORTAL_PASS';

	/** @var array Allowed modules */
	public $allowedModules = ['Contacts'];

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
