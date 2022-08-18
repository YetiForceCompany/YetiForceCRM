{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-OSSTimeControl-dashboards-TimeCounter -->
	<div class="dashboardWidgetHeader">
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME) TITLE=$WIDGET->getTitle()}
			{if !$WIDGET->isDefault()}
				<div class="d-inline-flex">
					<button class="btn btn-sm btn-light js-widget-remove" title="{\App\Language::translate('LBL_CLOSE')}" data-url="{$WIDGET->getDeleteUrl()}" data-js="click">
						<span class="fas fa-times"></span>
					</button>
				</div>
			{/if}
		</div>
	</div>
	<div class="dashboardWidgetContent d-flex justify-content-center align-items-center">
		{include file=\App\Layout::getTemplatePath('dashboards/TimeCounterContents.tpl', $MODULE_NAME)}
	</div>
	<!-- /tpl-OSSTimeControl-dashboards-TimeCounter -->
{/strip}
