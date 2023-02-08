<?php
/**
 * Eraser cli file.
 *
 * @package Cli
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Cli;

/**
 * Eraser cli class.
 */
class Eraser extends Base
{
	/** {@inheritdoc} */
	public $moduleName = 'Eraser data';

	/** @var string[] Methods list */
	public $methods = [
		'mtNonExist' => 'ModTracker - Delete the history of non-existent entries',
		'mtAll' => 'ModTracker - Delete all entries',
		'userEntries' => 'User data - Delete all entries (except modules: MultiCompany,OSSEmployees,BankAccounts,EmailTemplates,SMSTemplates,IStorages,MailAccount)',
	];

	/**
	 * ModTracker - Delete the history of non-existent entries.
	 *
	 * @return void
	 */
	public function mtNonExist(): void
	{
		if ($this->confirmation('Are you sure you want to delete the data?', 'Eraser')) {
			return;
		}
		$this->climate->bold('Deleting data for the vtiger_modtracker_basic table ... ');
		$dbCommand = \App\Db::getInstance()->createCommand();
		$i = $dbCommand->delete(
			'vtiger_modtracker_basic',
			['NOT IN', 'crmid', (new \App\Db\Query())->select(['crmid'])->from('vtiger_crmentity')]
		)->execute();
		$this->climate->bold('Number of deleted entries: ' . $i);
		$this->climate->lightYellow()->border('─', 200);
		$this->climate->bold('Deleting data for the vtiger_modtracker_detail table ... ');
		$i = $dbCommand->delete(
			'vtiger_modtracker_detail',
			['NOT IN', 'id', (new \App\Db\Query())->select(['id'])->from('vtiger_modtracker_basic')]
		)->execute();
		$this->climate->bold('Number of deleted entries: ' . $i);
		$this->climate->lightYellow()->border('─', 200);
		$this->climate->bold('Deleting data for the vtiger_modtracker_relations table ... ');
		$i = $dbCommand->delete(
			'vtiger_modtracker_relations',
			['NOT IN', 'id', (new \App\Db\Query())->select(['id'])->from('vtiger_modtracker_basic')]
		)->execute();
		$this->climate->bold('Number of deleted entries: ' . $i);
		$this->climate->lightYellow()->border('─', 200);
		if (!$this->climate->arguments->defined('action')) {
			$this->cli->actionsList('Eraser');
		}
	}

	/**
	 * ModTracker - Delete all entries.
	 *
	 * @return void
	 */
	public function mtAll(): void
	{
		if ($this->confirmation('Are you sure you want to delete the data?', 'Eraser')) {
			return;
		}
		$this->climate->bold('Deleting data for the vtiger_modtracker_basic table ... ');
		$dbCommand = \App\Db::getInstance()->createCommand();
		$i = $dbCommand->delete('vtiger_modtracker_basic')->execute();
		$this->climate->bold('Number of deleted entries: ' . $i);
		$this->climate->lightYellow()->border('─', 200);
		$this->climate->bold('Deleting data for the vtiger_modtracker_detail table ... ');
		$i = $dbCommand->delete('vtiger_modtracker_detail')->execute();
		$this->climate->bold('Number of deleted entries: ' . $i);
		$this->climate->lightYellow()->border('─', 200);
		$this->climate->bold('Deleting data for the vtiger_modtracker_relations table ... ');
		$i = $dbCommand->delete('vtiger_modtracker_relations')->execute();
		$this->climate->bold('Number of deleted entries: ' . $i);
		$this->climate->lightYellow()->border('─', 200);
		if (!$this->climate->arguments->defined('action')) {
			$this->cli->actionsList('Eraser');
		}
	}

	/**
	 * Delete user entries
	 * Except modules: MultiCompany,OSSEmployees,BankAccounts,EmailTemplates,SMSTemplates,IStorages,MailAccount.
	 *
	 * @return void
	 */
	public function userEntries(): void
	{
		if ($this->confirmation('Are you sure you want to delete the data?', 'Eraser')) {
			return;
		}
		$this->climate->bold('Deleting entries from the database ...');
		$dbCommand = \App\Db::getInstance()->createCommand();
		$i = $dbCommand->delete(
			'vtiger_crmentity',
			[
				'NOT IN', 'setype',
				[
					'MultiCompany', 'OSSEmployees', 'BankAccounts', 'EmailTemplates',
					'SMSTemplates', 'IStorages', 'MailAccount'
				]
			]
		)->execute();
		$this->climate->bold('Number of deleted entries: ' . $i);
		$this->climate->lightYellow()->border('─', 200);
		$this->climate->bold('Deleting data for the dav_addressbookchanges table ... ');
		$i = $dbCommand->delete('dav_addressbookchanges')->execute();
		$this->climate->bold('Number of deleted entries: ' . $i);
		$this->climate->lightYellow()->border('─', 200);
		$this->climate->bold('Deleting data for the dav_calendarchanges table ... ');
		$i = $dbCommand->delete('dav_calendarchanges')->execute();
		$this->climate->bold('Number of deleted entries: ' . $i);
		$this->climate->lightYellow()->border('─', 200);
		$this->climate->bold('Deleting data for the dav_calendarobjects table ... ');
		$i = $dbCommand->delete('dav_calendarobjects')->execute();
		$this->climate->bold('Number of deleted entries: ' . $i);
		$this->climate->lightYellow()->border('─', 200);
		$this->climate->bold('Deleting data for the dav_cards table ... ');
		$i = $dbCommand->delete('dav_cards')->execute();
		$this->climate->bold('Number of deleted entries: ' . $i);

		$dbCommand->update('vtiger_modentity_num', ['cur_id' => 1])->execute();
		if (!$this->climate->arguments->defined('action')) {
			$this->cli->actionsList('Eraser');
		}
	}
}
