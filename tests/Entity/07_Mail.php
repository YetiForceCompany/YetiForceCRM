<?php

/**
 * Mail test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Mail extends \Tests\Base
{
	/**
	 * Load configuration.
	 */
	public function testLoadConfig()
	{
		$db = \App\Db::getInstance();
		$db->createCommand()
			->insert('roundcube_users', [
				'username' => 'yetiforcetestmail@gmail.com',
				'mail_host' => 'imap.gmail.com',
				'language' => 'en_US',
				'preferences' => 'a:3:{s:9:"junk_mbox";s:12:"[Gmail]/Spam";s:10:"trash_mbox";s:12:"[Gmail]/Kosz";s:11:"client_hash";s:32:"0e1f51526f56ef769dbd1f58a674f106";}',
				'password' => $_SERVER['YETI_MAIL_PASS'],
				'crm_user_id' => '1',
				'actions' => 'CreatedEmail,CreatedHelpDesk,BindAccounts,BindContacts,BindLeads,BindHelpDesk,BindSSalesProcesses,BindCampaigns,BindCompetition,BindOSSEmployees,BindPartners,BindProject,BindServiceContracts,BindVendors',
			])->execute();
		$db->createCommand()
			->insert('vtiger_ossmailscanner_folders_uid', [
				'user_id' => '1',
				'type' => 'Received',
				'folder' => 'INBOX',
				'uid' => '0',
			])->execute();
		$this->assertTrue(true);
	}
}
