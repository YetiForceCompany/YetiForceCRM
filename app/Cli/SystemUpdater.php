<?php
/**
 * System Updater cli file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Cli;

/**
 * System Updater cli class.
 */
class SystemUpdater extends Base
{
	/** @var string[] Methods list */
	public $methods = [
		'history' => 'History of uploaded updates',
		'update' => 'Update',
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
		$this->cli->actionsList('SystemUpdater');
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
			$options[$package['hash']] = "{$package['label']} ({$package['fromVersion']} >> {$package['toVersion']})";
			if (\App\YetiForce\Updater::isDownloaded($package)) {
				$options[$package['hash']] .= ' - Downloaded, ready to install';
			} else {
				$options[$package['hash']] .= ' - To download';
			}
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
}
