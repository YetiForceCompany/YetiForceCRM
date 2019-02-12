{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="dashboardWidgetHeader">
	<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
		{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME)}
		{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderButtons.tpl', $MODULE_NAME)}
	</div>
	<hr class="widgetHr" />
	<div class="row no-gutters" >
		<div class="col-sm-10 form-inline">
			<div class="input-group input-group-sm">
				<div class=" input-group-prepend">
					<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
						<span class="fas fa-calendar-alt"></span>
					</span>
				</div>
				<input type="text" name="time" title="{\App\Language::translate('LBL_CHOOSE_DATE')}" class="dateRangeField widgetFilter form-control text-center" value="{$DTIME}">
				<span class="input-group-addon checkbox-addon">
					<input name="compare" class="widgetFilter" type="checkbox" {if $COMPARE}checked{/if} title="{\App\Language::translate('LBL_COMPARE_TO_LAST_PERIOD', $MODULE_NAME)}">
					<a href="#" class="js-popover-tooltip" data-js="popover" title="" data-placement="top" data-content="{\App\Language::translate('LBL_COMPARE_TO_LAST_PERIOD', $MODULE_NAME)}"><span class="fas fa-info-circle"></span></a>
				</span>
			</div>
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file=\App\Layout::getTemplatePath('dashboards/DashBoardWidgetContents.tpl', $MODULE_NAME)}
</div>

