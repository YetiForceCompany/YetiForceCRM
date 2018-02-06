{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="dashboardWidgetHeader">
		<div class="row">
			<div class="col-md-8">
				<div class="dashboardTitle" title="{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}"><strong>&nbsp;&nbsp;{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}</strong></div>
			</div>
			<div class="col-md-4">
				<div class="box float-right">
					{include file=\App\Layout::getTemplatePath('dashboards/DashboardHeaderIcons.tpl', $MODULE_NAME)}
				</div>
			</div>
		</div>
		<hr class="widgetHr" />
		<div class="row" >
			<div class="col-sm-6">
				<div class="input-group input-group-sm">
					<span class=" input-group-prepend">
						<span class="input-group-text">
							<span class="fas fa-calendar-alt iconMiddle margintop3"></span>
						</span>
					</span>
					<input type="text" name="time" title="{\App\Language::translate('Created Time', $MODULE_NAME)}" class="dateRangeField form-control widgetFilter" value="{implode(',',$DTIME)}" aria-label="Small" aria-describedby="inputGroup-sizing-sm"/>
				</div>
			</div>
			<div class="col-sm-6">
				{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
			</div>
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/DashBoardWidgetContents.tpl', $MODULE_NAME)}
	</div>
{/strip}
