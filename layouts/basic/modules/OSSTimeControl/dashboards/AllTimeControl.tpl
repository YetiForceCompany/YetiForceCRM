{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<script type="text/javascript">
		YetiForce_Bar_Widget_Js('YetiForce_AllTimeControl_Widget_Js', {}, {
			getBasicOptions: function getBasicOptions() {
				let options = this._super();
				options.yAxis.axisLabel.formatter = (value) => (typeof value === 'number' ? App.Fields.Double.formatToDisplay(value) + ' ' + app.vtranslate('JS_H') : value);
				options.tooltip = {
					appendToBody: true,
					formatter: function(params, ticket, callback) {
						let response = '';
						let header;
						for (let data of params) {
							if (!header && data.data.fullLabel) {
								header = '<strong>' + data.data.fullLabel + '</strong><br>';
							}
							if (data.data.fullValue === undefined) {
								continue;
							}
							if (response) {
								response += '<br>';
							}
							response += data.marker + data.data.seriesLabel + ': <strong>' + data.data.fullValue + '</strong>';
						}
						return header + response;
					}
				}

				return options;
			}
		});
	</script>
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
				<div class="input-group input-group-sm">
					<div class=" input-group-prepend">
						<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
							<span class="fas fa-calendar-alt"></span>
						</span>
					</div>
					<input type="text" name="time" title="{\App\Language::translate('LBL_CHOOSE_DATE')}"
						class="dateRangeField widgetFilter form-control text-center" value="{implode(',',$DTIME)}" />
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
{/strip}
