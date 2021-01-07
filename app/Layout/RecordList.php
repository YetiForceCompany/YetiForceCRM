<?php

/**
 * Layout record list file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */

namespace App\Layout;

/**
 * Layout record list class.
 */
class RecordList
{
	/** Variables to parse @param array */
	public $varsToParse = ['__MODULE_NAME__' => 'dependentModule', '__PARENT_MODULE_NAME__' => 'relatedModule'];
	/** Related module name @param string */
	protected $relatedModule = '';

	/**
	 * Ger record list buttons.
	 *
	 * @param string      $moduleName
	 * @param string|null $relatedModule
	 * @param string      $view
	 * @param string      $side
	 *
	 * @return array
	 */
	public function getRecordListButtons(string $moduleName, ?string $relatedModule, string $view, string $side): array
	{
		$cacheName = "RecordList::getRecordListButtons{$moduleName}_";
		$cacheKey = $view . $side;
		if (\App\Cache::has($cacheName, $cacheKey)) {
			return \App\Cache::get($cacheName, $cacheKey);
		}
		$this->moduleName = $moduleName;
		$where = ['and'];
		$where[] = ['tabid' => \App\Module::getModuleId($moduleName), 'view' => $view, 'side' => $side];
		if ($relatedModule) {
			$this->relatedModule = $relatedModule;
			$where[] = ['or', ['related_tabid' => \App\Module::getModuleId($relatedModule)], ['related_tabid' => null]];
		}
		$recordButtons = (new \App\Db\Query())->from('s_#__record_list_button')->where($where)->orderBy('sequence')->all();
		return \App\Cache::save($cacheName, $cacheKey, $this->createLinks($recordButtons));
	}

	/**
	 * Create links for the buttons.
	 *
	 * @param array $recordButtons
	 *
	 * @return array
	 */
	protected function createLinks(array $recordButtons): array
	{
		$links = [];
		foreach ($recordButtons as $button) {
			if ($this->checkPermissions($button['related_tabid'])) {
				$this->dependentModule = \App\Module::getModuleName($button['dependent_tabid']);
				$this->setVarsToParse();
				$parsedValue = $this->parseSpecialVars($button['params']);
				$buttonParams = \App\Json::decode($parsedValue);
				$this->buttonParam = $buttonParams;
				$links[$buttonParams['linklabel']] = \Vtiger_Link_Model::getInstanceFromValues($buttonParams);
			}
		}
		return $links;
	}

	/**
	 * Set variables to parse.
	 *
	 * @return void
	 */
	public function setVarsToParse(): void
	{
		$this->varsToParse['__MODULE_NAME__'] = $this->dependentModule;
		$this->varsToParse['__PARENT_MODULE_NAME__'] = $this->relatedModule;
	}

	/**
	 * Replace special variables by real names.
	 *
	 * @param string $textToParse
	 *
	 * @return string
	 */
	protected function parseSpecialVars(string $textToParse): string
	{
		foreach ($this->varsToParse as $parseKey => $parseValue) {
			$textToParse = str_replace($parseKey, $parseValue, $textToParse);
		}
		return $textToParse;
	}

	/**
	 * Check permissions to action.
	 *
	 * @return bool
	 */
	public function checkPermissions(): bool
	{
		if (!$this->relatedModule) {
			return true;
		}
		return \App\Privilege::isPermitted($this->relatedModule, 'QuickCreate');
	}
}
