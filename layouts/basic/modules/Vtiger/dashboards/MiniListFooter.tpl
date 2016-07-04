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
						<a class="pull-left" href="index.php?module={$MINILIST_WIDGET_MODEL->getTargetModule()}&view=List&mode=showListViewRecords&viewname={$WIDGET->get('filterid')}"><span class="count badge pull-left">{$MINILIST_WIDGET_MODEL->getKeyMetricsWithCount($OWNER)}</span></a>
					</div>
			{if count($MINILIST_WIDGET_RECORDS) >= $MINILIST_WIDGET_MODEL->getRecordLimit()}
					<div class="col-md-8">
						<a class="pull-right" href="index.php?module={$MINILIST_WIDGET_MODEL->getTargetModule()}&view=List&mode=showListViewRecords&viewname={$WIDGET->get('filterid')}">{vtranslate('LBL_MORE')}</a>
					</div>
			{else} &nbsp;
			{/if}
		</div>
	</div>
{/strip}
