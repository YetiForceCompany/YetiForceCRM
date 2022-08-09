<?php
/**
 * System cli file.
 *
 * @package Cli
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		'deleteRegistration' => 'Delete registration data',
		'showProducts' => 'Show active products',
		'reloadModule' => 'Reload modules',
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
		} else {
			$this->climate->lightGreen('No updates');
		}
		if (!$this->climate->arguments->defined('action')) {
			$this->cli->actionsList('System');
		}
	}

	/**
	 * Update CRM.
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
		$this->climate->arguments->add([
			'type' => [
				'prefix' => 't',
				'description' => 'Update type. Values: patches, version',
			],
		]);
		if ($this->helpMode) {
			return;
		}
		$this->climate->arguments->parse();
		if ($this->climate->arguments->defined('type')) {
			$this->updateByType($this->climate->arguments->get('type'));
		} else {
			$this->updateBySelect();
		}
	}

	/**
	 * Update by package type.
	 *
	 * @param string $type Package type. Values: patches, version
	 *
	 * @return void
	 */
	private function updateByType(string $type): void
	{
		$types = ['patches', 'version'];
		if (!\in_array($this->climate->arguments->get('type'), $types)) {
			$this->climate->white('Type not found. Allowed types:')->columns($types);
			return;
		}
		$versionUpdate = 'version' === $type;
		foreach (\App\YetiForce\Updater::getToInstall() as $package) {
			$versionCompare = $package['fromVersion'] !== $package['toVersion'];
			if (($versionCompare && !$versionUpdate) || (!$versionCompare && $versionUpdate)) {
				continue;
			}
			if (!\App\YetiForce\Updater::isDownloaded($package)) {
				$this->climate->inline($package['label'] . ' - Downloading a package ...');
				\App\YetiForce\Updater::download($package);
				$this->climate->out('- downloaded');
			}
			$this->updateByPackage($package);
		}
	}

	/**
	 * Update by selecting a package.
	 *
	 * @return void
	 */
	private function updateBySelect(): void
	{
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
					$this->updateByPackage($package);
				} else {
					\App\YetiForce\Updater::download($package);
					$this->update();
				}
				return;
			}
		}
	}

	/**
	 * Update package.
	 *
	 * @param array $package
	 *
	 * @return void
	 */
	private function updateByPackage(array $package): void
	{
		$startTime = microtime(true);
		try {
			$packageInstance = new \vtlib\Package();
			$this->climate->white($package['label'] . ' - Installing the package');
			$response = $packageInstance->import(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . \Settings_ModuleManager_Module_Model::getUploadDirectory() . \DIRECTORY_SEPARATOR . $package['hash'] . '.zip', true);
			if ($packageInstance->_errorText) {
				$this->climate->lightRed($packageInstance->_errorText);
			} else {
				echo $response . PHP_EOL;
			}
		} catch (\Throwable $th) {
			$this->climate->lightRed($th->__toString());
		}
		$this->climate->lightBlue('Total time: ' . round(microtime(true) - $startTime, 2));
	}

	/**
	 * Check registration status.
	 *
	 * @return void
	 */
	public function checkRegStatus(): void
	{
		\App\YetiForce\Register::check(true);
		$this->climate->bold('Status: ' . \App\Language::translate(\App\YetiForce\Register::STATUS_MESSAGES[\App\YetiForce\Register::getStatus()], 'Settings::Companies'));
		$this->climate->border('─', 200);
		$this->climate->bold('APP ID: ' . \App\YetiForce\Register::getInstanceKey());
		$this->climate->border('─', 200);
		$this->climate->bold('CRM ID: ' . \App\YetiForce\Register::getCrmKey());
		$this->climate->border('─', 200);
		$this->climate->bold('Provider: ' . \App\YetiForce\Register::getProvider());
		$this->climate->border('─', 200);
		$table = [];
		foreach (\App\Company::getAll() as $row) {
			$table[] = [
				'name' => $row['name'],
				'status' => $row['status'],
				'type' => $row['type'],
				'companysize' => $row['companysize'],
				'vat_id' => $row['vat_id'],
			];
		}
		$this->climate->table($table);
		$this->climate->border('─', 200);
		if (!$this->climate->arguments->defined('action')) {
			$this->cli->actionsList('System');
		}
	}

	/**
	 * Show active products.
	 *
	 * @return void
	 */
	public function showProducts(): void
	{
		$table = [];
		foreach (\App\YetiForce\Register::getProducts() as $row) {
			$row['params'] = \App\Utils::varExport($row['params']);
			$table[] = $row;
		}
		$table ? $this->climate->table($table) : $this->climate->bold('None');
		$this->climate->border('─', 200);
		if (!$this->climate->arguments->defined('action')) {
			$this->cli->actionsList('System');
		}
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
		$this->climate->lightYellow()->border('─', 200);
		if (!$this->climate->arguments->defined('action')) {
			$this->cli->actionsList('System');
		}
	}

	/**
	 * Check registration status.
	 *
	 * @return void
	 */
	public function reloadUserPrivileges(): void
	{
		$this->climate->bold('Users: ' . \App\UserPrivilegesFile::recalculateAll());
		$this->climate->lightYellow()->border('─', 200);
		if (!$this->climate->arguments->defined('action')) {
			$this->cli->actionsList('System');
		}
	}

	/**
	 * Delete registration data.
	 *
	 * @return void
	 */
	public function deleteRegistration(): void
	{
		\App\Db::getInstance('admin')->createCommand()->update('s_#__companies', [
			'status' => 0,
			'name' => '', 'industry' => '', 'vat_id' => '', 'city' => '', 'address' => '',
			'post_code' => '', 'country' => '', 'companysize' => '', 'website' => '', 'logo' => '',
			'firstname' => '', 'lastname' => '', 'email' => '', 'facebook' => '', 'twitter' => '', 'linkedin' => '',
		])->execute();
	}
}
