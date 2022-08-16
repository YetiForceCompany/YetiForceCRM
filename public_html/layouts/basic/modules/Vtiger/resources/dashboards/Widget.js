/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 *************************************************************************************/
'use strict';

jQuery.Class(
	'Vtiger_Widget_Js',
	{
		widgetPostLoadEvent: 'Vtiger.Dashboard.PostLoad',
		widgetPostRefereshEvent: 'Vtiger.Dashboard.PostRefresh',
		getInstance: function getInstance(container, widgetClassName, moduleName) {
			if (typeof moduleName === 'undefined') {
				moduleName = app.getModuleName();
			}
			const moduleClass = window[moduleName + '_' + widgetClassName + '_Widget_Js'];
			const fallbackClass = window['Vtiger_' + widgetClassName + '_Widget_Js'];
			const yetiClass = window['YetiForce_' + widgetClassName + '_Widget_Js'];
			const basicClass = YetiForce_Widget_Js;
			let instance;
			if (typeof moduleClass !== 'undefined') {
				instance = new moduleClass(container, false, widgetClassName);
			} else if (typeof fallbackClass !== 'undefined') {
				instance = new fallbackClass(container, false, widgetClassName);
			} else if (typeof yetiClass !== 'undefined') {
				instance = new yetiClass(container, false, widgetClassName);
			} else {
				instance = new basicClass(container, false, widgetClassName);
			}
			return instance;
		}
	},
	{
		container: false,
		plotContainer: false,
		chartInstance: false,
		chartData: [],
		paramCache: false,
		init: function init(container, reload, widgetClassName) {
			container = $(container);
			this.setContainer(container);
			this.registerWidgetPostLoadEvent(container);
			if (!reload) {
				this.registerWidgetPostRefreshEvent(container);
			}
			this.registerCache(container);
		},
		areColorsFromDividingField() {
			return !!Number(this.getContainer().find('[name="colorsFromDividingField"]').val());
		},
		getSourceChartType() {
			return this.getContainer().find('[name="typeChart"]').val();
		},
		isMultiFilter() {
			if (typeof this.filterIds !== 'undefined') {
				return this.filterIds.length > 1;
			}
			return false;
		},
		/**
		 * Get widget data
		 * @returns {*}
		 */
		getWidgetData() {
			if (typeof this.widgetData !== 'undefined') {
				return this.widgetData;
			}
			let widgetDataEl = this.getContainer().find('.widgetData');
			if (widgetDataEl.length) {
				return (this.widgetData = JSON.parse(widgetDataEl.val()));
			}
			return false;
		},
		/**
		 * Predefined functions that will replace options function type
		 * @type {Object}
		 */
		globalChartFunctions: {
			/**
			 * Functions for x or y axes scales xAxes:[{here}]
			 */
			scales: {
				formatAxesLabels: function formatAxesLabels(value, index, values) {
					if (String(value).length > 0 && !isNaN(Number(value))) {
						return App.Fields.Double.formatToDisplay(value);
					}
					return value;
				}
			},
			/**
			 * Functions for datalabels
			 */
			datalabels: {
				display(context) {
					const meta = context.chart.getDatasetMeta(context.datasetIndex);
					return meta.hidden !== true;
				},
				formatter: function datalabelsFormatter(value, context) {
					if (
						typeof this.widgetData !== 'undefined' &&
						typeof this.widgetData.valueType !== 'undefined' &&
						this.widgetData.valueType === 'count'
					) {
						return App.Fields.Double.formatToDisplay(value, 0);
					}
					if (
						typeof context.chart.data.datasets[context.datasetIndex].dataFormatted !== 'undefined' &&
						typeof context.chart.data.datasets[context.datasetIndex].dataFormatted[context.dataIndex] !== 'undefined'
					) {
						// data presented in different format usually exists in alternative dataFormatted array
						return context.chart.data.datasets[context.datasetIndex].dataFormatted[context.dataIndex];
					}
					if (String(value).length > 0 && isNaN(Number(value))) {
						return App.Fields.Double.formatToDisplay(value);
					}
					return value;
				}
			},
			/**
			 * Tooltips functions
			 */
			tooltips: {
				label: function tooltipLabelCallback(tooltipItem, data) {
					// get already formatted data if exists
					if (
						typeof data.datasets[tooltipItem.datasetIndex].dataFormatted !== 'undefined' &&
						data.datasets[tooltipItem.datasetIndex].dataFormatted[tooltipItem.index] !== 'undefined'
					) {
						return data.datasets[tooltipItem.datasetIndex].dataFormatted[tooltipItem.index];
					}
					// if there is no formatted data so try to format it
					if (
						String(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]).length > 0 &&
						!isNaN(Number(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]))
					) {
						if (
							typeof this.widgetData !== 'undefined' &&
							typeof this.widgetData.valueType !== 'undefined' &&
							this.widgetData.valueType === 'count'
						) {
							return App.Fields.Double.formatToDisplay(
								data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index],
								0
							);
						}
						return App.Fields.Double.formatToDisplay(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]);
					}
					// return raw data at idex
					return data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
				},
				title: function tooltipTitleCallback(tooltipItems, data) {
					const tooltipItem = tooltipItems[0];
					// get already formatted title if exists
					if (
						typeof data.datasets[tooltipItem.datasetIndex].titlesFormatted !== 'undefined' &&
						data.datasets[tooltipItem.datasetIndex].titlesFormatted[tooltipItem.index] !== 'undefined'
					) {
						return data.datasets[tooltipItem.datasetIndex].titlesFormatted[tooltipItem.index];
					}
					// if there is no formatted title so try to format it
					if (String(data.labels[tooltipItem.index]).length > 0 && !isNaN(Number(data.labels[tooltipItem.index]))) {
						if (
							typeof this.widgetData !== 'undefined' &&
							typeof this.widgetData.valueType !== 'undefined' &&
							this.widgetData.valueType === 'count'
						) {
							return App.Fields.Double.formatToDisplay(data.labels[tooltipItem.index], 0);
						}
						return App.Fields.Double.formatToDisplay(data.labels[tooltipItem.index]);
					}
					// return label at index
					return data.labels[tooltipItem.index];
				}
			},

			legend: {
				onClick(e, legendItem) {
					let type = this.chartInstance.config.type;
					if (typeof Chart.defaults[type] !== 'undefined') {
						return Chart.defaults[type].legend.onClick.apply(this.chartInstance, [e, legendItem]);
					}
					return Chart.defaults.global.legend.onClick.apply(this.chartInstance, [e, legendItem]);
				},
				generateLabels(chart) {
					let type = chart.config.type;
					let labels;
					if (typeof Chart.defaults[type] !== 'undefined') {
						labels = Chart.defaults[type].legend.labels.generateLabels(chart);
					} else {
						labels = Chart.defaults.global.legend.labels.generateLabels(chart);
					}
					if (this.areColorsFromDividingField() || this.isMultiFilter()) {
						chart.config.options.legend.labels.boxWidth = 12;
						labels.forEach((label, index) => {
							label.fillStyle = 'rgba(0,0,0,0)';
							label.strokeStyle = 'rgba(0,0,0,0.15)';
						});
					}
					return labels;
				},
				display() {
					if (this.isMultiFilter() || this.areColorsFromDividingField()) {
						return true;
					}
					return false;
				}
			},

			/**
			 * plugins
			 */
			plugins: {
				/**
				 * If datalabels doesn't fit - hide them individually
				 * @param  {Chart} chart chart instance
				 * @return {undefined}
				 */
				hideVerticalBarDatalabelsIfNeeded: function (chart) {
					let getDatasetsMeta = function (chart) {
						const datasets = [];
						const data = chart.data;
						if (typeof data !== 'undefined' && typeof data.datasets !== 'undefined' && Array.isArray(data.datasets)) {
							for (let i = 0, len = data.datasets.length; i < len; i++) {
								const meta = chart.getDatasetMeta(i);
								if (typeof meta.data !== 'undefined' && Array.isArray(meta.data)) {
									datasets.push(meta);
								}
							}
						}
						return datasets;
					};
					let datasetsMeta = getDatasetsMeta(chart);
					let datasets = chart.data.datasets;
					for (let i = 0, len = datasets.length; i < len; i++) {
						const dataset = datasets[i];
						const meta = datasetsMeta[i];
						if (meta.hidden) {
							continue;
						}
						const metaData = meta.data;
						if (typeof dataset._models === 'undefined') {
							dataset._models = {};
						}
						if (typeof dataset.datalabels === 'undefined') {
							dataset.datalabels = {};
						}
						if (typeof dataset.datalabels.display === 'undefined') {
							dataset.datalabels.display = true;
						}
						for (let iItem = 0, lenItem = metaData.length; iItem < lenItem; iItem++) {
							const dataItem = metaData[iItem];
							if (typeof dataItem.$datalabels !== 'undefined' && typeof dataItem.$datalabels._model !== 'undefined') {
								let model = dataItem.$datalabels._model;
								if (model !== null && typeof model !== 'undefined') {
									dataset._models[iItem] = model;
								} else if (dataset._models[iItem] !== null && typeof dataset._models[iItem] !== 'undefined') {
									model = dataset._models[iItem];
								} else {
									return false;
								}
								const labelWidth = model.size.width + model.padding.width + model.borderWidth * 2;
								const labelHeight = model.size.height + model.padding.height + model.borderWidth * 2;
								const barHeight = dataItem.height();
								let threshold = 10;
								if (typeof chart.config.options.verticalBarLabelsThreshold !== 'undefined') {
									threshold = chart.config.options.verticalBarLabelsThreshold;
								}
								if (dataItem._view.width + threshold < labelWidth || barHeight + threshold < labelHeight) {
									dataItem.$datalabels._model.positioner = () => {
										return false;
									};
								} else {
									dataItem.$datalabels._model = model;
								}
							}
						}
					}
				},
				/**
				 * If datalabels doesn't fit - hide them individually
				 * @param  {Chart} chart  Chart instance
				 * @return {undefined}
				 */
				hideHorizontalBarDatalabelsIfNeeded: function hideHorizontalBarDatalabelsIfNeeded(chart) {
					let getDatasetsMeta = function (chart) {
						const datasets = [];
						const data = chart.data;
						if (typeof data !== 'undefined' && typeof data.datasets !== 'undefined' && Array.isArray(data.datasets)) {
							for (let i = 0, len = data.datasets.length; i < len; i++) {
								const meta = chart.getDatasetMeta(i);
								if (typeof meta.data !== 'undefined' && Array.isArray(meta.data)) {
									datasets.push(meta);
								}
							}
						}
						return datasets;
					};
					let datasetsMeta = getDatasetsMeta(chart);
					let datasets = chart.data.datasets;
					for (let i = 0, len = datasets.length; i < len; i++) {
						const dataset = datasets[i];
						const meta = datasetsMeta[i];
						if (meta.hidden) {
							continue;
						}
						const metaData = meta.data;
						if (typeof dataset._models === 'undefined') {
							dataset._models = {};
						}
						if (typeof dataset.datalabels === 'undefined') {
							dataset.datalabels = {};
						}
						if (typeof dataset.datalabels.display === 'undefined') {
							dataset.datalabels.display = true;
						}
						for (let iItem = 0, lenItem = metaData.length; iItem < lenItem; iItem++) {
							const dataItem = metaData[iItem];
							if (typeof dataItem.$datalabels !== 'undefined' && typeof dataItem.$datalabels._model !== 'undefined') {
								let model = dataItem.$datalabels._model;
								if (model !== null && typeof model !== 'undefined') {
									dataset._models[iItem] = model;
								} else if (dataset._models[iItem] !== null && typeof dataset._models[iItem] !== 'undefined') {
									model = dataset._models[iItem];
								} else {
									return false;
								}
								const labelWidth = model.size.width + model.padding.width + model.borderWidth * 2;
								const labelHeight = model.size.height + model.padding.height + model.borderWidth * 2;
								const barWidth = dataItem.width;
								let threshold = 10;
								if (typeof chart.config.options.horizontalBarLabelsThreshold !== 'undefined') {
									threshold = chart.config.options.horizontalBarLabelsThreshold;
								}
								if (dataItem._view.height + threshold < labelHeight || barWidth + threshold < labelWidth) {
									dataItem.$datalabels._model.positioner = () => {
										return false;
									};
								} else {
									dataItem.$datalabels._model = model;
								}
							}
						}
					}
				},
				/**
				 * Fix to long axis labels
				 * @param  {Chart}  chart    Chart instance
				 * @return {Boolean}       [description]
				 */
				fixXAxisLabels: function fixXAxisLabels(chart) {
					let shortenXTicks = function shortenXTicks(data, options) {
						if (typeof options.scales === 'undefined') {
							options.scales = {};
						}
						if (typeof options.scales.xAxes === 'undefined') {
							options.scales.xAxes = [{}];
						}
						options.scales.xAxes.forEach((axis) => {
							if (typeof axis.ticks === 'undefined') {
								axis.ticks = {};
							}
							axis.ticks.callback = function xAxisTickCallback(value, index, values) {
								if (value.length > 13) {
									return value.substr(0, 10) + '...';
								}
								return value;
							};
						});
						return options;
					};
					let rotateXLabels90 = function rotateXLabels90(data, options) {
						if (typeof options.scales === 'undefined') {
							options.scales = {};
						}
						if (typeof options.scales.xAxes === 'undefined') {
							options.scales.xAxes = [{}];
						}
						options.scales.xAxes.forEach((axis) => {
							if (typeof axis.ticks === 'undefined') {
								axis.ticks = {};
							}
							axis.ticks.minRotation = 90;
						});
						return options;
					};

					chart.data.datasets.forEach((dataset, index) => {
						if (dataset._updated) {
							return false;
						}
						for (let prop in dataset._meta) {
							if (dataset._meta.hasOwnProperty(prop)) {
								for (let i = 0, len = dataset._meta[prop].data.length; i < len; i++) {
									const metaDataItem = dataset._meta[prop].data[i];
									const label = metaDataItem._xScale.ticks[i];
									const ctx = metaDataItem._xScale.ctx;
									let categoryWidth = metaDataItem._xScale.width / dataset._meta[prop].data.length;
									if (typeof metaDataItem._xScale.options.categoryPercentage !== 'undefined') {
										// if it is bar chart there is category percentage option that we should use
										categoryWidth *= metaDataItem._xScale.options.categoryPercentage;
									}
									const fullWidth = ctx.measureText(label).width;
									if (categoryWidth < fullWidth) {
										const shortened = label.substr(0, 10) + '...';
										const shortenedWidth = ctx.measureText(shortened).width;
										if (categoryWidth < shortenedWidth) {
											chart.options = rotateXLabels90(chart.data, chart.options);
											chart.options = shortenXTicks(chart.data, chart.options);
										} else {
											chart.options = shortenXTicks(chart.data, chart.options);
										}
										if (!dataset._updated) {
											dataset._updated = true;
											chart.update();
											// recalculate positions for smooth animation (for all datasets)
											chart.data.datasets.forEach((dataset, index) => {
												dataset._meta[prop].data.forEach((metaDataItem, dataIndex) => {
													metaDataItem._view.x = metaDataItem._xScale.getPixelForValue(index, dataIndex);
													metaDataItem._view.base = metaDataItem._xScale.getBasePixel();
													metaDataItem._view.width =
														(metaDataItem._xScale.width / dataset._meta[prop].data.length) *
														metaDataItem._xScale.options.categoryPercentage *
														metaDataItem._xScale.options.barPercentage;
												});
											});
											break;
										}
									}
								}
								dataset._updated = true;
							}
						}
					});
				},
				/**
				 * Fix too long axis labels  - try to shorten and rotate
				 * @param  {Chart}  chart    Chart instance
				 * @return {Boolean}
				 */
				fixYAxisLabels: function fixYAxisLabels(chart) {
					let shortenYTicks = function shortenYTicks(data, options) {
						if (typeof options.scales === 'undefined') {
							options.scales = {};
						}
						if (typeof options.scales.yAxes === 'undefined') {
							options.scales.yAxes = [{}];
						}
						options.scales.yAxes.forEach((axis) => {
							if (typeof axis.ticks === 'undefined') {
								axis.ticks = {};
							}
							axis.ticks.callback = function yAxisTickCallback(value, index, values) {
								if (value.length > 13) {
									return value.substr(0, 10) + '...';
								}
								return value;
							};
						});
						return options;
					};
					chart.data.datasets.forEach((dataset, index) => {
						if (dataset._updated) {
							return false;
						}
						for (let prop in dataset._meta) {
							if (dataset._meta.hasOwnProperty(prop)) {
								// we have meta
								for (let i = 0, len = dataset._meta[prop].data.length; i < len; i++) {
									const metaDataItem = dataset._meta[prop].data[i];
									const label = metaDataItem._view.label;
									if (label.length > 13) {
										chart.options = shortenYTicks(chart.data, chart.options);
										if (!dataset._updated) {
											dataset._updated = true;
											chart.update();
											// recalculate positions for smooth animation (for all datasets)
											chart.data.datasets.forEach((dataset, index) => {
												dataset._meta[prop].data.forEach((metaDataItem, dataIndex) => {
													if (typeof metaDataItem._xScale !== 'undefined') {
														metaDataItem._view.x = metaDataItem._xScale.getPixelForValue(index, dataIndex);
														metaDataItem._view.base = metaDataItem._xScale.getBasePixel();
														metaDataItem._view.width =
															(metaDataItem._xScale.width / dataset._meta[prop].data.length) *
															metaDataItem._xScale.options.categoryPercentage *
															metaDataItem._xScale.options.barPercentage;
													}
												});
											});
											break;
										}
									}
								}
								dataset._updated = true;
							}
						}
					});
				}
			}
		},
		/**
		 * Get function from global functions from replacement string
		 * @param  {String} replacementStr replacement string from getFunctionReplacementString method
		 * @return {Function}
		 */
		getFunctionFromReplacementString: function getFunctionFromReplacementString(replacementStr) {
			let assignResult = false;
			if (replacementStr.substr(replacementStr.length - 2) === '()') {
				replacementStr = replacementStr.substr(0, replacementStr.length - 2);
				assignResult = true;
			}
			const splitted = replacementStr.split(':');
			if (splitted.length !== 2) {
				app.errorLog(
					new Error(
						"Function replacement string should look like 'function:path.to.fn' not like '" + replacementStr + "'"
					)
				);
			}
			let finalFunction = splitted[1].split('.').reduce((previous, current) => {
				return previous[current];
			}, this.globalChartFunctions);
			if (typeof finalFunction !== 'function') {
				app.errorLog(new Error('Global function does not exists: ' + splitted[1]));
			}
			if (!assignResult) {
				return finalFunction.bind(this);
			}
			return finalFunction.call(this);
		},
		/**
		 * Should options property be replaced by function?
		 * @param  {String}  str
		 * @return {Boolean}
		 */
		isReplacementString: function isReplacementString(str) {
			if (typeof str !== 'string') {
				return false;
			}
			return str.substr(0, 9) === 'function:';
		},
		/**
		 * Recursively parse options and replace function replacement strings to functions
		 * @param  {Object} options
		 * @param  {bool} afterInit are we parsing chart after it was mounted ?
		 * @return {Object} options with replaced string functions
		 */
		parseOptionsObject: function parseOptionsObject(options, original, afterInit = false) {
			let result = {};
			for (let propertyName in options) {
				let value = options[propertyName];
				if (afterInit) {
					if (propertyName.substr(0, 1) === '_') {
						result[propertyName] = value;
					} else if (Array.isArray(value)) {
						result[propertyName] = this.parseOptionsArray(value, original, afterInit);
					} else if (typeof value === 'object' && value !== null) {
						result[propertyName] = this.parseOptionsObject(value, original, afterInit);
					} else {
						result[propertyName] = value;
					}
				} else {
					if (propertyName.substr(0, 1) === '_') {
						result[propertyName] = value;
					} else if (this.isReplacementString(value)) {
						result[propertyName] = this.getFunctionFromReplacementString(value, afterInit, original);
					} else if (Array.isArray(value)) {
						result[propertyName] = this.parseOptionsArray(value, original, afterInit);
					} else if (typeof value === 'object' && value !== null) {
						result[propertyName] = this.parseOptionsObject(value, original, afterInit);
					} else {
						result[propertyName] = value;
					}
				}
			}
			return result;
		},
		/**
		 * Recursively parse options in array form and replace function replacement string with functions
		 * @param  {Array} arr
		 * @param  {bool} afterInit are we after chart js was mounted?
		 * @return {Array}
		 */
		parseOptionsArray: function parseOptionsArray(arr, original, afterInit = false) {
			return arr.map((item, index) => {
				if (this.isReplacementString(item)) {
					return this.getFunctionFromReplacementString(value);
				} else if (Array.isArray(item)) {
					return this.parseOptionsArray(item, original, afterInit);
				} else if (typeof item === 'object' && item !== null) {
					return this.parseOptionsObject(item, original, afterInit);
				}
				return item;
			});
		},
		/**
		 * Recursively parse options object and replace function replacement strings with global functions
		 * @param  {Object} options
		 * @param  {bool} afterInit - is chartjs loaded ?
		 * @return {Object}
		 */
		parseOptions: function parseOptions(options, original, afterInit = false) {
			if (Array.isArray(options)) {
				return this.parseOptionsArray(options, original, afterInit);
			} else if (typeof options === 'object' && options !== null) {
				return this.parseOptionsObject(options, original, afterInit);
			}
			app.errorLog(new Error('Unknown options format [' + typeof options + '] - should be object.'));
		},
		/**
		 * Remove 'Divided' from chart sub type
		 * for example 'barDivided' => 'bar'
		 *
		 * @param {String} chartSubType
		 * @return {String}
		 */
		removeStackedFromName(chartSubType) {
			const dividedPos = chartSubType.indexOf('Stacked');
			if (dividedPos > 0) {
				return chartSubType.substr(0, dividedPos);
			}
			return chartSubType;
		},
		/**
		 * Get global charts default configuration - may be loaded from database in the future
		 * We can modify something basing on chartData received from server (some custom options etc.)
		 *
		 * @param  {Object} chartData received from request ['labels':[],'datasets':['data':[]]] etc
		 * @param  {String} chartSubType 'bar','pie' etc..
		 * @return {Object}
		 */
		getGlobalDefaultChartsOptions: function getGlobalDefaultChartsOptions(chartSubType, chartData) {
			const options = {
				bar: {
					basic: {
						maintainAspectRatio: false,
						title: {
							display: false
						},
						legend: {
							display: 'function:legend.display()'
						},
						tooltips: {
							mode: 'point',
							callbacks: {
								label: 'function:tooltips.label',
								title: 'function:tooltips.title'
							}
						},
						scales: {
							xAxes: [
								{
									ticks: {
										autoSkip: false,
										beginAtZero: true,
										maxRotation: 90,
										callback: 'function:scales.formatAxesLabels'
									}
								}
							],
							yAxes: [
								{
									ticks: {
										autoSkip: false,
										beginAtZero: true,
										callback: 'function:scales.formatAxesLabels'
									}
								}
							]
						}
					},
					dataset: {
						datalabels: {
							font: {
								size: 11
							},
							color: 'white',
							backgroundColor: 'rgba(0,0,0,0.2)',
							borderColor: 'rgba(255,255,255,0.2)',
							borderWidth: 2,
							borderRadius: 2,
							anchor: 'center',
							align: 'center',
							formatter: 'function:datalabels.formatter',
							display: 'function:datalabels.display'
						}
					},
					plugins: [
						{
							beforeDraw: 'function:plugins.fixXAxisLabels'
						},
						{
							beforeDraw: 'function:plugins.hideVerticalBarDatalabelsIfNeeded'
						}
					]
				},
				barStacked: {
					basic: {
						maintainAspectRatio: false,
						title: {
							display: false
						},
						legend: {
							display: 'function:legend.display()'
						},
						tooltips: {
							mode: 'point',
							callbacks: {
								label: 'function:tooltips.label',
								title: 'function:tooltips.title'
							}
						},
						scales: {
							xAxes: [
								{
									stacked: true,
									ticks: {
										autoSkip: false,
										beginAtZero: true,
										maxRotation: 90,
										callback: 'function:scales.formatAxesLabels'
									}
								}
							],
							yAxes: [
								{
									stacked: true,
									ticks: {
										autoSkip: false,
										beginAtZero: true,
										callback: 'function:scales.formatAxesLabels'
									}
								}
							]
						}
					},
					dataset: {
						datalabels: {
							font: {
								size: 11
							},
							color: 'white',
							backgroundColor: 'rgba(0,0,0,0.2)',
							borderColor: 'rgba(255,255,255,0.2)',
							borderWidth: 2,
							borderRadius: 2,
							anchor: 'center',
							align: 'center',
							formatter: 'function:datalabels.formatter',
							display: 'function:datalabels.display'
						}
					},
					plugins: [
						{
							beforeDraw: 'function:plugins.fixXAxisLabels'
						},
						{
							beforeDraw: 'function:plugins.hideVerticalBarDatalabelsIfNeeded'
						}
					]
				},
				horizontalBar: {
					basic: {
						maintainAspectRatio: false,
						title: {
							display: false
						},
						legend: {
							display: 'function:legend.display()'
						},
						tooltips: {
							mode: 'point',
							callbacks: {
								label: 'function:tooltips.label',
								title: 'function:tooltips.title'
							}
						},
						scales: {
							xAxes: [
								{
									ticks: {
										autoSkip: false,
										beginAtZero: true,
										maxRotation: 90,
										callback: 'function:scales.formatAxesLabels'
									}
								}
							],
							yAxes: [
								{
									ticks: {
										autoSkip: false,
										beginAtZero: true,
										callback: 'function:scales.formatAxesLabels'
									}
								}
							]
						}
					},
					dataset: {
						datalabels: {
							font: {
								size: 11
							},
							color: 'white',
							backgroundColor: 'rgba(0,0,0,0.2)',
							borderColor: 'rgba(255,255,255,0.2)',
							borderWidth: 2,
							borderRadius: 2,
							anchor: 'center',
							align: 'center',
							formatter: 'function:datalabels.formatter',
							display: 'function:datalabels.display'
						}
					},
					plugins: [
						{
							beforeDraw: 'function:plugins.fixYAxisLabels'
						},
						{
							beforeDraw: 'function:plugins.hideHorizontalBarDatalabelsIfNeeded'
						}
					]
				},
				horizontalBarStacked: {
					basic: {
						maintainAspectRatio: false,
						title: {
							display: false
						},
						legend: {
							display: 'function:legend.display()'
						},
						tooltips: {
							mode: 'point',
							callbacks: {
								label: 'function:tooltips.label',
								title: 'function:tooltips.title'
							}
						},
						scales: {
							xAxes: [
								{
									stacked: true,
									ticks: {
										autoSkip: false,
										beginAtZero: true,
										maxRotation: 90,
										callback: 'function:scales.formatAxesLabels'
									}
								}
							],
							yAxes: [
								{
									stacked: true,
									ticks: {
										autoSkip: false,
										beginAtZero: true,
										callback: 'function:scales.formatAxesLabels'
									}
								}
							]
						}
					},
					dataset: {
						datalabels: {
							font: {
								size: 11
							},
							color: 'white',
							backgroundColor: 'rgba(0,0,0,0.2)',
							borderColor: 'rgba(255,255,255,0.2)',
							borderWidth: 2,
							borderRadius: 2,
							anchor: 'center',
							align: 'center',
							formatter: 'function:datalabels.formatter',
							display: 'function:datalabels.display'
						}
					},
					plugins: [
						{
							beforeDraw: 'function:plugins.fixYAxisLabels'
						},
						{
							beforeDraw: 'function:plugins.hideHorizontalBarDatalabelsIfNeeded'
						}
					]
				},
				line: {
					basic: {
						maintainAspectRatio: false,
						title: {
							display: false
						},
						legend: {
							display: 'function:legend.display()'
						},
						tooltips: {
							mode: 'point',
							callbacks: {
								label: 'function:tooltips.label',
								title: 'function:tooltips.title'
							}
						},
						scales: {
							xAxes: [
								{
									ticks: {
										autoSkip: false,
										beginAtZero: true,
										maxRotation: 90,
										callback: 'function:scales.formatAxesLabels',
										labelOffset: 0
									}
								}
							],
							yAxes: [
								{
									ticks: {
										autoSkip: false,
										beginAtZero: true,
										callback: 'function:scales.formatAxesLabels'
									}
								}
							]
						}
					},
					dataset: {
						fill: false,
						datalabels: {
							font: {
								size: 11
							},
							color: 'white',
							backgroundColor: 'rgba(0,0,0,0.5)',
							borderColor: 'rgba(255,255,255,0.5)',
							borderWidth: 2,
							borderRadius: 2,
							anchor: 'bottom',
							align: 'bottom',
							formatter: 'function:datalabels.formatter',
							display: 'function:datalabels.display'
						}
					},
					plugins: [
						{
							beforeDraw: 'function:plugins.fixXAxisLabels'
						}
					]
				},
				lineStacked: {
					basic: {
						maintainAspectRatio: false,
						title: {
							display: false
						},
						legend: {
							display: 'function:legend.display()'
						},
						tooltips: {
							mode: 'point',
							callbacks: {
								label: 'function:tooltips.label',
								title: 'function:tooltips.title'
							}
						},
						scales: {
							xAxes: [
								{
									ticks: {
										autoSkip: false,
										beginAtZero: true,
										maxRotation: 90,
										callback: 'function:scales.formatAxesLabels',
										labelOffset: 0
									}
								}
							],
							yAxes: [
								{
									stacked: true,
									ticks: {
										autoSkip: false,
										beginAtZero: true,
										callback: 'function:scales.formatAxesLabels'
									}
								}
							]
						}
					},
					dataset: {
						fill: false,
						datalabels: {
							font: {
								size: 11
							},
							color: 'white',
							backgroundColor: 'rgba(0,0,0,0.5)',
							borderColor: 'rgba(255,255,255,0.5)',
							borderWidth: 2,
							borderRadius: 2,
							anchor: 'bottom',
							align: 'bottom',
							formatter: 'function:datalabels.formatter',
							display: 'function:datalabels.display'
						}
					},
					plugins: [
						{
							beforeDraw: 'function:plugins.fixXAxisLabels'
						}
					]
				},
				linePlain: {
					basic: {
						maintainAspectRatio: false,
						title: {
							display: false
						},
						legend: {
							display: 'function:legend.display()'
						},
						tooltips: {
							mode: 'point',
							callbacks: {
								label: 'function:tooltips.label',
								title: 'function:tooltips.title'
							}
						},
						scales: {
							xAxes: [
								{
									ticks: {
										autoSkip: false,
										beginAtZero: true,
										maxRotation: 90,
										callback: 'function:scales.formatAxesLabels',
										labelOffset: 0
									}
								}
							],
							yAxes: [
								{
									ticks: {
										autoSkip: false,
										beginAtZero: true,
										callback: 'function:scales.formatAxesLabels'
									}
								}
							]
						}
					},
					dataset: {
						lineTension: 0,
						fill: false,
						datalabels: {
							font: {
								size: 11
							},
							color: 'white',
							backgroundColor: 'rgba(0,0,0,0.5)',
							borderColor: 'rgba(255,255,255,0.5)',
							borderWidth: 2,
							borderRadius: 2,
							anchor: 'bottom',
							align: 'bottom',
							formatter: 'function:datalabels.formatter',
							display: 'function:datalabels.display'
						}
					},
					plugins: [
						{
							beforeDraw: 'function:plugins.fixXAxisLabels'
						}
					]
				},
				linePlainStacked: {
					basic: {
						maintainAspectRatio: false,
						title: {
							display: false
						},
						legend: {
							display: 'function:legend.display()'
						},
						tooltips: {
							mode: 'point',
							callbacks: {
								label: 'function:tooltips.label',
								title: 'function:tooltips.title'
							}
						},
						scales: {
							xAxes: [
								{
									ticks: {
										autoSkip: false,
										beginAtZero: true,
										maxRotation: 90,
										callback: 'function:scales.formatAxesLabels',
										labelOffset: 0
									}
								}
							],
							yAxes: [
								{
									stacked: true,
									ticks: {
										autoSkip: false,
										beginAtZero: true,
										callback: 'function:scales.formatAxesLabels'
									}
								}
							]
						}
					},
					dataset: {
						fill: false,
						lineTension: 0,
						datalabels: {
							font: {
								size: 11
							},
							color: 'white',
							backgroundColor: 'rgba(0,0,0,0.5)',
							borderColor: 'rgba(255,255,255,0.5)',
							borderWidth: 2,
							borderRadius: 2,
							anchor: 'bottom',
							align: 'bottom',
							formatter: 'function:datalabels.formatter',
							display: 'function:datalabels.display'
						}
					},
					plugins: [
						{
							beforeDraw: 'function:plugins.fixXAxisLabels'
						}
					]
				},
				pie: {
					basic: {
						maintainAspectRatio: false,
						title: {
							display: false
						},
						legend: {
							display: true,
							labels: {
								generateLabels: 'function:legend.generateLabels'
							}
						},
						cutoutPercentage: 0,
						layout: {
							padding: {
								bottom: 12
							}
						},
						tooltips: {
							mode: 'point',
							callbacks: {
								label: 'function:tooltips.label',
								title: 'function:tooltips.title'
							}
						},
						scales: {}
					},
					dataset: {
						datalabels: {
							font: {
								size: 11
							},
							color: 'white',
							backgroundColor: 'rgba(0,0,0,0.5)',
							borderColor: 'rgba(255,255,255,0.5)',
							borderWidth: 2,
							borderRadius: 4,
							anchor: 'end',
							align: 'center',
							formatter: 'function:datalabels.formatter',
							display: 'function:datalabels.display'
						}
					},
					plugins: []
				},
				doughnut: {
					basic: {
						maintainAspectRatio: false,
						title: {
							display: false
						},
						legend: {
							display: true,
							onClick: 'function:legend.onClick',
							labels: {
								generateLabels: 'function:legend.generateLabels'
							}
						},
						cutoutPercentage: 50,
						layout: {
							padding: {
								bottom: 12
							}
						},
						tooltips: {
							mode: 'point',
							callbacks: {
								label: 'function:tooltips.label',
								title: 'function:tooltips.title'
							}
						},
						scales: {}
					},
					dataset: {
						datalabels: {
							font: {
								size: 11
							},
							color: 'white',
							backgroundColor: 'rgba(0,0,0,0.5)',
							borderColor: 'rgba(255,255,255,0.5)',
							borderWidth: 2,
							borderRadius: 4,
							anchor: 'end',
							align: 'center',
							formatter: 'function:datalabels.formatter',
							display: 'function:datalabels.display'
						}
					},
					plugins: []
				},
				funnel: {
					basic: {
						maintainAspectRatio: false,
						title: {
							display: false
						},
						legend: {
							display: false
						},
						sort: 'desc',
						tooltips: {
							mode: 'point',
							callbacks: {
								label: 'function:tooltips.label',
								title: 'function:tooltips.title'
							}
						},
						scales: {
							yAxes: [
								{
									display: true,
									beginAtZero: true,
									ticks: {
										callback: 'function:scales.formatAxesLabels'
									}
								}
							]
						}
					},
					dataset: {
						datalabels: {
							display: false
						}
					},
					plugins: [
						{
							beforeDraw: 'function:plugins.fixYAxisLabels'
						}
					]
				}
			};
			if (typeof options[chartSubType] !== 'undefined') {
				return options[chartSubType];
			}
			// if divided and standard chart types are equal
			const notStackedChartSubType = this.removeStackedFromName(chartSubType);
			if (typeof options[notStackedChartSubType] !== 'undefined') {
				return options[notStackedChartSubType];
			}
			app.errorLog(new Error(chartSubType + ' chart does not exists!'));
		},
		/**
		 * Get default chart basic options for specified chart subtype
		 *
		 * @param  {String} chartSubType 'bar','pie'...
		 * @param  {Object} chartData received from request ['labels':[],'datasets':['data':[]]] etc
		 * @return {Object}
		 */
		getDefaultBasicOptions: function getDefaultBasicOptions(chartSubType, chartData) {
			return this.getGlobalDefaultChartsOptions(chartSubType, chartData).basic;
		},
		/**
		 * Get default dataset options for specified chart subtype
		 *
		 * @param  {String} chartSubType 'bar','pie'...
		 * @param  {Object} chartData received from request ['labels':[],'datasets':['data':[]]] etc
		 * @return {Object}
		 */
		getDefaultDatasetOptions: function getDefaultDatasetOptions(chartSubType, chartData) {
			return this.getGlobalDefaultChartsOptions(chartSubType, chartData).dataset;
		},
		/**
		 * Get default plugins for specified chart subtype
		 *
		 * @param  {String} chartSubType 'bar','pie'...
		 * @param  {Object} chartData received from request ['labels':[],'datasets':['data':[]]] etc
		 * @return {Object}
		 */
		getDefaultPlugins: function getDefaultPlugins(chartSubType, chartData) {
			return this.getGlobalDefaultChartsOptions(chartSubType, chartData).plugins;
		},
		getContainer: function getContainer() {
			return this.container;
		},
		/**
		 * Get widget content
		 * @returns {jQuery}
		 */
		getContainerContent: function getContainer() {
			return this.getContainer().find('.dashboardWidgetContent');
		},
		setContainer: function setContainer(element) {
			this.container = element;
			return this;
		},
		isEmptyData: function isEmptyData() {
			return this.getContainer().find('.widgetData').length === 0 || this.getContainer().find('.noDataMsg').length > 0;
		},
		getUserDateFormat: function getUserDateFormat() {
			return CONFIG.dateFormat;
		},
		getChartContainer: function getChartContainer(useCache) {
			if (typeof useCache === 'undefined') {
				useCache = false;
			}
			if (this.plotContainer === false || !useCache) {
				this.plotContainer = this.getContainer().find('.widgetChartContainer').find('canvas').get(0);
			}
			return this.plotContainer;
		},
		registerRecordsCount: function registerRecordsCount() {
			var thisInstance = this;
			var recordsCountBtn = thisInstance.getContainer().find('.recordCount');
			recordsCountBtn.on('click', function () {
				var url = recordsCountBtn.data('url');
				AppConnector.request(url).done(function (response) {
					recordsCountBtn.find('.count').html(response.result.totalCount);
					recordsCountBtn.find('.fas').addClass('d-none').attr('aria-hidden', true);
					recordsCountBtn.find('a').removeClass('d-none').attr('aria-hidden', false);
				});
			});
		},
		/**
		 * Load scrollbar
		 */
		loadScrollbar: function loadScrollbar() {
			let container = $(this.getChartContainer(false));
			if (!container.length) {
				container = this.getContainerContent();
			}
			if (!container.length) {
				return;
			}
			const widget = container.closest('.dashboardWidget');
			const content = widget.find('.dashboardWidgetContent');
			const footer = widget.find('.dashboardWidgetFooter');
			let adjustedHeight = widget.innerHeight() - widget.find('.dashboardWidgetHeader').outerHeight();
			if (footer.length) {
				adjustedHeight -= footer.outerHeight();
			}
			if (!content.length) {
				return;
			}
			content.css('height', adjustedHeight + 'px');
			content.css('max-height', adjustedHeight + 'px');
			if (typeof this.scrollbar !== 'undefined') {
				this.scrollbar.update();
			} else {
				this.scrollbar = app.showNewScrollbar(content, {
					wheelPropagation: true
				});
			}
		},
		restrictContentDrag: function restrictContentDrag() {
			this.getContainer().on('mousedown.draggable', function (e) {
				var element = jQuery(e.target);
				var isHeaderElement = element.closest('.dashboardWidgetHeader').length > 0 ? true : false;
				if (isHeaderElement) {
					return;
				}
				//Stop the event propagation so that drag will not start for contents
				e.stopPropagation();
			});
		},
		/**
		 * Get data from JSON encoded input value
		 *
		 * @return {object} data from request
		 */
		generateData: function generateData() {
			var thisInstance = this;
			var jData = thisInstance.getContainer().find('.widgetData').val();
			if (typeof jData === 'undefined') {
				return (thisInstance.chartData = jData);
			}
			thisInstance.chartData = JSON.parse(jData);
			return thisInstance.chartData;
		},
		positionNoDataMsg: function positionNoDataMsg() {
			var container = this.getContainer();
			var widgetContentsContainer = container.find('.dashboardWidgetContent');
			var noDataMsgHolder = widgetContentsContainer.find('.noDataMsg');
			noDataMsgHolder.position({
				my: 'center center',
				at: 'center center',
				of: widgetContentsContainer
			});
		},
		/**
		 * Print html content as image
		 * @param {jQuery} element
		 */
		printHtml(element) {
			let widget = element.closest('.dashboardWidget'),
				title = widget.find('.dashboardTitle').prop('title'),
				printContainer = widget.find('.js-print__container').get(0),
				imgEl = $('<img style="width:100%">');
			imgEl.get(0).onload = () => {
				let width = $(printContainer).outerWidth();
				let height = $(printContainer).outerHeight();
				if (width < 600) {
					width = 600;
				}
				if (height < 400) {
					height = 400;
				}
				this.printImage(imgEl.get(0), title, width, height);
			};
			app.htmlToImage(printContainer, (imageBase64) => {
				imgEl.get(0).src = imageBase64;
			});
		},
		/**
		 * Download html content as image
		 * @param {jQuery} element
		 */
		downloadHtmlAsImage(element) {
			let widget = element.closest('.dashboardWidget'),
				title = widget.find('.dashboardTitle').prop('title');
			app.htmlToImage(widget.find('.js-print__container').get(0), (imageBase64) => {
				let anchor = document.createElement('a');
				anchor.setAttribute('href', imageBase64);
				anchor.setAttribute('download', title + '.png');
				anchor.click();
			});
		},
		//Place holdet can be extended by child classes and can use this to handle the post load
		postLoadWidget: function postLoadWidget() {
			if (!this.isEmptyData()) {
				this.loadChart(this.options);
			} else {
				this.positionNoDataMsg();
			}
			this.registerSectionClick();
			this.registerFilter();
			this.registerFilterChangeEvent();
			this.restrictContentDrag();
			this.registerWidgetSwitch();
			this.registerChangeSorting();
			this.registerLoadMore();
			this.registerHeaderButtons();
			this.loadScrollbar();
		},
		postRefreshWidget: function postRefreshWidget() {
			this.loadScrollbar();
			if (!this.isEmptyData()) {
				this.loadChart(this.options);
			} else {
				this.positionNoDataMsg();
			}
			this.registerSectionClick();
			this.registerLoadMore();
		},
		setSortingButton: function setSortingButton(currentElement) {
			if (currentElement.length) {
				let container = this.getContainer(),
					drefresh = container.find('.js-widget-refresh'),
					url = drefresh.data('url');
				url = url.replace('&sortorder=desc', '');
				url = url.replace('&sortorder=asc', '');
				url += '&sortorder=';
				let sort = currentElement.data('sort'),
					sortorder = 'desc',
					icon = 'fa-sort-amount-down',
					iconBase = 'fa-sort-amount-up';
				if (sort == 'desc') {
					sortorder = 'asc';
					icon = 'fa-sort-amount-up';
					iconBase = 'fa-sort-amount-down';
				}
				currentElement.data('sort', sortorder);
				currentElement.attr('title', currentElement.data(sortorder));
				currentElement.attr('alt', currentElement.data(sortorder));
				url += sortorder;
				currentElement.find('.fas').removeClass(iconBase).addClass(icon);
				drefresh.data('url', url);
			}
		},
		getChartImage() {
			const base64Image = this.chartInstance.toBase64Image();
			const image = new Image();
			image.src = base64Image;
			return image;
		},
		printImage(imgEl, title, width, height) {
			const print = window.open('', 'PRINT', 'height=' + height + ',width=' + width);
			print.document.write('<html><head><title>' + title + '</title>');
			print.document.write('</head><body >');
			print.document.write($('<div>').append(imgEl).html());
			print.document.write('</body></html>');
			print.document.close(); // necessary for IE >= 10
			print.focus(); // necessary for IE >= 10
			setTimeout(function () {
				print.print();
				print.close();
			}, 1000);
		},
		registerHeaderButtons: function registerHeaderButtons() {
			const container = this.getContainer();
			const header = container.find('.dashboardWidgetHeader');
			const downloadWidget = header.find('.downloadWidget');
			const printWidget = header.find('.printWidget');
			printWidget.on('click', (e) => {
				const imgEl = this.getChartImage();
				this.printImage(imgEl, header.find('.dashboardTitle').text(), 600, 400);
			});
			downloadWidget.on('click', (e) => {
				const imgEl = $(this.getChartImage());
				const a = $('<a>')
					.attr('href', imgEl.attr('src'))
					.attr('download', header.find('.js-widget__header__title').text() + '.png')
					.appendTo(container);
				a[0].click();
				a.remove();
			});
			container.find('.js-widget-quick-create').on('click', function (e) {
				App.Components.QuickCreate.createRecord($(this).data('module-name'));
			});
		},
		registerChangeSorting: function registerChangeSorting() {
			var thisInstance = this;
			var container = this.getContainer();
			thisInstance.setSortingButton(container.find('.changeRecordSort'));
			container.find('.changeRecordSort').on('click', function (e) {
				var drefresh = container.find('.js-widget-refresh');
				thisInstance.setSortingButton(jQuery(e.currentTarget));
				drefresh.click();
			});
		},
		registerWidgetSwitch: function registerWidgetSwitch() {
			var thisInstance = this;
			var switchButtons = this.getContainer().find('.dashboardWidgetHeader .js-switch--calculations');
			thisInstance.setUrlSwitch(switchButtons);
			switchButtons.on('change', (e) => {
				var currentElement = $(e.currentTarget);
				var dashboardWidgetHeader = currentElement.closest('.dashboardWidgetHeader');
				var drefresh = dashboardWidgetHeader.find('.js-widget-refresh');
				thisInstance.setUrlSwitch(currentElement).done(function (data) {
					if (data) {
						drefresh.click();
					}
				});
			});
		},
		setUrlSwitch: function setUrlSwitch(switchButtons) {
			var aDeferred = jQuery.Deferred();
			switchButtons.each(function (index, e) {
				var currentElement = jQuery(e);
				var dashboardWidgetHeader = currentElement.closest('.dashboardWidgetHeader');
				var drefresh = dashboardWidgetHeader.find('.js-widget-refresh');
				var url = drefresh.data('url');
				var urlparams = currentElement.data('urlparams');
				if (urlparams !== '') {
					var switchUrl = currentElement.data('url-value');
					url = url.replace('&' + urlparams + '=' + switchUrl, '');
					url += '&' + urlparams + '=' + switchUrl;
					drefresh.data('url', url);
					aDeferred.resolve(true);
				} else {
					aDeferred.reject();
				}
			});
			return aDeferred.promise();
		},
		getFilterData: function getFilterData() {
			return {};
		},
		/**
		 * Refresh widget
		 * @returns {undefined}
		 */
		refreshWidget: function refreshWidget() {
			let thisInstance = this;
			let parent = this.getContainer();
			let element = parent.find('.js-widget-refresh');
			let url = element.data('url');
			let contentContainer = parent.find('.dashboardWidgetContent');
			let params = url;
			let widgetFilters = parent.find('.widgetFilter');
			if (widgetFilters.length > 0) {
				params = {};
				params.url = url;
				params.data = {};
				widgetFilters.each((index, domElement) => {
					let widgetFilter = $(domElement);
					let filterName = widgetFilter.attr('name');
					if ('checkbox' === widgetFilter.attr('type')) {
						params.data[filterName] = widgetFilter.is(':checked');
					} else {
						params.data[filterName] = widgetFilter.val();
					}
				});
			}

			let additionalWidgetFilters = parent.find('.js-chartFilter__additional-filter-field');
			if (additionalWidgetFilters.length > 0) {
				params = {};
				params.url = url;
				params.data = {};
				additionalWidgetFilters.each((index, domElement) => {
					let widgetFilter = jQuery(domElement);
					let filterName = widgetFilter.attr('name');
					let arr = false;
					if (filterName.substr(-2) === '[]') {
						arr = true;
						filterName = filterName.substr(0, filterName.length - 2);
						if (!Array.isArray(params.data[filterName])) {
							params.data[filterName] = [];
						}
					}
					if ('checkbox' === widgetFilter.attr('type')) {
						if (arr) {
							params.data[filterName].push(widgetFilter.is(':checked'));
						} else {
							params.data[filterName] = widgetFilter.is(':checked');
						}
					} else {
						if (arr) {
							params.data[filterName].push(widgetFilter.val());
						} else {
							params.data[filterName] = widgetFilter.val();
						}
					}
				});
			}
			let refreshContainer = parent.find('.dashboardWidgetContent');
			let refreshContainerFooter = parent.find('.dashboardWidgetFooter');
			refreshContainer.html('');
			refreshContainerFooter.html('');
			refreshContainer.progressIndicator();
			if (
				this.paramCache &&
				(additionalWidgetFilters.length || widgetFilters.length || parent.find('.listSearchContributor'))
			) {
				thisInstance.setFilterToCache(params.url ? params.url : params, params.data ? params.data : {});
			}
			AppConnector.request(params)
				.done((data) => {
					data = $(data);
					let footer = data.filter('.widgetFooterContent');
					refreshContainer.progressIndicator({
						mode: 'hide'
					});
					if (footer.length) {
						footer = footer.clone(true, true);
						refreshContainerFooter.html(footer);
						data.each(function (n, e) {
							if (jQuery(this).hasClass('widgetFooterContent')) {
								data.splice(n, 1);
							}
						});
					}
					contentContainer.html(data).trigger(YetiForce_Widget_Js.widgetPostRefereshEvent);
				})
				.fail(() => {
					refreshContainer.progressIndicator({
						mode: 'hide'
					});
				});
		},
		registerFilter: function registerFilter() {
			const container = this.getContainer();
			const search = container.find('.listSearchContributor');
			const refreshBtn = container.find('.js-widget-refresh');
			const originalUrl = refreshBtn.data('url');
			const selects = container.find('.select2noactive');
			search.css('width', '100%');
			search.parent().addClass('w-100');
			search.each((index, element) => {
				const fieldInfo = $(element).data('fieldinfo');
				$(element).attr('placeholder', fieldInfo.label).data('placeholder', fieldInfo.label);
			});
			App.Fields.Picklist.changeSelectElementView(selects, 'select2', {
				containerCssClass: 'form-control'
			});
			App.Fields.Date.register(container);
			App.Fields.Date.registerRange(container);
			App.Fields.DateTime.register(container);
			search.on('change apply.daterangepicker', (e) => {
				let searchParams = [];
				container.find('.listSearchContributor').each((index, domElement) => {
					let searchInfo = [];
					const searchContributorElement = $(domElement);
					const fieldInfo = searchContributorElement.data('fieldinfo');
					const fieldName = searchContributorElement.attr('name');
					let searchValue = searchContributorElement.val();
					if (typeof searchValue === 'object') {
						if (searchValue == null) {
							searchValue = '';
						} else {
							searchValue = searchValue.join('##');
						}
					} else if ($.inArray(fieldInfo.type, ['tree']) >= 0) {
						searchValue = searchValue.replace(/,/g, '##');
					}
					searchValue = searchValue.trim();
					if (searchValue.length <= 0) {
						//continue
						return true;
					}
					let searchOperator = 'a';
					if (fieldInfo.hasOwnProperty('searchOperator')) {
						searchOperator = fieldInfo.searchOperator;
					} else if (
						jQuery.inArray(fieldInfo.type, [
							'modules',
							'time',
							'userCreator',
							'owner',
							'picklist',
							'tree',
							'boolean',
							'fileLocationType',
							'userRole',
							'multiReferenceValue',
							'currencyList'
						]) >= 0
					) {
						searchOperator = 'e';
					} else if (fieldInfo.type === 'date' || fieldInfo.type === 'datetime') {
						searchOperator = 'bw';
					} else if (fieldInfo.type === 'multipicklist' || fieldInfo.type === 'categoryMultipicklist') {
						searchOperator = 'c';
					}
					searchInfo.push(fieldName);
					searchInfo.push(searchOperator);
					searchInfo.push(searchValue);
					if ($.inArray(fieldInfo.type, ['tree', 'categoryMultipicklist']) != -1) {
						let searchInSubcategories = $(
							'.listViewHeaders .searchInSubcategories[data-columnname="' + fieldName + '"]'
						).prop('checked');
						if (searchInSubcategories) {
							searchOperator = 'ch';
						}
					}
					searchParams.push(searchInfo);
				});
				let url = originalUrl + '&search_params=' + JSON.stringify([searchParams]);
				refreshBtn.data('url', url);
				refreshBtn.trigger('click');
			});
		},
		registerFilterChangeEvent: function registerFilterChangeEvent() {
			let container = this.getContainer();
			container.on('change', '.widgetFilter', (e) => {
				container.find('.js-widget-refresh').trigger('click');
			});
			if (container.find('.widgetFilterByField').length) {
				App.Fields.Picklist.showSelect2ElementView(container.find('.select2noactive'));
				this.getContainer().on('change', '.widgetFilterByField .form-control', (e) => {
					container.find('.js-widget-refresh').trigger('click');
				});
			}
		},
		registerWidgetPostLoadEvent: function registerWidgetPostLoadEvent(container) {
			var thisInstance = this;
			container.on(YetiForce_Widget_Js.widgetPostLoadEvent, function (e) {
				thisInstance.postLoadWidget();
			});
		},
		registerWidgetPostRefreshEvent: function registerWidgetPostRefreshEvent(container) {
			var thisInstance = this;
			container.off(YetiForce_Widget_Js.widgetPostRefereshEvent);
			container.on(YetiForce_Widget_Js.widgetPostRefereshEvent, function (e) {
				thisInstance.postRefreshWidget();
			});
		},
		registerSectionClick: function registerSectionClick() {
			const thisInstance = this;
			let pointer = false;
			$(thisInstance.chartInstance.canvas)
				.on('click', function (e) {
					if (typeof thisInstance.getDataFromEvent(e, ['links']).links !== 'undefined') {
						window.location.href = thisInstance.getDataFromEvent(e, ['links']).links;
					}
				})
				.on('mousemove', function (e) {
					if (typeof thisInstance.getDataFromEvent(e, ['links']).links !== 'undefined') {
						if (!pointer) {
							$(this).css('cursor', 'pointer');
							pointer = true;
						}
					} else {
						if (pointer) {
							$(this).css('cursor', 'auto');
							pointer = false;
						}
					}
				})
				.on('mouseout', function () {
					if (pointer) {
						$(this).css('cursor', 'auto');
						pointer = false;
					}
				});
		},
		registerLoadMore: function registerLoadMore() {
			var thisInstance = this;
			var parent = thisInstance.getContainer();
			var contentContainer = parent.find('.dashboardWidgetContent');
			contentContainer.off('click', '.showMoreHistory');
			contentContainer.on('click', '.showMoreHistory', function (e) {
				var element = jQuery(e.currentTarget);
				element.hide();
				var parent = jQuery(e.delegateTarget).closest('.dashboardWidget');
				jQuery(parent).find('.slimScrollDiv').css('overflow', 'visible');
				var url = element.data('url') + '&content=true';
				let additionalFilter = parent.find('.widgetFilter');
				if (additionalFilter.length > 0) {
					additionalFilter.each(function () {
						url += '&' + $(this).attr('name') + '=' + $(this).val();
					});
				}
				if (parent.find('.changeRecordSort').length > 0) {
					url += '&sortorder=' + parent.find('.changeRecordSort').data('sort');
				}
				contentContainer.progressIndicator();
				AppConnector.request(url).done(function (data) {
					contentContainer.progressIndicator({
						mode: 'hide'
					});
					jQuery(parent).find('.dashboardWidgetContent').append(data);
					element.parent().remove();
					thisInstance.postRefreshWidget();
				});
			});
		},
		setFilterToCache: function setFilterToCache(url, data) {
			var paramCache = url;
			var container = this.getContainer();
			paramCache = paramCache.replace('&content=', '&notcontent=');
			for (var i in data) {
				if (typeof data[i] == 'object') {
					data[i] = JSON.stringify(data[i]);
				}
				paramCache += '&' + i + '=' + data[i];
			}
			var userId = CONFIG.userId;
			var name = container.attr('id');
			app.cacheSet(name + '_' + userId, paramCache);
		},
		registerCache: function registerCache(container) {
			if (container.data('cache') == 1) {
				this.paramCache = true;
			}
		},
		/**
		 * Load and display chart into the view
		 *
		 * @return {Chart} chartInstance
		 */
		loadChart: function loadChart() {
			if (typeof this.chartData === 'undefined' || typeof this.getChartContainer() === 'undefined') {
				return false;
			}
			this.getWidgetData(); // load widget data for label formatters
			const type = this.getType();
			let data = this.generateData();
			data.datasets = this.loadDatasetOptions(data);
			const options = this.parseOptions(this.loadBasicOptions(data));
			const plugins = this.parseOptions(this.loadPlugins(data));
			data = this.parseOptions(data);
			const chart = (this.chartInstance = new Chart(this.getChartContainer().getContext('2d'), {
				type,
				data,
				options,
				plugins
			}));
			// parse chart one more time after it was mounted - some options need to have chart loaded
			data.datasets = data.datasets.map((dataset, index) => {
				dataset.datasetIndex = index;
				return this.parseOptions(dataset, dataset, true);
			});
			return chart;
		},
		/**
		 * Get data from event like mouse hover,click etc - get data which belongs to pointed element
		 *
		 * @param {Object} e
		 * @param {array} additionalFields if element from event have additional
		 * array in dataset like links for data then additionalFields will look like ['links']
		 * @returns {object} {label,value,...additionalFields}
		 */
		getDataFromEvent: function getDataFromEvent(e, additionalFields) {
			let chart = this.chartInstance;
			const elements = chart.getElementAtEvent(e);
			if (elements.length === 0) {
				return false;
			}
			const element = elements[0];
			const dataIndex = element._index;
			const datasetIndex = element._datasetIndex;
			const eventData = {
				label: chart.data.labels[dataIndex],
				value: chart.data.datasets[0].data[dataIndex]
			};
			if (typeof additionalFields !== 'undefined' && Array.isArray(additionalFields)) {
				additionalFields.forEach((fieldName) => {
					if (
						typeof chart.data.datasets[datasetIndex][fieldName] !== 'undefined' &&
						typeof chart.data.datasets[datasetIndex][fieldName][dataIndex] !== 'undefined'
					) {
						eventData[fieldName] = chart.data.datasets[datasetIndex][fieldName][dataIndex];
					}
				});
			}
			return eventData;
		},
		/**
		 * Get default chart options for current chart subtype
		 * basic and tooltip options share the same space
		 *
		 * @param  {Object} chartData
		 * @return {Object} merged options
		 */
		loadBasicOptions: function loadBasicOptions(chartData) {
			this.formatTooltipTitles(chartData);
			this.formatTooltipLabels(chartData);
			return this.mergeOptions(
				this.getBasicOptions(chartData),
				this.getDefaultBasicOptions(this.getSubType(), chartData)
			);
		},
		/**
		 * Apply default dataset options (usually datalabels configuration)
		 *
		 * @param {object} chartData from request
		 * @returns {object} chartData
		 */
		loadDatasetOptions: function loadDatasetOptions(chartData) {
			return chartData.datasets.map((dataset, index) => {
				let result = this.mergeOptions(
					dataset,
					this.getDatasetOptions(chartData),
					this.getDefaultDatasetOptions(this.getSubType(), chartData)
				);
				return result;
			});
		},
		/**
		 * Load plugins from configuration
		 * @param  {Object} chartData from the request
		 * @return {Object}
		 */
		loadPlugins: function loadPlugins(chartData) {
			return this.mergeOptionsArray(this.getPlugins(chartData), this.getDefaultPlugins(this.getSubType(), chartData));
		},
		/**
		 * Format tooltip titles to user number format and push this modification to titlesFormatted
		 * it is better to parse tooltips at initialization phase than
		 * in tooltip callback which is called after hover
		 *
		 * @param {object} data - data from request
		 * @returns {undefined}
		 */
		formatTooltipTitles: function formatTooltipTitles(data) {
			data.datasets.forEach((dataset) => {
				if (typeof dataset.titlesFormatted === 'undefined') {
					dataset.titlesFormatted = [];
					dataset.data.forEach((dataItem, index) => {
						let defaultLabel = data.labels[index];
						if (String(defaultLabel).length > 0 && !isNaN(Number(defaultLabel))) {
							if (
								typeof this.widgetData !== 'undefined' &&
								typeof this.widgetData.valueType !== 'undefined' &&
								this.widgetData.valueType === 'count'
							) {
								defaultLabel = App.Fields.Double.formatToDisplay(defaultLabel, 0);
							} else {
								defaultLabel = App.Fields.Double.formatToDisplay(defaultLabel);
							}
						}
						if (typeof dataset.label !== 'undefined') {
							let label = dataset.label;
							if (String(label).length > 0 && !isNaN(Number(label))) {
								if (
									typeof this.widgetData !== 'undefined' &&
									typeof this.widgetData.valueType !== 'undefined' &&
									this.widgetData.valueType === 'count'
								) {
									label = App.Fields.Double.formatToDisplay(label, 0);
								} else {
									label = App.Fields.Double.formatToDisplay(label);
								}
							}
							defaultLabel += ' (' + label + ')';
						}
						dataset.titlesFormatted.push(defaultLabel);
					});
				}
			});
		},
		/**
		 * Format tooltip titles to user number format and push this modification to titlesFormatted
		 * it is better to parse tooltips at initialization phase than
		 * in tooltip callback which is called after hover
		 *
		 * @param {object} data - data from request
		 * @returns {undefined}
		 */
		formatTooltipLabels: function formatTooltipTitles(data) {
			data.datasets.forEach((dataset) => {
				if (typeof dataset.dataFormatted === 'undefined') {
					dataset.dataFormatted = [];
					dataset.data.forEach((dataItem, index) => {
						let dataFormatted = dataItem;
						if (String(dataItem).length > 0 && !isNaN(Number(dataItem))) {
							if (
								typeof this.widgetData !== 'undefined' &&
								typeof this.widgetData.valueType !== 'undefined' &&
								this.widgetData.valueType === 'count'
							) {
								dataFormatted = App.Fields.Double.formatToDisplay(dataItem, 0);
							} else {
								dataFormatted = App.Fields.Double.formatToDisplay(dataItem);
							}
						}
						dataset.dataFormatted.push(dataFormatted);
					});
				}
			});
		},
		/**
		 * Same as mergeOptionsObject but in array ;)
		 * @param  {Array} to
		 * @param  {Array} fromArray
		 * @return {Array}
		 */
		mergeOptionsArray: function mergeOptionsArray(to, fromArray) {
			if (typeof to !== 'undefined') {
				return to;
			}
			to = [];
			let result = fromArray
				.map((from, index) => {
					if (Array.isArray(from) && !to.hasOwnProperty(key)) {
						return this.mergeOptionsArray(to[index], from);
					}
					if (
						typeof from === 'object' &&
						from !== null &&
						(typeof to[index] === 'undefined' || (typeof to[index] === 'object' && to[index] !== null))
					) {
						return this.mergeOptionsObject(to[index], from);
					}
					return to[index];
				})
				.filter((item) => typeof item !== 'undefined');
			return result;
		},
		/**
		 * Merge options object and do not override existing properties
		 * @param  {Object} to   object to extend
		 * @param  {Object} from copy properties from this object
		 * @return {Object}      mixed properties
		 */
		mergeOptionsObject: function mergeOptionsObject(to, from) {
			if (typeof to === 'undefined') {
				to = {};
			}
			for (let key in from) {
				if (from.hasOwnProperty(key)) {
					if (Array.isArray(from[key])) {
						if (!to.hasOwnProperty(key)) {
							to[key] = this.mergeOptionsArray(undefined, from[key]);
						}
					} else if (
						typeof from[key] === 'object' &&
						from[key] !== null &&
						(!to.hasOwnProperty(key) || (typeof to[key] === 'object' && to[key] !== null && !Array.isArray(to[key])))
					) {
						// if property is an object - merge recursively
						to[key] = this.mergeOptionsObject(to[key], from[key]);
					} else {
						if (!to.hasOwnProperty(key)) {
							to[key] = from[key];
						}
					}
				}
			}
			return to;
		},
		/**
		 * Merge two objects with options and do not override existing properties
		 *
		 * @param  {Object} to
		 * @param  {Array|arguments} fromArray
		 * @return {object}
		 */
		mergeOptions: function mergeOptions(to = {}, ...fromArray) {
			for (let i = 0, len = fromArray.length; i < len; i++) {
				if (typeof fromArray[i] !== 'object' || Array.isArray(fromArray[i])) {
					app.errorLog(
						new Error(
							'Options argument should be an object! Chart subType: ' +
								this.getSubType() +
								' [' +
								fromArray[i].toString() +
								']'
						)
					);
				} else {
					to = this.mergeOptionsObject(to, fromArray[i]);
				}
			}
			return to;
		},
		/**
		 * Placeholder for individual chart type options
		 * If you want to customize default options this is the right place - override this method in your class
		 *
		 * @param {object} chartData
		 * @returns {object} chart options
		 */
		getBasicOptions: function getBasicOptions(chartData) {
			return {
				responsive: true,
				maintainAspectRatio: false
			};
		},
		/**
		 * Placeholder for individual chart type dataset options
		 *
		 * @param  {object} chartData
		 * @return {Object} datalabels configurations
		 */
		getDatasetOptions: function getDatasetOptions(chartData) {
			return {};
		},
		/**
		 * Placeholder for individual chart type plugins
		 * You can add custom plugins for individual charts by overriding this method
		 * see: http://www.chartjs.org/docs/latest/developers/plugins.html
		 *
		 * @param {object} chartData
		 * @returns {Array|undefined} plugins
		 */
		getPlugins: function getPlugins(chartData) {
			// do not return anything - undefined
		},
		/**
		 * Get chart type
		 * We don't wan't to override loadChart method (good practice)
		 * so we can extend some chart type and change its type only to show data in different manner.
		 * Get type is used to set up Chartjs chart type.
		 *
		 * @param {object} chartData
		 * @returns {string}
		 */
		getType: function getType(chartData) {
			return 'bar';
		},
		/**
		 * Get sub type of a chart.
		 * For example 'bar' is main type and barDivided is a subset of bar with little different options.
		 * By default we are using standard type.
		 * GetSubType is used to get properties - it does not set up Chartjs chart type per se (getType is used for this purpose)
		 *
		 * @param {object}  chartData
		 * @returns {string}
		 */
		getSubType: function getSubType(chartData) {
			return this.getType();
		}
	}
);
Vtiger_Widget_Js('YetiForce_Widget_Js', {}, {});
YetiForce_Widget_Js(
	'YetiForce_Bar_Widget_Js',
	{},
	{
		getType: function getType() {
			return 'bar';
		}
	}
);
YetiForce_Bar_Widget_Js(
	'YetiForce_BarStacked_Widget_Js',
	{},
	{
		getSubType() {
			return 'barStacked';
		}
	}
);
YetiForce_Bar_Widget_Js(
	'YetiForce_Horizontal_Widget_Js',
	{},
	{
		getType: function () {
			return 'horizontalBar';
		}
	}
);
YetiForce_Horizontal_Widget_Js(
	'YetiForce_HorizontalStacked_Widget_Js',
	{},
	{
		getType: function () {
			return 'horizontalBar';
		},
		getSubType() {
			return 'horizontalBarStacked';
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_Funnel_Widget_Js',
	{},
	{
		getType: function getType() {
			return 'funnel';
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_Pie_Widget_Js',
	{},
	{
		getType: function getType() {
			return 'pie';
		}
	}
);
YetiForce_Pie_Widget_Js(
	'YetiForce_PieDivided_Widget_Js',
	{},
	{
		getSubType() {
			return 'pieDivided';
		}
	}
);
YetiForce_Pie_Widget_Js(
	'YetiForce_Donut_Widget_Js',
	{},
	{
		getType: function getType() {
			return 'doughnut';
		}
	}
);
YetiForce_Donut_Widget_Js('YetiForce_Axis_Widget_Js', {}, {});
YetiForce_Widget_Js(
	'YetiForce_BarDivided_Widget_Js',
	{},
	{
		getType: function getType() {
			return 'bar';
		},
		getSubType: function getSubType() {
			return 'barDivided';
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_Line_Widget_Js',
	{},
	{
		getType: function getType() {
			return 'line';
		}
	}
);
YetiForce_Line_Widget_Js(
	'YetiForce_LineStacked_Widget_Js',
	{},
	{
		getType() {
			return 'line';
		},
		getSubType() {
			return 'lineStacked';
		}
	}
);
YetiForce_Line_Widget_Js(
	'YetiForce_LinePlain_Widget_Js',
	{},
	{
		getSubType: function getSubType() {
			return 'linePlain';
		}
	}
);
YetiForce_LineStacked_Widget_Js(
	'YetiForce_LinePlainStacked_Widget_Js',
	{},
	{
		getSubType() {
			return 'linePlainStacked';
		}
	}
);
YetiForce_Bar_Widget_Js(
	'YetiForce_TicketsByStatus_Widget_Js',
	{},
	{
		getBasicOptions: function () {
			return {
				legend: {
					display: true
				},
				scales: {
					xAxes: [
						{
							stacked: true
						}
					],
					yAxes: [
						{
							stacked: true
						}
					]
				}
			};
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_Calendar_Widget_Js',
	{},
	{
		calendarView: false,
		calendarCreateView: false,
		fullCalendar: false,
		/**
		 * Register calendar
		 */
		registerCalendar: function () {
			const self = this,
				container = this.getContainer();
			//Default time format
			let userTimeFormat = CONFIG.hourFormat;
			if (userTimeFormat == 24) {
				userTimeFormat = {
					hour: '2-digit',
					minute: '2-digit',
					hour12: false,
					meridiem: false
				};
			} else {
				userTimeFormat = {
					hour: 'numeric',
					minute: '2-digit',
					meridiem: 'short'
				};
			}
			//Default first hour of the day
			let defaultFirstHour = app.getMainParams('startHour');
			let explodedTime = defaultFirstHour.split(':');
			defaultFirstHour = explodedTime['0'];
			let defaultDate = app.getMainParams('defaultDate');
			if (this.paramCache && defaultDate != moment().format('YYYY-MM-DD')) {
				defaultDate = moment(defaultDate).format('D') == 1 ? moment(defaultDate) : moment(defaultDate).add(1, 'M');
			}
			container.find('.js-widget-quick-create').on('click', function (e) {
				App.Components.QuickCreate.createRecord($(this).data('module-name'));
			});
			this.fullCalendar = new FullCalendar.Calendar(this.getCalendarView().get(0), {
				headerToolbar: { left: ' ', center: 'prev title next', right: ' ' },
				initialDate: defaultDate,
				eventTimeFormat: userTimeFormat,
				slotLabelFormat: userTimeFormat,
				scrollTime: defaultFirstHour,
				firstDay: CONFIG.firstDayOfWeekNo,
				initialView: 'dayGridMonth',
				editable: false,
				slotDuration: 15,
				defaultTimedEventDuration: '01:00:00',
				dayMaxEventRows: false,
				allDaySlot: false,
				moreLinkContent: app.vtranslate('JS_MORE'),
				allDayText: app.vtranslate('JS_ALL_DAY'),
				noEventsText: app.vtranslate('JS_NO_RECORDS'),
				viewHint: '$0',
				contentHeight: 'auto',
				buttonText: {
					today: '',
					year: app.vtranslate('JS_YEAR'),
					week: app.vtranslate('JS_WEEK'),
					month: app.vtranslate('JS_MONTH'),
					day: app.vtranslate('JS_DAY'),
					dayGridMonth: app.vtranslate('JS_MONTH'),
					dayGridWeek: app.vtranslate('JS_WEEK'),
					listWeek: app.vtranslate('JS_WEEK'),
					dayGridDay: app.vtranslate('JS_DAY'),
					timeGridDay: app.vtranslate('JS_DAY')
				},
				navLinkHint: (_dateStr, zonedDate) => {
					return App.Fields.Date.dateToUserFormat(zonedDate);
				},
				dayHeaderContent: (arg) => {
					return App.Fields.Date.daysTranslated[arg.date.getDay()];
				},
				titleFormat: (args) => {
					return Calendar_Js.monthFormat[CONFIG.dateFormat]
						.replace('YYYY', args.date['year'])
						.replace('MMMM', App.Fields.Date.fullMonthsTranslated[args.date['month']]);
				},
				dateClick: (args) => {
					let date = moment(args.date).format(CONFIG.dateFormat.toUpperCase());
					App.Components.QuickCreate.createRecord('Calendar', {
						noCache: true,
						data: {
							date_start: date,
							due_date: date
						},
						callbackFunction: function () {
							self.getCalendarView().closest('.dashboardWidget').find('.js-widget-refresh').trigger('click');
						}
					});
				},
				eventClick: function (info) {
					info.jsEvent.preventDefault();
					let url = $(info.el).attr('href');
					if (url !== undefined) {
						let params = [];
						url += '&viewname=' + container.find('select.widgetFilter.customFilter').val();
						const owner = container.find('.widgetFilter.owner option:selected');
						if (owner.val() != 'all') {
							params.push(['assigned_user_id', 'e', owner.val()]);
						}
						if (container.find('.widgetFilterSwitch').length > 0) {
							const status = container.find('.widgetFilterSwitch').data();
							params.push(['activitystatus', 'e', status[container.find('.widgetFilterSwitch').val()]]);
						}
						const date = App.Fields.Date.dateToUserFormat(info.event.start);
						params.push(
							['activitytype', 'e', info.event.extendedProps.activityType],
							['date_start', 'bw', date + ' 00:00:00,' + date + ' 23:59:59']
						);
						url += '&search_params=' + encodeURIComponent(JSON.stringify([params]));
						window.location.href = `${url}`;
					}
				}
			});
			this.fullCalendar.render();
			this.getCalendarView()
				.find('td.fc-day-top')
				.on('mouseenter', function () {
					jQuery('<span class="plus pull-left fas fa-plus"></span>').prependTo($(this));
				})
				.on('mouseleave', function () {
					$(this).find('.plus').remove();
				});
			const switchBtn = container.find('.js-switch--calendar');
			switchBtn.on('change', (e) => {
				const currentTarget = $(e.currentTarget);
				if (typeof currentTarget.data('on-text') !== 'undefined') container.find('.widgetFilterSwitch').val('current');
				else if (typeof currentTarget.data('off-text') !== 'undefined')
					container.find('.widgetFilterSwitch').val('history');
				this.refreshWidget();
			});
		},
		/**
		 * Load calendar data
		 */
		loadCalendarData: function () {
			this.fullCalendar.removeAllEvents();
			const start_date = App.Fields.Date.dateToUserFormat(this.fullCalendar.view.activeStart),
				end_date = App.Fields.Date.dateToUserFormat(this.fullCalendar.view.activeEnd),
				parent = this.getContainer();
			let user = parent.find('.owner').val();
			if (user == 'all') {
				user = '';
			}
			let params = {
				module: 'Calendar',
				action: 'Calendar',
				mode: 'getEvents',
				start: start_date,
				end: end_date,
				user: user,
				widget: true
			};
			if (parent.find('.customFilter').length > 0) {
				params.customFilter = parent.find('.customFilter').val();
			}
			let widgetFilterSwitch = parent.find('.widgetFilterSwitch');
			if (widgetFilterSwitch.length > 0) {
				params.time = widgetFilterSwitch.val();
				let defaultFilter = widgetFilterSwitch.data('default-filter');
				if (defaultFilter !== undefined) {
					params.customFilter = defaultFilter;
				}
			}
			if (this.paramCache) {
				this.setFilterToCache(this.getContainer().find('.js-widget-refresh').data('url'), {
					owner: user,
					customFilter: params.customFilter,
					start: start_date
				});
			}
			AppConnector.request(params).done((events) => {
				this.fullCalendar.addEventSource(events.result);
			});
		},
		/**
		 * Get calendar view container
		 * @returns {jQuery}
		 */
		getCalendarView: function () {
			if (this.calendarView === false) {
				this.calendarView = this.getContainer().find('.js-calendar__container');
			}
			return this.calendarView;
		},
		/**
		 * Update month name
		 */
		getMonthName: function () {
			let month = this.getCalendarView().find('.fc-toolbar h2').text();
			if (month) {
				this.getContainer()
					.find('.headerCalendar .month')
					.html('<h3>' + month + '</h3>');
			}
		},
		/**
		 * Register change view
		 */
		registerChangeView: function () {
			let thisInstance = this;
			let container = this.getContainer();
			container.find('.fc-toolbar').addClass('d-none');
			let month = container.find('.fc-toolbar h2').text();
			if (month) {
				container
					.find('.headerCalendar')
					.removeClass('d-none')
					.find('.month')
					.append('<h3>' + month + '</h3>');
				let button = container.find('.headerCalendar button');
				button.each(function () {
					let tag = jQuery(this).data('type');
					jQuery(this).on('click', function () {
						thisInstance
							.getCalendarView()
							.find('.fc-toolbar .' + tag)
							.trigger('click');
						thisInstance.loadCalendarData();
						thisInstance.getMonthName();
					});
				});
			}
		},
		/** @inheritdoc */
		loadScrollbar: function loadScrollbar() {
			if (this.fullCalendar) {
				this.fullCalendar.updateSize();
			}
			this._super();
		},
		/** @inheritdoc */
		postLoadWidget: function () {
			this.registerCalendar();
			this.loadCalendarData(true);
			this.registerChangeView();
			this.registerFilterChangeEvent();
		},
		/** @inheritdoc */
		refreshWidget: function () {
			let thisInstance = this;
			let refreshContainer = this.getContainer().find('.dashboardWidgetContent');
			refreshContainer.progressIndicator();
			thisInstance.loadCalendarData();
			refreshContainer.progressIndicator({
				mode: 'hide'
			});
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_CalendarActivities_Widget_Js',
	{},
	{
		modalView: false,
		postLoadWidget: function () {
			this._super();
			this.registerActivityChange();
			this.registerListViewButton();
		},
		postRefreshWidget: function () {
			this._super();
			this.registerActivityChange();
		},
		registerActivityChange: function () {
			var thisInstance = this;
			var refreshContainer = this.getContainer().find('.dashboardWidgetContent');
			refreshContainer.find('.changeActivity').on('click', function (e) {
				if (jQuery(e.target).is('a') || thisInstance.modalView) {
					return;
				}
				var url = jQuery(this).data('url');
				if (typeof url !== 'undefined') {
					var callbackFunction = function () {
						thisInstance.modalView = false;
					};
					thisInstance.modalView = true;
					app.showModalWindow(null, url, callbackFunction);
				}
			});
		},

		registerListViewButton: function () {
			const thisInstance = this,
				container = thisInstance.getContainer();
			container.find('.goToListView').on('click', function () {
				let status;
				let activitiesStatus = container.data('name');
				if (activitiesStatus === 'OverdueActivities') {
					status = 'PLL_OVERDUE';
				} else if (activitiesStatus === 'CalendarActivities') {
					status = 'PLL_IN_REALIZATION##PLL_PLANNED';
				} else {
					status = 'PLL_IN_REALIZATION##PLL_PLANNED##PLL_OVERDUE';
				}
				let url = 'index.php?module=Calendar&view=List&viewname=All';
				url += '&search_params=[[';
				let owner = container.find('.widgetFilter.owner option:selected');
				if (owner.val() !== 'all') {
					url += '["assigned_user_id","e","' + owner.val() + '"],';
				}
				url += '["activitystatus","e","' + encodeURIComponent(status) + '"]]]';
				window.location.href = url;
			});
		}
	}
);
YetiForce_CalendarActivities_Widget_Js('YetiForce_CreatedNotMineActivities_Widget_Js', {}, {});
YetiForce_CalendarActivities_Widget_Js('YetiForce_CreatedNotMineOverdueActivities_Widget_Js', {}, {});
YetiForce_CalendarActivities_Widget_Js('YetiForce_OverDueActivities_Widget_Js', {}, {});
YetiForce_CalendarActivities_Widget_Js('YetiForce_OverdueActivities_Widget_Js', {}, {});
YetiForce_Widget_Js(
	'YetiForce_ProductsSoldToRenew_Widget_Js',
	{},
	{
		modalView: false,
		postLoadWidget: function () {
			this._super();
			this.registerAction();
			this.registerListViewButton();
		},
		postRefreshWidget: function () {
			this._super();
			this.registerAction();
		},
		registerAction: function () {
			var thisInstance = this;
			var refreshContainer = this.getContainer().find('.dashboardWidgetContent');
			refreshContainer.find('.rowAction').on('click', function (e) {
				if (jQuery(e.target).is('a') || thisInstance.modalView) {
					return;
				}
				var url = jQuery(this).data('url');
				if (typeof url !== 'undefined') {
					var callbackFunction = function () {
						thisInstance.modalView = false;
					};
					thisInstance.modalView = true;
					app.showModalWindow(null, url, callbackFunction);
				}
			});
		},
		registerListViewButton: function () {
			var thisInstance = this;
			var container = thisInstance.getContainer();
			container.on('click', '.goToListView', function () {
				var url = jQuery(this).data('url');
				var orderBy = container.find('.orderby');
				var sortOrder = container.find('.changeRecordSort');
				if (orderBy.length) {
					url += '&orderby=' + orderBy.val();
				}
				if (sortOrder.length) {
					url += '&sortorder=' + sortOrder.data('sort').toUpperCase();
				}
				window.location.href = url;
			});
		}
	}
);
YetiForce_ProductsSoldToRenew_Widget_Js('YetiForce_ServicesSoldToRenew_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js(
	'YetiForce_AllTimeControl_Widget_Js',
	{},
	{
		getBasicOptions: function getBasicOptions() {
			return {
				legend: {
					display: true
				},
				scales: {
					yAxes: [
						{
							stacked: true,
							ticks: {
								callback: function formatYAxisTick(value, index, values) {
									return app.formatToHourText(value, 'short', false, false);
								}
							}
						}
					],
					xAxes: [
						{
							stacked: true,
							ticks: {
								minRotation: 0
							}
						}
					]
				},
				tooltips: {
					callbacks: {
						label: function (tooltipItem, data) {
							return (
								data.datasets[tooltipItem.datasetIndex].original_label +
								': ' +
								data.datasets[tooltipItem.datasetIndex].dataFormatted[tooltipItem.index]
							);
						},
						title: function (tooltipItems, data) {
							return data.fullLabels[tooltipItems[0].index];
						}
					}
				}
			};
		},
		getDatasetOptions: function getDatasetOptions(dataset, type, datasetIndex) {
			return {
				datalabels: {
					formatter: function datalabelsFormatter(value, context) {
						return context.dataset.dataFormatted[context.dataIndex];
					}
				}
			};
		}
	}
);
YetiForce_Bar_Widget_Js('YetiForce_LeadsBySource_Widget_Js', {}, {});
YetiForce_Pie_Widget_Js('YetiForce_ClosedTicketsByPriority_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_ClosedTicketsByUser_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_OpenTickets_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_AccountsByIndustry_Widget_Js', {}, {});
YetiForce_Funnel_Widget_Js(
	'YetiForce_EstimatedvalueByStatus_Widget_Js',
	{},
	{
		getBasicOptions: function getBasicOptions() {
			return {
				sort: 'data-desc'
			};
		},
		getPlugins: function getPlugins() {
			return [];
		}
	}
);
YetiForce_Bar_Widget_Js('YetiForce_NotificationsBySender_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_NotificationsByRecipient_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js(
	'YetiForce_TeamsEstimatedSales_Widget_Js',
	{},
	{
		generateChartData: function () {
			const thisInstance = this,
				container = this.getContainer(),
				jData = container.find('.widgetData').val(),
				data = JSON.parse(jData);
			let chartData = [[], [], [], []],
				yMaxValue,
				index,
				parseData;
			if (data.hasOwnProperty('compare')) {
				for (index in data) {
					parseData = thisInstance.parseChartData(data[index], chartData);
					chartData[0].push(parseData[0]);
					chartData[3].push(parseData[3]);
					chartData = [chartData[0], parseData[1], parseData[2], chartData[3], ['#CC6600', '#208CB3']];
				}
			} else {
				parseData = thisInstance.parseChartData(data, chartData);
				chartData = [[parseData[0]], parseData[1], parseData[2], [parseData[3]], ['#208CB3']];
			}
			yMaxValue = chartData[1];
			yMaxValue = yMaxValue + 2 + (yMaxValue / 100) * 25;
			return {
				chartData: chartData[0],
				yMaxValue: yMaxValue,
				labels: chartData[2],
				data_labels: chartData[3],
				placement: 'inside',
				location: 'n',
				colors: chartData[4]
			};
		},
		parseChartData: function (data, chartDataGlobal) {
			var chartData = [];
			var xLabels = [];
			var sum = 0;
			for (var index in data) {
				var row = data[index];
				row[0] = parseInt(row[0]);
				sum += row[0];
				xLabels.push(app.getDecodedValue(row[1]));
				chartData.push(row[0]);
				if (parseInt(row[0]) > chartDataGlobal[1]) {
					chartDataGlobal[1] = parseInt(row[0]);
				}
			}
			return [chartData, chartDataGlobal[1], xLabels, '&nbsp; \u03A3 ' + sum + '&nbsp;'];
		},
		registerSectionClick: function () {
			const container = this.getContainer(),
				data = container.find('.widgetData').val(),
				dataInfo = JSON.parse(data),
				compare = dataInfo && dataInfo.hasOwnProperty('compare');
			let url;
			this.getContainer()
				.off('jqplotDataClick')
				.on('jqplotDataClick', function (ev, seriesIndex, pointIndex, args) {
					if (seriesIndex) {
						url = dataInfo['compare'][pointIndex][2];
					} else if (compare) {
						url = dataInfo[0][pointIndex][2];
					} else {
						url = dataInfo[pointIndex][2];
					}
					window.location.href = url;
				});
		}
	}
);
YetiForce_TeamsEstimatedSales_Widget_Js('YetiForce_ActualSalesOfTeam_Widget_Js', {}, {});
YetiForce_Widget_Js(
	'YetiForce_History_Widget_Js',
	{},
	{
		postLoadWidget: function () {
			this._super();
			this.registerLoadMore();
		},
		postRefreshWidget: function () {
			this._super();
			this.registerLoadMore();
		},
		registerLoadMore: function () {
			var thisInstance = this;
			var parent = thisInstance.getContainer();
			var contentContainer = parent.find('.dashboardWidgetContent');
			var loadMoreHandler = contentContainer.find('.load-more');
			loadMoreHandler.on('click', function () {
				var parent = thisInstance.getContainer();
				var element = parent.find('.js-widget-refresh');
				var url = element.data('url');
				var params = url;
				var widgetFilters = parent.find('.widgetFilter');
				if (widgetFilters.length > 0) {
					params = {
						url: url,
						data: {}
					};
					widgetFilters.each(function (index, domElement) {
						var widgetFilter = jQuery(domElement);
						var filterName = widgetFilter.attr('name');
						var filterValue = widgetFilter.val();
						params.data[filterName] = filterValue;
					});
				}

				var filterData = thisInstance.getFilterData();
				if (!jQuery.isEmptyObject(filterData)) {
					if (typeof params == 'string') {
						params = {
							url: url,
							data: {}
						};
					}
					params.data = jQuery.extend(params.data, thisInstance.getFilterData());
				}

				// Next page.
				params.data['page'] = loadMoreHandler.data('nextpage');
				var refreshContainer = parent.find('.dashboardWidgetContent');
				refreshContainer.progressIndicator();
				AppConnector.request(params)
					.done(function (data) {
						refreshContainer.progressIndicator({
							mode: 'hide'
						});
						loadMoreHandler.replaceWith(data);
						thisInstance.registerLoadMore();
					})
					.fail(function () {
						refreshContainer.progressIndicator({
							mode: 'hide'
						});
					});
			});
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_MiniList_Widget_Js',
	{},
	{
		postLoadWidget: function () {
			this.restrictContentDrag();
			this.registerFilter();
			this.registerFilterChangeEvent();
			this.registerRecordsCount();
		},
		postRefreshWidget: function () {
			this.registerRecordsCount();
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_UpcomingEvents_Widget_Js',
	{},
	{
		postLoadWidget: function () {
			this.registerFilterChangeEvent();
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_Notebook_Widget_Js',
	{},
	{
		// Override widget specific functions.
		postLoadWidget: function () {
			this.registerNotebookEvents();
		},
		registerNotebookEvents: function () {
			this.container.on('click', '.dashboard_notebookWidget_edit', () => {
				this.editNotebookContent();
			});
			this.container.on('click', '.dashboard_notebookWidget_save', () => {
				this.saveNotebookContent();
			});
		},
		editNotebookContent: function () {
			$('.dashboard_notebookWidget_view', this.container).hide();
			let editContainer = $('.dashboard_notebookWidget_text', this.container).show();
			let editTextArea = editContainer.find('textarea');
			editTextArea.css(
				'height',
				this.container.innerHeight() -
					this.container.find('.dashboardWidgetHeader').innerHeight() -
					editTextArea.prev().innerHeight() -
					16
			);
		},
		saveNotebookContent: function () {
			let textarea = $('.dashboard_notebookWidget_textarea', this.container),
				url = this.container.data('url'),
				params = url + '&content=true&mode=save&contents=' + encodeURIComponent(textarea.val()),
				refreshContainer = this.container.find('.dashboardWidgetContent');
			refreshContainer.progressIndicator();
			AppConnector.request(params).done((data) => {
				refreshContainer.progressIndicator({
					mode: 'hide'
				});
				$('.dashboardWidgetContent', this.container).html(data);
			});
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_KpiBar_Widget_Js',
	{},
	{
		generateChartData: function () {
			var container = this.getContainer();
			var jData = container.find('.widgetData').val();
			var data = JSON.parse(jData);
			var chartData = [];
			var xLabels = [];
			var yMaxValue = 0;
			return {
				chartData: [[[data['result'], data['all']]]],
				yMaxValue: data['maxValue'],
				labels: ''
			};
		},
		loadChart: function () {
			var data = this.generateChartData();
			this.getChartContainer(false).jqplot(data['chartData'], {
				animate: !$.jqplot.use_excanvas,
				seriesDefaults: {
					renderer: jQuery.jqplot.BarRenderer,
					rendererOptions: {
						showDataLabels: true,
						dataLabels: 'value',
						barDirection: 'horizontal'
					}
				},
				axes: {
					xaxis: {
						min: 0,
						max: data['yMaxValue']
					},
					yaxis: {
						renderer: jQuery.jqplot.CategoryAxisRenderer
					}
				}
			});
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_ChartFilter_Widget_Js',
	{},
	{
		chartfilterInstance: false,
		init: function (container, reload, widgetClassName) {
			this.setContainer(jQuery(container));
			let chartClassName = container.find('[name="typeChart"]').val();
			const stacked = !!Number(container.find('[name="stacked"]').val());
			if (stacked) {
				chartClassName += 'Stacked';
			}
			this.chartfilterInstance = YetiForce_Widget_Js.getInstance(container, chartClassName);
			if (this.chartfilterInstance) {
				const filterIdsStr = container.find('[name="filterIds"]').val();
				if (filterIdsStr) {
					this.chartfilterInstance.filterIds = JSON.parse(filterIdsStr);
				}
			}
			this.registerRecordsCount();
			this.registerCache(container);
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_Multifilter_Widget_Js',
	{},
	{
		multifilterControlsView: false,
		multifilterContentView: false,
		multifilterSettingsView: false,
		registerSubmit() {
			this.getContainer()
				.find('.js-multifilter-save')
				.on('click', (e) => {
					let progressIndicatorElement = $.progressIndicator({
						position: 'html',
						blockInfo: {
							enabled: true
						}
					});
					let widgetId = this.getMultifilterControls().attr('data-widgetid');
					let actions = this.getContainer().find('.js-select').val();
					AppConnector.request({
						action: 'Widget',
						mode: 'updateWidgetConfig',
						module: app.getModuleName(),
						widgetid: widgetId,
						widgetData: { customMultiFilter: actions }
					}).done((_) => {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						this.refreshWidget();
					});
				});
		},
		loadData() {
			let widgetId = this.getMultifilterControls().attr('data-widgetid'),
				multifilterIds = this.getMultifilterSettings().find('.js-select option:selected'),
				params = [];
			this.getMultifilterContent().html('');
			$.each(multifilterIds, (i, e) => {
				let element = $(e);
				let existFilter = this.getMultifilterContent().find('[data-id="' + element.val() + '"]');
				if (0 < existFilter.length) {
					return true;
				}
				params.push({
					module: element.data('module'),
					modulename: element.data('module'),
					view: 'ShowWidget',
					name: 'Multifilter',
					content: true,
					widget: true,
					widgetid: widgetId,
					filterid: element.val()
				});
			});
			this.loadListData(params);
		},
		loadListData(params) {
			if (!params.length) {
				return false;
			}
			const self = this;
			let multiFilterContent = self.getMultifilterContent();
			let param = params.shift();
			AppConnector.request(param)
				.done(function (data) {
					if (
						self
							.getMultifilterSettings()
							.find('option[value="' + param.filterid + '"]')
							.is(':selected') &&
						!multiFilterContent.find('.detailViewTable[data-id="' + param.filterid + '"]').length
					) {
						self.registerRecordsCount(multiFilterContent.append(data).children('div:last-child'));
						self.registerShowHideBlocks();
						self.loadListData(params);
					}
				})
				.fail(function (error) {
					app.errorLog(error);
					self.loadListData(params);
				});
		},
		registerShowHideModuleSettings() {
			this.getMultifilterControls()
				.find('.js-widget-settings')
				.on('click', () => {
					this.getMultifilterSettings().toggleClass('d-none');
				});
		},
		registerShowHideBlocks() {
			let detailContentsHolder = this.getMultifilterContent();
			detailContentsHolder.find('.blockHeader').off('click');
			detailContentsHolder.find('.blockHeader').click(function () {
				let currentTarget = $(this).find('.js-block-toggle').not('.d-none'),
					closestBlock = currentTarget.closest('.js-toggle-panel'),
					bodyContents = closestBlock.find('.blockContent'),
					data = currentTarget.data();
				let hideHandler = function () {
					bodyContents.addClass('d-none');
				};
				let showHandler = function () {
					bodyContents.removeClass('d-none');
				};
				if ('show' == data.mode) {
					hideHandler();
					currentTarget.addClass('d-none');
					closestBlock.find('[data-mode="hide"]').removeClass('d-none');
				} else {
					showHandler();
					currentTarget.addClass('d-none');
					closestBlock.find("[data-mode='show']").removeClass('d-none');
				}
			});
		},
		registerRecordsCount(container) {
			let url = container.data('url');
			AppConnector.request(url).done(function (data) {
				container.find('.js-count').html(data.result.totalCount);
			});
		},
		getMultifilterControls() {
			if (this.multifilterControlsView == false) {
				this.multifilterControlsView = this.getContainer().find('.js-multifilterControls');
			}
			return this.multifilterControlsView;
		},
		getMultifilterContent() {
			if (this.multifilterContentView == false) {
				this.multifilterContentView = this.getContainer().find('.js-multifilterContent');
			}
			return this.multifilterContentView;
		},
		getMultifilterSettings() {
			if (this.multifilterSettingsView == false) {
				this.multifilterSettingsView = this.getContainer().find('.js-settings-widget');
			}
			return this.multifilterSettingsView;
		},
		postLoadWidget() {
			this.loadData();
			this.registerSubmit();
			this.registerShowHideModuleSettings();
		},
		refreshWidget() {
			this.loadData();
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_UpcomingProjectTasks_Widget_Js',
	{},
	{
		postLoadWidget: function () {
			this._super();
			this.registerListViewButton();
		},
		registerListViewButton: function () {
			const container = this.getContainer();
			container.find('.goToListView').on('click', function () {
				let url = 'index.php?module=ProjectTask&view=List&viewname=All';
				url += '&search_params=[[';
				let owner = container.find('.widgetFilter.owner option:selected');
				if (owner.val() !== 'all') {
					url += '["assigned_user_id","e","' + owner.val() + '"],';
				}
				url +=
					'["projecttaskstatus","e","' + encodeURIComponent(container.find('[name="status"]').data('value')) + '"]]]';
				app.openUrl(url);
			});
		}
	}
);
YetiForce_UpcomingProjectTasks_Widget_Js('YetiForce_CompletedProjectTasks_Widget_Js', {}, {});
YetiForce_Widget_Js(
	'YetiForce_Updates_Widget_Js',
	{},
	{
		postLoadWidget: function () {
			this._super();
			this.registerEvents();
			this.registerLoadMore();
		},
		postRefreshWidget: function () {
			this._super();
			this.registerContentEvents(this.getContainer());
			app.registerPopoverEllipsisIcon(this.getContainer().find('.js-popover-tooltip--ellipsis-icon'));
		},
		registerEvents: function () {
			const container = this.getContainer();
			const self = this;
			let modalContainer = container.find('.js-update-widget-modal');
			app.registerPopoverEllipsisIcon(container.find('.js-popover-tooltip--ellipsis-icon'));
			container.find('.js-update-widget-button').on('click', function () {
				let modal = modalContainer.clone(true);
				let widgetData = JSON.parse(container.find('.js-widget-data').val());
				if (widgetData) {
					for (let i in widgetData.actions) {
						modal.find('.js-tracker-action[value="' + widgetData.actions[i] + '"]').prop('checked', true);
					}
					modal.find('[name="owner"]').val(widgetData.owner);
					modal.find('[name="historyOwner"]').val(widgetData.historyOwner);
				}
				App.Fields.Picklist.showSelect2ElementView(modal.find('select'));
				app.showModalWindow(modal, function (data) {
					self.registerSubmit(data);
				});
			});
			this.registerContentEvents(container);
		},
		registerSubmit(data) {
			data.find('.js-modal__save').on('click', (e) => {
				let progressIndicatorElement = $.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				let actions = [];
				$.each(data.find('.js-tracker-action:checked'), function () {
					actions.push($(this).val());
				});
				AppConnector.request({
					action: 'Widget',
					mode: 'saveUpdatesWidgetConfig',
					module: 'ModTracker',
					widgetId: this.getContainer().find('.js-widget-id').val(),
					trackerActions: actions,
					owner: data.find('[name="owner"]').val(),
					historyOwner: data.find('[name="historyOwner"]').val()
				}).done((data) => {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					this.refreshWidget();
					app.hideModalWindow();
				});
			});
		},
		registerContentEvents() {
			const container = this.getContainer();
			$('.js-history-detail', container).on('click', (e) => {
				let actionId = e.currentTarget.dataset.action;
				let widgetData = JSON.parse(container.find('.js-widget-data').val());
				let progressIndicatorElement = $.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				let params = {
					view: 'UpdatesDetail',
					module: 'ModTracker',
					widgetId: this.getContainer().find('.js-widget-id').val(),
					trackerAction: e.currentTarget.dataset.action,
					sourceModule: e.currentTarget.dataset.module,
					owner: widgetData.owner,
					historyOwner: widgetData.historyOwner,
					dateRange: container.find('[name="dateRange"]').val(),
					page: 1
				};
				AppConnector.request(params)
					.done((modal) => {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						app.showModalWindow(modal, function (data) {
							data.on('click', '.showMoreHistory', (e) => {
								AppConnector.request(e.currentTarget.dataset.url).done((result) => {
									$(e.target).parent().remove();
									data.find('.modal-body').append($(result).filter('.modal-body').get(0).childNodes);
								});
							});
						});
					})
					.fail((error) => {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						app.errorLog(error);
					});
			});
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_TimeCounter_Widget_Js',
	{},
	{
		/** @type {number} Hours of the timer */
		hr: 0,
		/** @type {number} Timer minutes */
		min: 0,
		/** @type {number} Seconds of the timer */
		sec: 0,
		/** @type {boolean} Starting a timer */
		counter: true,
		/** @type {(string|number)} Time to start work */
		timeStart: '',
		/** @type {(string|number)} End of work time */
		timeStop: '',
		/**
		 * Show quick create form
		 */
		postLoadWidget: function () {
			this._super();
			this.registerNavigatorButtons();
		},
		/**
		 * Register events on the navigation buttons.
		 */
		registerNavigatorButtons: function () {
			const container = this.getContainer();
			let btnStart = container.find('.js-time-counter-start');
			let btnStop = container.find('.js-time-counter-stop');
			let btnReset = container.find('.js-time-counter-reset');
			let navigatorButtons = container.find('.js-navigator-buttons');
			let btnMinutes = container.find('.js-time-counter-minute');
			btnStart.on('click', () => {
				navigatorButtons.addClass('active');
				btnStart.addClass('d-none');
				btnStop.removeClass('d-none');
				btnReset.removeClass('d-none');
				btnMinutes.attr('disabled', true);
				btnMinutes.removeClass('btn-outline-success');
				btnMinutes.addClass('btn-outline-danger');
				this.startTimerCounter();
			});
			btnStop.on('click', () => {
				this.stopTimerCounter(false);
			});
			btnReset.on('click', () => {
				navigatorButtons.removeClass('active');
				btnReset.addClass('d-none');
				btnStop.addClass('d-none');
				btnStart.removeClass('d-none');
				btnMinutes.attr('disabled', false);
				btnMinutes.removeClass('btn-outline-danger');
				btnMinutes.addClass('btn-outline-success');
				this.resetTimerCounter();
			});
			if (btnMinutes.length > 1) {
				btnMinutes.on('click', (e) => {
					this.counter = false;
					let element = $(e.currentTarget);
					this.min = element.data('value');
					let dateEnd = new Date();
					let hours = (dateEnd.getHours() < 10 ? '0' : '') + dateEnd.getHours();
					let minutes = (dateEnd.getMinutes() < 10 ? '0' : '') + dateEnd.getMinutes();
					this.timeStop = hours + ':' + minutes;
					this.stopTimerCounter(true);
				});
			}
		},
		/**
		 * Time counting starts
		 */
		startTimerCounter: function () {
			let dateStart = new Date();
			let hours = (dateStart.getHours() < 10 ? '0' : '') + dateStart.getHours();
			let minutes = (dateStart.getMinutes() < 10 ? '0' : '') + dateStart.getMinutes();
			this.timeStart = hours + ':' + minutes;
			if (this.counter === true) {
				this.counter = false;
				this.timeCounter();
			}
		},
		/**
		 * Time counting ends.
		 * @param {boolean} $afterTime
		 */
		stopTimerCounter: function ($afterTime) {
			if (this.counter === false) {
				this.counter = true;
				let quickCreateParams = {};
				let customParams = {};
				if ($afterTime) {
					this.setStopAfterTime();
				} else {
					this.setStopBeforeTime();
				}
				customParams['time_start'] = this.timeStart;
				customParams['time_end'] = this.timeStop;
				quickCreateParams['data'] = customParams;
				quickCreateParams['noCache'] = true;
				App.Components.QuickCreate.createRecord('OSSTimeControl', quickCreateParams);
			}
		},
		/**
		 * Sets the end time before ending the call.
		 */
		setStopBeforeTime: function () {
			if (parseInt(this.sec) > 30 || parseInt(this.min) === 0) {
				this.min = parseInt(this.min) + 1;
			}
			let dateStart = new Date();
			dateStart.setHours(this.timeStart.split(':')[0]);
			dateStart.setMinutes(this.timeStart.split(':')[1]);
			dateStart.setHours(dateStart.getHours() + parseInt(this.hr));
			dateStart.setMinutes(dateStart.getMinutes() + parseInt(this.min));
			let hours = (dateStart.getHours() < 10 ? '0' : '') + dateStart.getHours();
			let minutes = (dateStart.getMinutes() < 10 ? '0' : '') + dateStart.getMinutes();
			this.timeStop = hours + ':' + minutes;
			this.sec = 0;
			this.min = 0;
			this.hr = 0;
		},

		/**
		 * Sets the end time after ending the call.
		 */
		setStopAfterTime: function () {
			let dateEnd = new Date();
			dateEnd.setHours(this.timeStop.split(':')[0]);
			dateEnd.setMinutes(this.timeStop.split(':')[1]);
			dateEnd.setHours(dateEnd.getHours() - parseInt(this.hr));
			dateEnd.setMinutes(dateEnd.getMinutes() - parseInt(this.min));
			let hours = (dateEnd.getHours() < 10 ? '0' : '') + dateEnd.getHours();
			let minutes = (dateEnd.getMinutes() < 10 ? '0' : '') + dateEnd.getMinutes();
			this.timeStart = hours + ':' + minutes;
			this.sec = 0;
			this.min = 0;
			this.hr = 0;
		},
		/**
		 * Resets the counting operation.
		 */
		resetTimerCounter: function () {
			if (this.counter === false) {
				this.counter = true;
				this.sec = 0;
				this.min = 0;
				this.hr = 0;
			}
			this.getContainer().find('.js-time-counter').html('00:00:00');
		},
		/**
		 * Counting time from the moment of starting work.
		 */
		timeCounter: function () {
			if (this.counter === false) {
				this.sec = parseInt(this.sec);
				this.min = parseInt(this.min);
				this.hr = parseInt(this.hr);
				this.sec = this.sec + 1;
				if (this.sec === 60) {
					this.min = this.min + 1;
					this.sec = 0;
				}
				if (this.min === 60) {
					this.hr = this.hr + 1;
					this.min = 0;
					this.sec = 0;
				}
				if (this.sec < 10 || this.sec === 0) {
					this.sec = '0' + this.sec;
				}
				if (this.min < 10 || this.min === 0) {
					this.min = '0' + this.min;
				}
				if (this.hr < 10 || this.hr === 0) {
					this.hr = '0' + this.hr;
				}
				this.getContainer()
					.find('.js-time-counter')
					.html(this.hr + ':' + this.min + ':' + this.sec);
				setTimeout((_) => {
					this.timeCounter();
				}, 1000);
			}
		}
	}
);
