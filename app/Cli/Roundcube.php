<?php
/**
 * Roundcube cli file.
 *
 * @package Cli
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Cli;

/**
 * Roundcube cli class.
 */
class Roundcube extends Base
{
	/** {@inheritdoc} */
	public $moduleName = 'Roundcube';

	/** @var string[] Methods list */
	public $methods = [
		'clearUsersPreferences' => 'Clear users preferences',
		'clearUsersCache' => 'Clear users cache',
		'clearUsersSession' => 'Clear users session',
	];

	/**
	 * Clear users preferences.
	 *
	 * @return void
	 */
	public function clearUsersPreferences(): void
	{
		\App\Db::getInstance()->createCommand()->update('roundcube_users', ['preferences' => ''])->execute();
		if (!$this->climate->arguments->defined('action')) {
			$this->cli->actionsList('Roundcube');
		}
	}

	/**
	 * Clear users cache.
	 *
	 * @return void
	 */
	public function clearUsersCache(): void
	{
		$createCommand = \App\Db::getInstance()->createCommand();
		foreach (['roundcube_cache', 'roundcube_cache_index', 'roundcube_cache_messages', 'roundcube_cache_shared', 'roundcube_cache_thread'] as $table) {
			$createCommand->truncateTable($table)->execute();
		}
		if (!$this->climate->arguments->defined('action')) {
			$this->cli->actionsList('Roundcube');
		}
	}

	/**
	 * Clear users session.
	 *
	 * @return void
	 */
	public function clearUsersSession(): void
	{
		\App\Db::getInstance()->createCommand()->truncateTable('roundcube_session')->execute();
		if (!$this->climate->arguments->defined('action')) {
			$this->cli->actionsList('Roundcube');
		}
	}
}
