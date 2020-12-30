<?php

/**
 * Cron test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\Base;

class G_Cron extends \Tests\Base
{
	/**
	 * Remove file if using php7.1
	 * Prepare mail config for mail functionalities.
	 *
	 * @codeCoverageIgnore
	 */
	public static function setUpBeforeClass(): void
	{
		if (\App\Version::compare(PHP_VERSION, '7.1.x')) {
			\unlink('app/SystemWarnings/Security/Dependencies.php');
		}
		if (!empty($_SERVER['YETI_MAIL_PASS'])) {
			$db = \App\Db::getInstance();
			$db->createCommand()
				->insert('roundcube_users', [
					'username' => 'yetiforcegitdevelopery@gmail.com',
					'mail_host' => 'imap.gmail.com',
					'language' => 'en_US',
					'preferences' => 'a:1:{s:11:"client_hash";s:16:"UmfW5Tgq7vMU35P0";}',
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
		}
		foreach (['EUR', 'USD', 'GBP', 'CNY'] as $value) {
			$row = (new \App\Db\Query())
				->select(['vtiger_currencies.*'])
				->from('vtiger_currencies')
				->leftJoin('vtiger_currency_info', 'vtiger_currencies.currency_code = vtiger_currency_info.currency_code')
				->where(['vtiger_currencies.currency_code' => $value, 'vtiger_currency_info.currency_code' => null])->one();
			if ($row) {
				unset($row['currencyid']);
				$row['conversion_rate'] = 1;
				$row['currency_status'] = 'Active';
				\App\Db::getInstance()->createCommand()->insert('vtiger_currency_info', $row)->execute();
			}
		}
	}

	/**
	 * Cron testing.
	 */
	public function test()
	{
		\App\Cron::updateStatus(\App\Cron::STATUS_DISABLED, 'OpenStreetMap');
		echo PHP_EOL;
		require_once 'cron.php';
		$rows = (new \App\Db\Query())->select(['modue' => 'setype', 'rows' => 'count(*)'])->from('vtiger_crmentity')->groupBy('setype')->orderBy(['rows' => SORT_DESC])->all();
		$c = '';
		foreach ($rows as $value) {
			$c .= "{$value['modue']} = {$value['rows']}" . PHP_EOL;
		}
		\file_put_contents('tests/records.log', $c, FILE_APPEND);
		$this->assertFalse((new \App\Db\Query())->from('vtiger_cron_task')->where(['status' => 2])->exists());
	}

	/**
	 * Testing last cron start getter.
	 */
	public function testGetLastCronStart()
	{
		$module = \Settings_CronTasks_Module_Model::getInstance('Settings:CronTasks');
		$this->assertNotSame(0, $module->getLastCronStart(), 'Last cron start is 0');
	}
}
