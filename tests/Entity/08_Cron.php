<?php

/**
 * Cron test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Cron extends \Tests\Base
{
	/**
	 * Cron testing.
	 */
	public function test()
	{
		\App\Db::getInstance()->createCommand()
			->update('vtiger_cron_task', [
				'status' => 0,
			], ['module' => 'OpenStreetMap'])
			->execute();
		echo PHP_EOL;
		require_once 'cron.php';
		$rows = (new \App\Db\Query())->select(['modue' => 'setype', 'rows' => 'count(*)'])->from('vtiger_crmentity')->groupBy('setype')->orderBy(['rows' => SORT_DESC])->all();
		$c = '';
		foreach ($rows as $value) {
			$c .= "{$value['modue']} = {$value['rows']}" . PHP_EOL;
		}
		file_put_contents('tests/records.log', $c, FILE_APPEND);
		$this->assertFalse((new \App\Db\Query())->from('vtiger_cron_task')->where(['status' => 2])->exists());
	}
}
