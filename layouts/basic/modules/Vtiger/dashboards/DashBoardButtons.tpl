{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="dashboardHeading d-flex ml-auto mb-2 mt-sm-2 pr-sm-1 u-remove-dropdown-icon-down-lg u-w-xs-down-100">
		<input type="hidden" name="selectedModuleName" value="{$MODULE_NAME}">
		{assign var="SPECIAL_WIDGETS" value=Settings_WidgetsManagement_Module_Model::getSpecialWidgets('Home')}
		{if $MODULE_PERMISSION}
			<div class="js-predefined-widgets" data-js="container">
				{include file=\App\Layout::getTemplatePath('dashboards/DashBoardWidgetsList.tpl', $MODULE)}
			</div>
		{/if}
		{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardFilter')}
			<button class="btn btn-outline-secondary c-btn-block-xs-down addFilter ml-1"
					data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}" data-block-id="0" data-width="4"
					data-height="4">
				<span class="fas fa-filter mr-md-1"></span>
				<span class="d-none d-md-inline">{\App\Language::translate('LBL_ADD_FILTER')}</span>
			</button>
		{/if}
		{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardChartFilter')}
			<button class="btn btn-outline-secondary c-btn-block-xs-down addChartFilter ml-1"
					data-linkid="{$SPECIAL_WIDGETS['ChartFilter']->get('linkid')}" data-block-id="0" data-width="4"
					data-height="4">
				<span class="fas fa-chart-pie mr-md-1"></span>
				<span class="d-none d-md-inline">{\App\Language::translate('LBL_ADD_CHART_FILTER')}</span>
			</button>
		{/if}
	</div>
{/strip}
