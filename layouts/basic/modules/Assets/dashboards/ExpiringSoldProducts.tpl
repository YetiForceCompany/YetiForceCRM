{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="dashboardWidgetHeader">
	<div class="row">
		<div class="col-md-8">
			<div class="dashboardTitle" title="{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}"><b>&nbsp;&nbsp;{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}</b></div>
		</div>
		<div class="col-md-4">
			<div class="box float-right">
				{if \App\Privilege::isPermitted($MODULE_NAME, 'CreateView')}
					<a class="btn btn-light btn-sm" onclick="Vtiger_Header_Js.getInstance().quickCreateModule('{$MODULE_NAME}'); return false;">
						<i class='fas fa-plus' border='0' title="{\App\Language::translate('LBL_ADD_RECORD')}" alt="{\App\Language::translate('LBL_ADD_RECORD')}"></i>
					</a>
				{/if}
				<a class="btn btn-light btn-sm" href="javascript:void(0);" name="drefresh" data-url="{$WIDGET->getUrl()}&linkid={$WIDGET->get('linkid')}&content=data">
					<i class="fas fa-sync-alt" hspace="2" border="0" align="absmiddle" title="{\App\Language::translate('LBL_REFRESH')}" alt="{\App\Language::translate('LBL_REFRESH')}"></i>
				</a>
				{if !$WIDGET->isDefault()}
					<a class="btn btn-light btn-sm" name="dclose" class="widget" data-url="{$WIDGET->getDeleteUrl()}">
						<i class="fas fa-times" hspace="2" border="0" align="absmiddle" title="{\App\Language::translate('LBL_CLOSE')}" alt="{\App\Language::translate('LBL_CLOSE')}"></i>
					</a>
				{/if}
			</div>
		</div>
	</div>
	<hr class="widgetHr" />
	<div class="row" >
		<div class="col-md-12">
			<div class="float-right">
				<input class="switchBtn calculationsSwitch" type="checkbox" checked="" data-size="mini" data-label-width="5" data-handle-width="75" data-on-text="{\App\Language::translate('LBL_OWNER',$MODULE_NAME)}" data-off-text="{\App\Language::translate('LBL_COMMON',$MODULE_NAME)}" data-on-val="owner" data-off-val="common" data-urlparams="showtype">
			</div>
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file=\App\Layout::getTemplatePath('dashboards/ExpiringSoldProductsContents.tpl', $MODULE_NAME)}
</div>
