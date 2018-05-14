{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="dashboardWidgetHeader">
		<div class="row">
			<div class="col-md-7">
				<h5 class="dashboardTitle h6"
					 title="{App\Purifier::encodeHtml(App\Language::translate($WIDGET->getTitle(), $MODULE_NAME))}">
					<strong>&nbsp;&nbsp;{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}</strong></h5>
			</div>
			<div class="col-md-5">
				<div class="box float-right">
					<button class="btn btn-sm btn-light downloadWidget hidden"
							data-widgetid="{$CHART_MODEL->get('widgetid')}"
							title="{\App\Language::translate('LBL_WIDGET_DOWNLOAD','Home')}">
						<span class="far fa-arrow-alt-circle-down"></span>
					</button>&nbsp;
					<button class="btn btn-sm btn-light printWidget hidden"
							data-widgetid="{$CHART_MODEL->get('widgetid')}"
							title="{\App\Language::translate('LBL_WIDGET_PRINT','Home')}">
						<span class="fas fa-print"></span>
					</button>&nbsp;
					{if count($CHART_MODEL->getFilterIds())<=1}
						<button class="btn btn-sm btn-light recordCount"
								data-url="{\App\Purifier::encodeHtml($CHART_MODEL->getTotalCountURL())}"
								title="{\App\Language::translate('LBL_WIDGET_FILTER_TOTAL_COUNT_INFO')}">
							<span class="fas fa-signal" aria-hidden="false"></span>
							<a class="d-none" aria-hidden="true"
							   href="{\App\Purifier::encodeHtml($CHART_MODEL->getListViewURL())}">
								<span class="count badge badge-secondary"></span>
							</a>
						</button>
					{/if}
					{include file=\App\Layout::getTemplatePath('dashboards/DashboardHeaderIcons.tpl', $MODULE_NAME)}
				</div>
			</div>
		</div>
		{assign var="WIDGET_DATA" value=$WIDGET->getArray('data')}
		{if !empty($WIDGET_DATA['additionalFiltersFields'])}
			<hr class="widgetHr"/>
		{/if}
		{foreach item=FIELD from=$ADDITIONAL_FILTERS_FIELDS key=COUNTER}
			{assign var=FIELD_UI_TYPE_MODEL value=$FIELD->getUITypeModel()}
			{assign var=FIELD_NAME value=$FIELD->getName()}
			{if isset($SEARCH_DETAILS[$FIELD_NAME])}
				{assign var=SEARCH_INFO value=$SEARCH_DETAILS[$FIELD_NAME]}
			{else}
				{assign var=SEARCH_INFO value=[]}
			{/if}
			{if $COUNTER % 2 === 0}<div class="row">{/if}
				<div class="col-md-6">
					{include file=\App\Layout::getTemplatePath($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(), $MODULE_NAME)
					FIELD_MODEL=$FIELD SEARCH_INFO=$SEARCH_INFO USER_MODEL=$USER_MODEL}
				</div>
			{if $COUNTER % 2 !==0 || $COUNTER===count($ADDITIONAL_FILTERS_FIELDS)-1}</div>{/if}
		{/foreach}
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/ChartFilterContents.tpl', $MODULE_NAME) WIDGET=$WIDGET}
	</div>
{/strip}
