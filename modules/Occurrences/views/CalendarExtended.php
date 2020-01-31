<?php

/**
 * Occurrences calendar view class.
 *
 * @package View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Occurrences_Calendar_View class.
 */
class Occurrences_CalendarExtended_View extends Calendar_CalendarExtended_View
{
	/**
	 * {@inheritdoc}
	 */
	protected function getTpl(string $tplFile)
	{
		return $tplFile;
	}

	public function getFooterScripts(App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();
		return array_merge($headerScriptInstances, $this->checkAndConvertJsScripts([
			"modules.{$moduleName}.resources.CalendarView",
		]));
	}
}
