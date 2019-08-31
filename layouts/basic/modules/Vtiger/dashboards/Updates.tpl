{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Dashboards-Updates -->
	<div class="tpl-Base-dashboards-Updates dashboardWidgetHeader">
		<input type="hidden" value="{$WIDGET->get('id')}" id="updatesWidgetId">
		<input type="hidden" value="{$WIDGET->get('data')}" id="widgetData">
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME)}
			<div class="flex-row">
			<div class="d-inline-flex">
			<a class="js-show-update-widget-config btn btn-sm btn-light" data-js="click"">
					<span class="fas fa-cog" title="{\App\Language::translate('LBL_UPDATES_WIDGET_CONFIGURATION')}"></span>
			</a>
			</div>
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderButtons.tpl', $MODULE_NAME)}
			</div>
		</div>
		<hr class="widgetHr" />
		<div class="row no-gutters justify-content-end">
			<div class="col-ceq-xsm-6">
				<div class="input-group input-group-sm">
					<div class=" input-group-prepend">
						<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
							<span class="fas fa-calendar-alt"
									title="{\App\Language::translate('Created Time', $MODULE_NAME)} &nbsp; {\App\Language::translate('LBL_BETWEEN', $MODULE_NAME)}"></span>
						</span>
					</div>
						<input type="text" name="dateRange" title="{\App\Language::translate('LBL_CHOOSE_DATE')}"
								class="dateRangeField widgetFilter form-control textAlignCenter text-center"
								value="{implode(',', $DATE_RANGE)}" aria-label="Small" aria-describedby="inputGroup-sizing-sm"/>
				</div>
			</div>
			<div class="col-ceq-xsm-6">
				{assign var=ACCESSIBLE_USERS value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
				{assign var=ACCESSIBLE_GROUPS value=\App\Fields\Owner::getInstance()->getAccessibleGroups()}
				{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
			</div>
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/UpdatesContents.tpl', $MODULE_NAME)}
	</div>
<!-- /tpl-Base-Dashboards-Updates -->
{/strip}
