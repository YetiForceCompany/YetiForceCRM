{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="dashboardWidgetHeader">
		<div class="row">
			<div class="col-md-7">
				<div class="dashboardTitle" title="{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}"><strong>&nbsp;&nbsp;{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}</strong></div>
			</div>
			<div class="col-md-5">
				<div class="box float-right">
					<button class="btn btn-sm btn-light downloadWidget hidden" data-widgetid="{$CHART_MODEL->get('widgetid')}" title="{\App\Language::translate('LBL_WIDGET_DOWNLOAD','Home')}">
						<span class="far fa-arrow-alt-circle-down"></span>
					</button>&nbsp;
					<button class="btn btn-sm btn-light printWidget hidden" data-widgetid="{$CHART_MODEL->get('widgetid')}" title="{\App\Language::translate('LBL_WIDGET_PRINT','Home')}">
						<span class="fas fa-print"></span>
					</button>&nbsp;
					<button class="btn btn-sm btn-light recordCount" data-url="{\App\Purifier::encodeHtml($CHART_MODEL->getTotalCountURL())}" title="{\App\Language::translate('LBL_WIDGET_FILTER_TOTAL_COUNT_INFO')}">
						<span class="fas fa-signal"></span>
						<a class="float-left d-none" href="{\App\Purifier::encodeHtml($CHART_MODEL->getListViewURL())}">
							<span class="count badge float-left"></span>
						</a>
					</button>
					{include file=\App\Layout::getTemplatePath('dashboards/DashboardHeaderIcons.tpl', $MODULE_NAME)}
				</div>
			</div>
		</div>
		{assign var="WIDGET_DATA" value=$WIDGET->getArray('data')}
		{if $WIDGET_DATA['timeRange'] || $WIDGET_DATA['showOwnerFilter']}
			<hr class="widgetHr" />
		{/if}
		<div class="row">
			{if $WIDGET_DATA['timeRange']}
				<div class="col-md-6">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="input-group-text u-cursor-pointer js-clock__btn" data-js="click">
								<span class="far fa-clock"></span>
							</span>
						</div>
						<input type="text" name="time" title="{\App\Language::translate('LBL_CHOOSE_DATE')}" placeholder="{\App\Language::translate('LBL_CHOOSE_DATE')}" class="dateRangeField widgetFilter form-control text-center" aria-label="Small" aria-describedby="inputGroup-sizing-sm"/>
					</div>
				</div>
			{/if}
			{if $WIDGET_DATA['showOwnerFilter']}
				<div class="col-md-6 ownersFilter">
					<div class="input-group input-group-sm flex-nowrap">
						<div class=" input-group-prepend">
							<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
								<span class="fas fa-calendar-alt" title="{\App\Language::translate('Assigned To')}"></span>
							</span>
						</div>	
						<div class="select2Wrapper">
							<select class="widgetFilter select2 owner form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm" name="owner" title="{\App\Language::translate('LBL_OWNER')}">
								<option value="0">{\App\Language::translate('LBL_ALL_OWNERS','Home')}</option>
							</select>
						</div>
					</div>
				</div>
			{/if}
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/ChartFilterContents.tpl', $MODULE_NAME) WIDGET=$WIDGET}
	</div>
{/strip}
