{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="dashboardWidgetHeader">
	<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
		{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME) CLASSNAME="col-md-10"}
		<div class="d-inline-flex">
			{if \App\Privilege::isPermitted($MODULE_NAME, 'CreateView')}
				<button class="btn btn-sm btn-light js-widget-quick-create" data-js="click" type="button"
						data-module-name="{$MODULE_NAME}">
					<span class="fas fa-plus" title="{\App\Language::translate('LBL_ADD_RECORD')}"></span>
				</button>
			{/if}
			<a class="btn btn-sm btn-light" role="button" href="javascript:void(0);" name="drefresh"
			   data-url="{$WIDGET->getUrl()}&linkid={$WIDGET->get('linkid')}&content=data">
				<span class="fas fa-sync-alt" title="{\App\Language::translate('LBL_REFRESH')}"></span>
			</a>
			{if !$WIDGET->isDefault()}
				<a class="btn btn-sm btn-light" role="button" class="js-widget-remove" data-js="click | bootbox"
				   data-url="{$WIDGET->getDeleteUrl()}">
					<span class="fas fa-times" title="{\App\Language::translate('LBL_CLOSE')}"></span>
				</a>
			{/if}
		</div>
	</div>
	<hr class="widgetHr"/>
	<div class="row no-gutters">
		<div class="col-ceq-xsm-6">
			<div class="input-group input-group-sm">
				<div class=" input-group-prepend">
					<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
						<span class="fas fa-calendar-alt"></span>
					</span>
				</div>
				<input type="text" name="time" title="{\App\Language::translate('LBL_CHOOSE_DATE')}"
					   class="dateRangeField widgetFilter form-control text-center" value="{implode(',',$DTIME)}"/>
			</div>
		</div>
		<div class="col-ceq-xsm-6">
			{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
		</div>
	</div>
</div>

<div class="dashboardWidgetContent allTimeControl paddingBottom10">
	{include file=\App\Layout::getTemplatePath('dashboards/TimeControlContents.tpl', $MODULE_NAME)}
</div>

