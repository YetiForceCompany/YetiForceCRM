{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
<input type="hidden" name="typeChart" value="{$CHART_TYPE}">
<input class="widgetData" name="data" type="hidden" value="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATA_CHART))}" />
{if count($CHART_TYPE) gt 0 }
	<div class="widgetChartContainer chartcontent"></div>
{else}
	<span class="noDataMsg">
		{vtranslate('LBL_NO_RECORDS_MATCHED_THIS_CRITERIA')}
	</span>
{/if}
<script>
		Vtiger_Widget_Js('YetiForce_Chartfilter_Widget_Js', {}, {
			loadChart: function () {
				var container = this.getContainer();
				var chartType = container.find('[name="typeChart"]').val();
				var chartClassName = chartType.toCamelCase();
				var chartClass = window["Vtiger_" + chartClassName + "_Widget_Js"];

				var instance = false;
				if (typeof chartClass != 'undefined') {
					instance = new chartClass(container);
					instance.loadChart();
				}
			}
		});
</script>
