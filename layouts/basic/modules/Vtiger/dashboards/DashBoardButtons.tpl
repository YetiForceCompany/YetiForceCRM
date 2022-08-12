{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-dashboards-DashBoardButtons -->
	<div class="dashboardHeading d-flex ml-auto mb-2 mt-sm-2 pr-sm-1 u-remove-dropdown-icon-down-lg u-w-xs-down-100">
		<input type="hidden" name="selectedModuleName" value="{$MODULE_NAME}">
		{assign var="SPECIAL_WIDGETS" value=Settings_WidgetsManagement_Module_Model::getSpecialWidgets($MODULE_NAME)}
		{if $MODULE_PERMISSION}
			<div class="js-predefined-widgets" data-js="container">
				{include file=\App\Layout::getTemplatePath('dashboards/DashBoardWidgetsList.tpl', $MODULE)}
			</div>
		{/if}
		{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardFilter')}
			<button class="btn btn-outline-dark js-add-filter ml-1 js-popover-tooltip" title="{\App\Purifier::encodeHtml(\App\Language::translate('LBL_ADD_FILTER','Dashboard'))}" data-content="{\App\Purifier::encodeHtml(\App\Language::translate('LBL_ADD_FILTER_DESC','Dashboard'))}"
				data-linkid="{$SPECIAL_WIDGETS['Mini List']->get('linkid')}" data-block-id="0" data-width="4" data-height="4" data-js="click|popover">
				<span class="fas fa-filter"></span>
				<span class="d-none d-xxl-inline ml-2">{\App\Language::translate('LBL_ADD_FILTER','Dashboard')}</span>
			</button>
		{/if}
		{if $USER_PRIVILEGES_MODEL->hasModuleActionPermission($MODULE_MODEL->getId(),'CreateDashboardChartFilter')}
			<button class="btn btn-outline-dark js-show-modal ml-1 js-popover-tooltip" title="{\App\Language::translate('LBL_ADD_CHART_FILTER','Dashboard')}" data-content="{\App\Purifier::encodeHtml(\App\Language::translate('LBL_ADD_CHART_FILTER_DESC','Dashboard'))}"
				data-url="index.php?module={$MODULE_MODEL->getName()}&view=ChartFilter&step=step1&linkId={$SPECIAL_WIDGETS['ChartFilter']->get('linkid')}"
				data-linkid="{$SPECIAL_WIDGETS['ChartFilter']->get('linkid')}"
				data-width="4" data-height="4" data-block-id="0" data-module="{$MODULE_MODEL->getName()}"
				data-modalId="{\App\Layout::getUniqueId('ChartFilter')}" data-content="5555" data-js="click|popover">
				<span class="fas fa-chart-pie"></span>
				<span class="d-none d-xxl-inline ml-2">{\App\Language::translate('LBL_ADD_CHART_FILTER','Dashboard')}</span>
			</button>
		{/if}
		<a class="btn btn-outline-dark ml-1 js-popover-tooltip js-post-action" href="index.php?module={$MODULE_MODEL->getName()}&action=Widget&mode=clear" title="{\App\Purifier::encodeHtml(\App\Language::translate('LBL_CLEAR_DEVICE_CONF','Dashboard'))}" data-content="{\App\Purifier::encodeHtml(\App\Language::translate('LBL_CLEAR_DEVICE_CONF_DESC','Dashboard'))}" data-js="popover">
			<span class="fas fa-broom fa-fw"></span>
		</a>
	</div>
	<!-- /tpl-dashboards-DashBoardButtons -->
{/strip}
