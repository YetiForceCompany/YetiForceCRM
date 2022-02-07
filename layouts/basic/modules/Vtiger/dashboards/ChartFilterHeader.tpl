{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-dashboards-ChartFilterHeader -->
	<div class="dashboardWidgetHeader">
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME) CLASSNAME="col-md-6"}
			<div class="d-inline-flex">
				{if $CHART_MODEL->isDownloadable()}
					<button class="btn btn-sm btn-light downloadWidget" data-widgetid="{$CHART_MODEL->get('widgetid')}" title="{\App\Language::translate('LBL_WIDGET_DOWNLOAD','Home')}">
						<span class="far fa-arrow-alt-circle-down"></span>
					</button>&nbsp;
					<button class="btn btn-sm btn-light printWidget" data-widgetid="{$CHART_MODEL->get('widgetid')}" title="{\App\Language::translate('LBL_WIDGET_PRINT','Home')}">
						<span class="fas fa-print"></span>
					</button>&nbsp;
				{/if}
				{if count($CHART_MODEL->getFilterIds())<=1}
					<button class="btn btn-sm btn-light recordCount" data-url="{\App\Purifier::encodeHtml($CHART_MODEL->getTotalCountURL())}" title="{\App\Language::translate('LBL_WIDGET_FILTER_TOTAL_COUNT_INFO')}">
						<span class="fas fa-signal" aria-hidden="false"></span>
						<a class="d-none" aria-hidden="true" href="{\App\Purifier::encodeHtml($CHART_MODEL->getListViewURL())}">
							<span class="count badge badge-secondary"></span>
						</a>
					</button>
				{/if}
				{include file=\App\Layout::getTemplatePath('dashboards/DashboardHeaderIcons.tpl', $MODULE_NAME)}
			</div>
		</div>
		{assign var="WIDGET_DATA" value=$WIDGET->getArray('data')}
		{if !empty($WIDGET_DATA['additionalFiltersFields'])}
			<hr class="widgetHr" />
		{/if}
		{foreach item=FIELD from=$ADDITIONAL_FILTERS_FIELDS key=COUNTER}
			{assign var=FIELD_UI_TYPE_MODEL value=$FIELD->getUITypeModel()}
			{assign var=FIELD_NAME value=$FIELD->getName()}
			{if isset($SEARCH_DETAILS[$FIELD_NAME])}
				{assign var=SEARCH_INFO value=$SEARCH_DETAILS[$FIELD_NAME]}
			{else}
				{assign var=SEARCH_INFO value=[]}
			{/if}
			{if $COUNTER % 2 === 0}<div class="row no-gutters">{/if}
				<div class="col-ceq-xsm-6 input-group-sm">
					{include file=\App\Layout::getTemplatePath($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(), $MODULE_NAME) FIELD_MODEL=$FIELD SEARCH_INFO=$SEARCH_INFO USER_MODEL=$USER_MODEL MODULE=$WIDGET_DATA['module'] CLASS_SIZE='input-group-sm'}
				</div>
				{if $COUNTER % 2 !==0 || $COUNTER===count($ADDITIONAL_FILTERS_FIELDS)-1}
			</div>{/if}
		{/foreach}
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/ChartFilterContents.tpl', $MODULE_NAME) WIDGET=$WIDGET}
	</div>
	<!-- /tpl-dashboards-ChartFilterHeader -->
{/strip}
