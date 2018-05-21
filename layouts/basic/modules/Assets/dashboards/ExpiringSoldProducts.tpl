{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="dashboardWidgetHeader">
	<div class="row">
		<div class="col-md-8">
			<h5 class="dashboardTitle h6"
				 title="{App\Purifier::encodeHtml(App\Language::translate($WIDGET->getTitle(), $MODULE_NAME))}"><b>&nbsp;&nbsp;{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}</b>
			</h5>
		</div>
		<div class="col-md-4">
			<div class="box float-right">
				{if \App\Privilege::isPermitted($MODULE_NAME, 'CreateView')}
					<a class="btn btn-light btn-sm" role="button" onclick="Vtiger_Header_Js.getInstance().quickCreateModule('{$MODULE_NAME}'); return false;" alt="{\App\Language::translate('LBL_ADD_RECORD')}">
						<span class="fas fa-plus" border="0" title="{\App\Language::translate('LBL_ADD_RECORD')}"></span>
						<span class="sr-only">{\App\Language::translate('LBL_ADD_RECORD')}</span>
					</a>
				{/if}
				<a class="btn btn-light btn-sm" role="button" href="javascript:void(0);" name="drefresh" data-url="{$WIDGET->getUrl()}&linkid={$WIDGET->get('linkid')}&content=data" alt="{\App\Language::translate('LBL_REFRESH')}">
					<span class="fas fa-sync-alt" hspace="2" border="0" align="absmiddle" title="{\App\Language::translate('LBL_REFRESH')}"></span>
					<span class="sr-only">{\App\Language::translate('LBL_REFRESH')}</span>
				</a>
				{if !$WIDGET->isDefault()}
					<a class="btn btn-light btn-sm" role="button" name="dclose" class="widget" data-url="{$WIDGET->getDeleteUrl()}" alt="{\App\Language::translate('LBL_CLOSE')}">
						<span class="fas fa-times" hspace="2" border="0" align="absmiddle" title="{\App\Language::translate('LBL_CLOSE')}"></span>
						<span class="sr-only">{\App\Language::translate('LBL_CLOSE')}</span>
					</a>
				{/if}
			</div>
		</div>
	</div>
	<hr class="widgetHr"/>
	<div class="row">
		<div class="col-md-12">
			<div class="float-right">
				<div class="btn-group btn-group-toggle" data-toggle="buttons">
					<label class="btn btn-sm btn-outline-primary active">
						<input class="js-switch--calculations" type="radio" name="options" id="option1"
							   data-js="change"
							   data-url-value="owner"
							   data-urlparams="showtype"
							   autocomplete="off"
							   checked
						> {\App\Language::translate('LBL_OWNER',$MODULE_NAME)}
					</label>
					<label class="btn btn-sm btn-outline-primary">
						<input class="js-switch--calculations" type="radio" name="options" id="option2"
							   data-js="change"
							   data-url-value="common"
							   data-urlparams="showtype"
							   autocomplete="off"
						> {\App\Language::translate('LBL_COMMON',$MODULE_NAME)}
					</label>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file=\App\Layout::getTemplatePath('dashboards/ExpiringSoldProductsContents.tpl', $MODULE_NAME)}
</div>
