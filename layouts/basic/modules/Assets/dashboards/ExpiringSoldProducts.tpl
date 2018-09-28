{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="dashboardWidgetHeader">
	<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
		{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME)}
		<div class="d-inline-flex">
			{if \App\Privilege::isPermitted($MODULE_NAME, 'CreateView')}
				<button class="btn btn-light btn-sm js-widget-quick-create" data-js="click" type="button"
						data-module-name="{$MODULE_NAME}">
					<span class="fas fa-plus" title="{\App\Language::translate('LBL_ADD_RECORD')}"></span>
				</button>
			{/if}
			<a class="btn btn-light btn-sm" role="button" href="javascript:void(0);" name="drefresh"
			   data-url="{$WIDGET->getUrl()}&linkid={$WIDGET->get('linkid')}&content=data">
				<span class="fas fa-sync-alt" title="{\App\Language::translate('LBL_REFRESH')}"></span>
			</a>
			{if !$WIDGET->isDefault()}
				<a class="btn btn-light btn-sm" role="button" class="js-widget-remove" data-js="click | bootbox"
				   data-url="{$WIDGET->getDeleteUrl()}">
					<span class="fas fa-times" title="{\App\Language::translate('LBL_CLOSE')}"></span>
				</a>
			{/if}
		</div>
	</div>
	<hr class="widgetHr"/>
	<div class="row no-gutters">
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
