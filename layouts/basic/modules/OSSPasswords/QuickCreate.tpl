{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{include file=\App\Layout::getTemplatePath('QuickCreate.tpl', 'Vtiger')}
	<input value="{$CONFIG_PASS['pass_allow_chars']}" type="hidden" id="allowedLetters">
	<input value="{$CONFIG_PASS['pass_length_max']}" type="hidden" id="maxChars">
	<input value="{$CONFIG_PASS['pass_length_min']}" type="hidden" id="minChars">
	<link rel="stylesheet" type="text/css"
		  href="{\App\Layout::getLayoutFile('modules/OSSPasswords/resources/validate_pass.css')}">
{/strip}
