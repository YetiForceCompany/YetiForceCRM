<?php
/**
 * Mail test class
 * @package YetiForce.Tests
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers Mail::<public>
 */
class Mail extends TestCase
{

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
				'actions' => 'CreatedEmail,CreatedHelpDesk,BindAccounts,BindContacts,BindLeads,BindHelpDesk,BindSSalesProcesses,BindCampaigns,BindCompetition,BindOSSEmployees,BindPartners,BindProject,BindServiceContracts,BindVendors'
			])->execute();
		$db->createCommand()
			->insert('vtiger_ossmailscanner_folders_uid', [
				'user_id' => '1',
				'type' => 'Received',
				'folder' => 'INBOX',
				'uid' => '1',
			])->execute();
	}
}
