{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if count($DATA['chart']) gt 0 }
		<div class="clearfix"></div>
		<div class="widgetChartContainer" style="height:100%;width:98%"></div>
	{else}
		<span class="noDataMsg">
			{vtranslate('LBL_NO_RECORDS_MATCHED_THIS_CRITERIA')}
		</span>
	{/if}
	<input class="widgetData" type="hidden" value="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($DATA))}"/>
{/strip}
