{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<script type="text/javascript">
	YetiForce_Bar_Widget_Js('YetiForce_SummationByUser_Widget_Js', {}, {
		getBasicOptions: function getBasicOptions() {
			let options = this._super();
			options.tooltip = {
				appendToBody: true,
				formatter: function(params, ticket, callback) {
					let name = params.data.fullName || '';
					let value = Number.isInteger(params.value) ? App.Fields.Integer.formatToDisplay(params.value) : App.Fields.Double.formatToDisplay(params.value);
					return params.marker + (name ? (name + ': ') : '') + "<strong>" + value + '</strong>';
				}
			}
			return options;
		}
	});
</script>
{strip}
	<div class="dashboardWidgetHeader">
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME)}
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderButtons.tpl', $MODULE_NAME)}
		</div>
		<hr class="widgetHr" />
		<div class="row no-gutters">
			<div class="col-ceq-xsm-6">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
							<span class="fas fa-calendar-alt"></span>
						</span>
					</div>
					<input type="text" name="time" title="{\App\Language::translate('LBL_CHOOSE_DATE')}" class="dateRangeField widgetFilter form-control text-center" value="{implode(',',$DTIME)}" />
				</div>
			</div>
			<div class="col-ceq-xsm-6">

			</div>
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/SummationByMonthsContents.tpl', $MODULE_NAME)}
	</div>
{/strip}
