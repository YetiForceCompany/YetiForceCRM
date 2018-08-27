<?php

namespace App\SystemWarnings\SystemRequirements;

/**
 * Disk usage system warnings class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class DiskUsage extends \App\SystemWarnings\Template
{
	protected $title = 'LBL_DISK_USAGE';
	protected $priority = 9;
	protected $leftAlert = 1024 * 1024 * 1024; // bytes = 1GB

	/**
	 * Check disk space.
	 */
	public function process()
	{
		$this->status = 1;
		$envInfo = \App\Utils\ConfReport::get('environment');
		if (!$envInfo['spaceRoot']['status'] || !$envInfo['spaceStorage']['status'] || !$envInfo['spaceTemp']['status']) {
			$this->status = 0;
		}
		if ($this->status === 0) {
			$this->description = \App\Language::translateArgs('LBL_DISK_FULL', 'Settings:SystemWarnings', $envInfo['spaceRoot']['www'] ?? $envInfo['spaceRoot']['cron'], $envInfo['spaceRoot']['www'] ?? $envInfo['spaceRoot']['cron'], $envInfo['spaceStorage']['www'] ?? $envInfo['spaceStorage']['cron'], $envInfo['spaceTemp']['www'] ?? $envInfo['spaceTemp']['cron']);
		}
	}
}
