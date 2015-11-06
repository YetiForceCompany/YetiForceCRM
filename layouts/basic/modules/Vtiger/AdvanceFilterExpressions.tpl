{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if !$MODULE_MODEL}
		{assign var=MODULE_MODEL value=Vtiger_Module_Model::getInstance($SOURCE_MODULE)}
	{/if}
	{if !$DATE_FILTERS}
		{assign var=DATE_FILTERS value=Vtiger_AdvancedFilter_Helper::getDateFilter($QUALIFIED_MODULE)}
	{/if}
	{if !$ADVANCED_FILTER_OPTIONS}
		{assign var=ADVANCED_FILTER_OPTIONS value=Vtiger_AdvancedFilter_Helper::getAdvancedFilterOptions()}
	{/if}
	{if !$ADVANCED_FILTER_OPTIONS_BY_TYPE}
		{assign var=ADVANCED_FILTER_OPTIONS_BY_TYPE value=Vtiger_AdvancedFilter_Helper::getAdvancedFilterOpsByFieldType()}
	{/if}
	{if !$FIELD_EXPRESSIONS}
		{assign var=FIELD_EXPRESSIONS value=Vtiger_AdvancedFilter_Helper::getExpressions()}
	{/if}
	{if !$META_VARIABLES}
		{assign var=META_VARIABLES value=Vtiger_AdvancedFilter_Helper::getMetaVariables()}
	{/if}
	{assign var=COLUMNNAME_API value='getName'}
	<div class="padding1per stepBorder">
		<div id="advanceFilterContainer" class="row conditionsContainer padding1per">
			<h5 class="padding-bottom1per col-md-10"><strong>{vtranslate('LBL_CHOOSE_FILTER_CONDITIONS',$MODULE)}</strong></h5>
			<div class="col-md-10" >
				{include file='AdvanceFilter.tpl'|@vtemplate_path}
			</div>
			{include file="FieldExpressions.tpl"|@vtemplate_path}
		</div>
	</div>
{/strip}
