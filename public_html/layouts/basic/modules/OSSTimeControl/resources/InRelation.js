/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery(document).ready(function ($) {
	if (window.loadInRelationTomeControl == undefined) {
		$.Class(
			'OSSTimeControl_Calendar_Js',
			{},
			{
				registerSwitch: function () {
					$('.switchChartContainer').on('click', function () {
						var chartContainer = $('.chartContainer')[0];
						if ($(chartContainer).is(':visible')) {
							$(this).find('.fas').removeClass('fa-chevron-up').addClass('fa-chevron-down');
							$('.chartContainer').hide();
						} else {
							$(this).find('.fas').removeClass('fa-chevron-down').addClass('fa-chevron-up');
							$('.chartContainer').show();
						}
					});
				},
				registerEvents: function () {
					let chart = $('.sumaryRelatedTimeControl');
					if (chart.length && typeof window['YetiForce_Chart_Widget_Js'] !== 'undefined') {
						let widgetInstance = YetiForce_Chart_Widget_Js.getInstance(chart, 'Bar');
						widgetInstance.init(chart);
						let options = {
							yAxis: {
								axisLabel: {
									formatter: (value) =>
										typeof value === 'number'
											? App.Fields.Double.formatToDisplay(value) + ' ' + app.vtranslate('JS_H')
											: value
								}
							},
							tooltip: {
								formatter: function (params, _, __) {
									return params.marker + params.data.fullName + ': <strong>' + params.data.fullValue + '</strong>';
								}
							}
						};
						widgetInstance.customOption = options;
						widgetInstance.postLoadWidget();
					}
					this.registerSwitch();
				}
			}
		);
	}
	var instance = new OSSTimeControl_Calendar_Js();
	instance.registerEvents();
	window.loadInRelationTomeControl = true;
});
