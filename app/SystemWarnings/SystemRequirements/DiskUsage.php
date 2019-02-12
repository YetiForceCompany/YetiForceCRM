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
	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	protected $title = 'LBL_DISK_USAGE';
	/**
	 * {@inheritdoc}
	 *
	 * @var int
	 */
	protected $priority = 9;

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
		if (!$this->status) {
			$this->description = \App\Language::translateArgs('LBL_DISK_FULL', 'Settings:SystemWarnings', $envInfo['spaceRoot']['www'], $envInfo['spaceRoot']['www'], $envInfo['spaceStorage']['www'], $envInfo['spaceTemp']['www']);
		}
	}
}
