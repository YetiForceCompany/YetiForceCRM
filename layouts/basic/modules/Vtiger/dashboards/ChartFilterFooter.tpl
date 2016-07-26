{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	<div class="widgetFooterContent">
		<div class="row no-margin">
			<div class="col-md-4">
				{assign var="COUNT" value=$CHART_MODEL->getKeyMetricsWithCount()}
				<a class="pull-left" href="{Vtiger_Util_Helper::toSafeHTML($CHART_MODEL->getListViewURL())}">{if $COUNT neq false}<span class="count badge pull-left">{$COUNT}</span>{else}&nbsp;{/if} </a>
			</div>
		</div>
	</div>
{/strip}
