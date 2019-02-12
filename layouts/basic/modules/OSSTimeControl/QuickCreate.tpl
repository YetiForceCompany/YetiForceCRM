{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{include file=\App\Layout::getTemplatePath('QuickCreate.tpl', 'Vtiger')}
	<input value="{AppConfig::module('OSSTimeControl', 'DISALLOW_LONGER_THAN_24_HOURS')}" type="hidden"
		   id="disallowLongerThan24Hours">
{/strip}
