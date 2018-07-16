{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="dashboardWidgetHeader">
	<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
		{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME)}
		<div class="d-inline-flex">
			{if \App\Privilege::isPermitted('Accounts', 'CreateView')}
				<a class="btn btn-sm btn-light" role="button" onclick="Vtiger_Header_Js.getInstance().quickCreateModule('Accounts'); return false;">
					<span class="fas fa-plus" title="{\App\Language::translate('LBL_ADD_RECORD')}"></span>
				</a>
			{/if}
			<a class="btn btn-sm btn-light" role="button" href="javascript:void(0);" name="drefresh" data-url="{$WIDGET->getUrl()}&linkid={$WIDGET->get('linkid')}&content=data">
				<span class="fas fa-sync-alt" title="{\App\Language::translate('LBL_REFRESH')}"></span>
			</a>
			{if !$WIDGET->isDefault()}
				<a class="btn btn-sm btn-light" role="button" name="dclose" class="widget" data-url="{$WIDGET->getDeleteUrl()}">
					<span class="fas fa-times" title="{\App\Language::translate('LBL_CLOSE')}"></span>
				</a>
			{/if}
		</div>
	</div>
	<hr class="widgetHr" />
	<div class="row">
		<div class="col-sm-6">
			{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file=\App\Layout::getTemplatePath('dashboards/NeglectedAccountsContents.tpl', $MODULE_NAME)}
</div>

