{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="dashboardWidgetHeader">
	<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
		{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME)}
		<div class="d-inline-flex">
			{if \App\Privilege::isPermitted($MODULE_NAME, 'CreateView')}
				<button class="btn btn-sm btn-ligh js-widget-quick-create" data-js="click" type="button"
					data-module-name="{$MODULE_NAME}">
					<span class="fas fa-plus" title="{\App\Language::translate('LBL_ADD_RECORD')}"></span>
				</button>
			{/if}
			<button class="btn btn-sm btn-light js-widget-refresh" title="{\App\Language::translate('LBL_REFRESH')}" data-url="{$WIDGET->getUrl()}&content=data" data-js="click">
				<span class="fas fa-sync-alt"></span>
			</button>
			{if !$WIDGET->isDefault()}
				<button class="btn btn-sm btn-light js-widget-remove" title="{\App\Language::translate('LBL_CLOSE')}" data-url="{$WIDGET->getDeleteUrl()}" data-js="click">
					<span class="fas fa-times"></span>
				</button>
			{/if}
		</div>
	</div>
	<hr class="widgetHr" />
	<div class="row no-gutters">
		<div class="col-ceq-xsm-6">
			{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file=\App\Layout::getTemplatePath('dashboards/NeglectedAccountsContents.tpl', $MODULE_NAME)}
</div>
