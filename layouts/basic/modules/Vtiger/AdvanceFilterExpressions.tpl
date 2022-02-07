{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if empty($MODULE_MODEL)}
		{assign var=MODULE_MODEL value=Vtiger_Module_Model::getInstance($SOURCE_MODULE)}
	{/if}
	{if empty($DATE_FILTERS)}
		{assign var=DATE_FILTERS value=Vtiger_AdvancedFilter_Helper::getDateFilter($QUALIFIED_MODULE)}
	{/if}
	{if empty($ADVANCED_FILTER_OPTIONS)}
		{assign var=ADVANCED_FILTER_OPTIONS value=Vtiger_AdvancedFilter_Helper::getAdvancedFilterOptions()}
	{/if}
	{if empty($ADVANCED_FILTER_OPTIONS_BY_TYPE)}
		{assign var=ADVANCED_FILTER_OPTIONS_BY_TYPE value=Vtiger_AdvancedFilter_Helper::getAdvancedFilterOpsByFieldType()}
	{/if}
	{if empty($FIELD_EXPRESSIONS)}
		{assign var=FIELD_EXPRESSIONS value=Vtiger_AdvancedFilter_Helper::getExpressions()}
	{/if}
	{if empty($META_VARIABLES)}
		{assign var=META_VARIABLES value=Vtiger_AdvancedFilter_Helper::getMetaVariables()}
	{/if}
	{assign var=COLUMNNAME_API value='getName'}
	<div class="card mb-2">
		<div id="advanceFilterContainer" class="js-conditions-container" data-js="container">
			<div class="card-header">
				<span class="fas fa-random"></span> {\App\Language::translate('LBL_CHOOSE_FILTER_CONDITIONS',$MODULE)}
			</div>
			<div class="card-body">
				{include file=\App\Layout::getTemplatePath('AdvanceFilter.tpl')}
			</div>
			{include file=\App\Layout::getTemplatePath('FieldExpressions.tpl')}
		</div>
	</div>
{/strip}
