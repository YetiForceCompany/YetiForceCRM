{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-ConditionBuilder-Duplicate">
		<label>
			{\App\Language::translate('LBL_IGNORE_EMPTY_VALUES', $MODULE_NAME)}
			{include file=\App\Layout::getTemplatePath('ConditionBuilder\Boolean.tpl', $MODULE_NAME)}
		</label>
	</div>
{/strip}
