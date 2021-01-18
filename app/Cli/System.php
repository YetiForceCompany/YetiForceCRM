<?php
/**
 * System cli file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Cli;

/**
 * System cli class.
 */
class System extends Base
{
	/** {@inheritdoc} */
	public $moduleName = 'System';

	/** @var string[] Methods list */
	public $methods = [
		'history' => 'History of uploaded updates',
		'update' => 'Update',
		'checkRegStatus' => 'Check registration status',
		'reloadModule' => 'Reload modules',
		'clearCache' => 'Clear cache',
		'reloadUserPrivileges' => 'Reload users privileges',
	];

	/**
	 * History of uploaded updates.
	 *
	 * @return void
	 */
	public function history(): void
	{
		$table = array_map(function ($item) {
			$item['result'] = $item['result'] ? 'OK' : 'Error';
			unset($item['id']);
			return $item;
		}, \Settings_Updates_Module_Model::getUpdates());
		if ($table) {
			$this->climate->table($table);
		}
		$this->cli->actionsList('System');
	}

	/**
	 * Update.
	 *
	 * @return void
	 */
	public function update(): void
	{
		$maxExecutionTime = ini_get('max_execution_time');
		if ($maxExecutionTime < 1 || $maxExecutionTime > 600) {
			$this->climate->lightGreen('Max execution time = ' . $maxExecutionTime);
		} else {
			$this->climate->lightRed('Max execution time = ' . $maxExecutionTime);
		}
		$options = [];
		$toInstall = \App\YetiForce\Updater::getToInstall();
		foreach ($toInstall as $package) {
			$option = "{$package['label']}";
			if ($package['fromVersion'] !== $package['toVersion']) {
				$option .= " ({$package['fromVersion']} >> {$package['toVersion']})";
			}
			if (\App\YetiForce\Updater::isDownloaded($package)) {
				$option .= ' - Downloaded, ready to install';
			} else {
				$option .= ' - To download';
			}
			$options[$package['hash']] = $option;
		}
		if (!$options) {
			$this->climate->lightBlue('No updates available');
			return;
		}
		$input = $this->climate->radio('Updates available:', $options);
		$hash = $input->prompt();
		foreach ($toInstall as $package) {
			if ($package['hash'] === $hash) {
				if (\App\YetiForce\Updater::isDownloaded($package)) {
					$startTime = microtime(true);
					try {
						$packageInstance = new \vtlib\Package();
						$response = $packageInstance->import(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . \Settings_ModuleManager_Module_Model::getUploadDirectory() . \DIRECTORY_SEPARATOR . $package['hash'] . '.zip', true);
						if ($packageInstance->_errorText) {
							$this->climate->lightRed($packageInstance->_errorText);
						} else {
							echo $response;
						}
					} catch (\Throwable $th) {
						$this->climate->lightRed($th->__toString());
					}
					$this->climate->lightBlue('Update time: ' . round(microtime(true) - $startTime, 2));
					$this->climate->lightBlue('Check the update logs: cache/logs/update.log');
				} else {
					\App\YetiForce\Updater::download($package);
					$this->update();
				}
				return;
			}
		}
	}

	/**
	 * Check registration status.
	 *
	 * @return void
	 */
	public function checkRegStatus(): void
	{
		$status = \App\YetiForce\Register::check(true);
		$this->climate->bold('Status: ' . \App\Language::translate(\App\YetiForce\Register::STATUS_MESSAGES[$status], 'Settings::Companies'));
		$this->climate->border('─', 200);
		$this->climate->bold('APP ID: ' . \App\YetiForce\Register::getInstanceKey());
		$this->climate->border('─', 200);
		$this->climate->bold('CRM ID: ' . \App\YetiForce\Register::getCrmKey());
		$this->climate->border('─', 200);
		$this->climate->bold('Provider: ' . \App\YetiForce\Register::getProvider());
		$this->climate->border('─', 200);
		$this->cli->actionsList('System');
	}

	/**
	 * Check registration status.
	 *
	 * @return void
	 */
	public function reloadModule(): void
	{
		$this->climate->bold('Tools: ' . \App\Db\Fixer::baseModuleTools());
		$this->climate->bold('Actions: ' . \App\Db\Fixer::baseModuleActions());
		$this->climate->bold('Profile field: ' . \App\Db\Fixer::profileField());
		$this->climate->bold('Share: ' . \App\Db\Fixer::share());
		\App\Module::createModuleMetaFile();
		$this->climate->bold('Create module meta file');
		\App\Colors::generate();
		$this->climate->bold('Colors');
		$this->cli->actionsList('System');
	}

	/**
	 * Check registration status.
	 *
	 * @return void
	 */
	public function clearCache(): void
	{
		$this->climate->bold('Clear: ' . \App\Cache::clear());
		$this->climate->bold('Clear opcache: ' . \App\Cache::clearOpcache());
		$this->cli->actionsList('System');
	}

	/**
	 * Check registration status.
	 *
	 * @return void
	 */
	public function reloadUserPrivileges(): void
	{
		$this->climate->bold('Users: ' . \App\UserPrivilegesFile::recalculateAll());
		$this->cli->actionsList('System');
	}
}
