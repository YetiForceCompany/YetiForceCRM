{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="dashboardWidgetHeader">	
	<div class="row">
		<div class="col-md-8">
			<div class="dashboardTitle" title="{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}"><strong>&nbsp;&nbsp;{\App\Language::translate($WIDGET->getTitle(),$MODULE_NAME)}</strong></div>
		</div>
		<div class="col-md-4">
			<div class="box float-right">
				{include file=\App\Layout::getTemplatePath('dashboards/DashboardHeaderIcons.tpl', $MODULE_NAME)}
			</div>
		</div>
	</div>
	<hr class="widgetHr" />
	<div class="row" >
		<div class="col-sm-10 form-inline">
			<div class="input-group input-group-sm">
				<span class=" input-group-addon"><span class="fas fa-calendar-alt iconMiddle "></span></span>
				<input type="text" name="time" title="{\App\Language::translate('LBL_CHOOSE_DATE')}" class="dateRangeField widgetFilter width90 form-control" value="{$DTIME}">
				<span class="input-group-addon checkbox-addon">
					<input name="compare" class="widgetFilter" type="checkbox" {if $COMPARE}checked{/if} title="{\App\Language::translate('LBL_COMPARE_TO_LAST_PERIOD', $MODULE_NAME)}">
					<a href="#" class="popoverTooltip" title="" data-placement="auto top" data-content="{\App\Language::translate('LBL_COMPARE_TO_LAST_PERIOD', $MODULE_NAME)}"><span class="fa fa-info-circle"></span></a>
				</span>
			</div>	
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file=\App\Layout::getTemplatePath('dashboards/DashBoardWidgetContents.tpl', $MODULE_NAME)}
</div>

