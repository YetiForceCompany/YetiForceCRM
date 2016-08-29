{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	<div class="widgetFooterContent">
		<div class="row no-margin">
			{if $OWNER eq false}
				{assign var="MINILIST_WIDGET_RECORDS" value=array()}
			{else}
				{assign var="MINILIST_WIDGET_RECORDS" value=$MINILIST_WIDGET_MODEL->getRecords($OWNER)}
			{/if}
			<div class="col-md-4">
				<button class="btn btn-xs btn-default recordCount" data-url="{Vtiger_Util_Helper::toSafeHTML($MINILIST_WIDGET_MODEL->getGetTotalCountURL($OWNER))}">
					<span class="glyphicon glyphicon-equalizer" title="{vtranslate('LBL_WIDGET_FILTER_TOTAL_COUNT_INFO')}"></span>
					<a class="pull-left hide" href="{Vtiger_Util_Helper::toSafeHTML($MINILIST_WIDGET_MODEL->getListViewURL($OWNER))}"><span class="count badge pull-left"></span></a>
				</button>
			</div>
			{if count($MINILIST_WIDGET_RECORDS) >= $MINILIST_WIDGET_MODEL->getRecordLimit()}
				<div class="col-md-8">
					<a class="pull-right" href="{Vtiger_Util_Helper::toSafeHTML($MINILIST_WIDGET_MODEL->getListViewURL($OWNER))}">{vtranslate('LBL_MORE')}</a>
				</div>
			{else} &nbsp;
			{/if}
		</div>
	</div>
{/strip}
