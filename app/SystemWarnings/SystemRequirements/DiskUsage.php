<?php

/**
 * Disk usage system warnings file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\SystemWarnings\SystemRequirements;

/**
 * Disk usage system warnings class.
 */
class DiskUsage extends \App\SystemWarnings\Template
{
	/** {@inheritdoc} */
	protected $title = 'LBL_DISK_USAGE';

	/** {@inheritdoc} */
	protected $priority = 9;

	/**
	 * Check disk space.
	 *
	 * @return void
	 */
	public function process(): void
	{
		$this->status = 1;
		$envInfo = \App\Utils\ConfReport::get('environment');
		if (!$envInfo['spaceRoot']['status'] || !$envInfo['spaceStorage']['status'] || !$envInfo['spaceTemp']['status'] || !$envInfo['spaceBackup']['status']) {
			$this->status = 0;
		}
		if (!$this->status) {
			$this->description = \App\Language::translateArgs('LBL_DISK_FULL', 'Settings:SystemWarnings', $envInfo['spaceRoot']['www'], $envInfo['spaceRoot']['www'], $envInfo['spaceStorage']['www'], $envInfo['spaceTemp']['www']);
		}
	}
}
