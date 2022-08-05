<?php
/**
 * Environment cli file.
 *
 * @package Cli
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Cli;

/**
 * Environment cli class.
 */
class Environment extends Base
{
	/** {@inheritdoc} */
	public $moduleName = 'Environment';

	/** @var string[] Methods list */
	public $methods = [
		'confReportErrors' => 'Server configuration - Errors',
		'confReportAll' => 'Server configuration - All',
	];

	/**
	 * Server configuration - Errors.
	 *
	 * @return void
	 */
	public function confReportErrors(): void
	{
		\App\Process::$requestMode = 'Cron';
		\App\Utils\ConfReport::$sapi = 'cron';
		foreach (\App\Utils\ConfReport::$types as $type) {
			$table = [];
			foreach (\App\Utils\ConfReport::getErrors($type, true) as $name => $item) {
				if (!empty($item['isHtml'])) {
					$this->htmlToText($item);
				}
				$table[] = [
					'Parameter' => $name,
					'Recommended' => $item['recommended'] ?? '-',
					'Value' => $item['cron'] ?? '-',
				];
			}
			if ($table) {
				$this->climate->border('─', 140);
				$this->climate->underline()->bold()->out("		$type");
				$this->climate->border('─', 140);
				$this->climate->table($table);
			}
		}
	}

	/**
	 * Server configuration - All.
	 *
	 * @return void
	 */
	public function confReportAll(): void
	{
		\App\Process::$requestMode = 'Cron';
		\App\Utils\ConfReport::$sapi = 'cron';
		$all = \App\Utils\ConfReport::getAll();
		foreach ($all as $type => $items) {
			$table = [];
			foreach ($items as $name => $item) {
				if (!$item['testCli']) {
					continue;
				}
				if (!empty($item['isHtml'])) {
					$this->htmlToText($item);
				}
				$value = $item['cron'] ?? $item['www'] ?? '';
				if (0 === strpos($value, 'LBL_')) {
					$value = \App\Language::translate($value);
				}
				$value = \is_array($value) ? \App\Json::encode($value) : $value;
				$table[] = [
					'Parameter' => $item['status'] ? $name : "<light_red>{$name}</light_red>",
					'Recommended' => empty($item['recommended']) ? '-' : print_r($item['recommended'], true),
					'Value' => $item['status'] ? $value : ("<light_red>{$value}</light_red>"),
				];
			}
			if ($table) {
				$this->climate->border('─', 140);
				$this->climate->underline()->bold()->out("		$type");
				$this->climate->border('─', 140);
				$this->climate->table($table);
			}
		}
	}

	/**
	 * Convert html to cli text.
	 *
	 * @param array &$item
	 *
	 * @return void
	 */
	public function htmlToText(array &$item): void
	{
		if (false !== strpos($item['val'], '<b class="text-danger">')) {
			$item['val'] = preg_replace_callback("'<b class=\"text-danger\">(.*?)</b>'si", fn ($match) => "<light_red>{$match['1']}</light_red>", $item['val']);
		}
		if (false !== strpos($item['recommended'], '<b class="text-danger">')) {
			$item['recommended'] = preg_replace_callback("'<b class=\"text-danger\">(.*?)</b>'si", fn ($match) => "<light_red>{$match['1']}</light_red>", $item['recommended']);
		}
	}
}
