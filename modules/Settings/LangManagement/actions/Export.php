<?php

/**
 * Settings LangManagement export action class
 * @package YetiForce.Action
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_LangManagement_Export_Action extends Settings_Vtiger_IndexAjax_View
{

	public function process(\App\Request $request)
	{
		$lang = $request->get('lang');

		$package = new vtlib\LanguageExport();
		$package->export($lang, '', $lang . '.zip', true);
	}
}
