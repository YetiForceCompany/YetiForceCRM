{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<script type="text/javascript">
		YetiForce_Bar_Widget_Js('YetiForce_OpenTickets_Widget_Js', {}, {
			getBasicOptions: function getBasicOptions() {
				let options = this._super();
				options.tooltip = {
					appendToBody: true,
					formatter: function(params, ticket, callback) {
						let name = params.value[2].fullName || '';
						let value = Number.isInteger(params.value[1]) ? App.Fields.Integer.formatToDisplay(params.value[1]) : App.Fields.Double.formatToDisplay(params.value[1]);
						return params.marker + (name ? (name + ': ') : '') + "<strong>" + value + '</strong>';
					}
				}
				return options;
			}
		});
	</script>
	<div class="dashboardWidgetHeader">
		{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeader.tpl', $MODULE_NAME)}
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/DashBoardWidgetContents.tpl', $MODULE_NAME)}
	</div>
{/strip}
