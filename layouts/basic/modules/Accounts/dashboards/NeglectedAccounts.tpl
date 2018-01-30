{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="dashboardWidgetHeader">
	<div class="row">
		<div class="col-md-8">
			<div class="dashboardTitle" title="{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}"><strong>&nbsp;&nbsp;{\App\Language::translate($WIDGET->getTitle(),$MODULE_NAME)}</strong></div>
		</div>
		<div class="col-md-4">
			<div class="box float-right">
				{if \App\Privilege::isPermitted('Accounts', 'CreateView')}
					<a class="btn btn-xs btn-light" onclick="Vtiger_Header_Js.getInstance().quickCreateModule('Accounts'); return false;">
						<span class='fas fa-plus' border='0' title="{\App\Language::translate('LBL_ADD_RECORD')}" alt="{\App\Language::translate('LBL_ADD_RECORD')}"></span>
					</a>
				{/if}
				<a class="btn btn-xs btn-light" href="javascript:void(0);" name="drefresh" data-url="{$WIDGET->getUrl()}&linkid={$WIDGET->get('linkid')}&content=data">
					<span class="glyphicon glyphicon-refresh" hspace="2" border="0" align="absmiddle" title="{\App\Language::translate('LBL_REFRESH')}" alt="{\App\Language::translate('LBL_REFRESH')}"></span>
				</a>
				{if !$WIDGET->isDefault()}
					<a class="btn btn-xs btn-light" name="dclose" class="widget" data-url="{$WIDGET->getDeleteUrl()}">
						<span class="fas fa-times" hspace="2" border="0" align="absmiddle" title="{\App\Language::translate('LBL_CLOSE')}" alt="{\App\Language::translate('LBL_CLOSE')}"></span>
					</a>
				{/if}
			</div>
		</div>
	</div>
	<hr class="widgetHr" />
	<div class="row">
		<div class="col-sm-6">
			{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
		</div>
	</div>
</div>
<div class="dashboardWidgetContent noSpaces">
	{include file=\App\Layout::getTemplatePath('dashboards/NeglectedAccountsContents.tpl', $MODULE_NAME)}
</div>

