/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
'use strict';

jQuery.Class('Vtiger_Widget_Js', {
	widgetPostLoadEvent: 'Vtiget.Dashboard.PostLoad',
	widgetPostRefereshEvent: 'Vtiger.Dashboard.PostRefresh',
	getInstance: function getInstance(container, widgetClassName, moduleName) {
		if (typeof moduleName === "undefined") {
			moduleName = app.getModuleName();
		}
		const moduleClass = window[moduleName + "_" + widgetClassName + "_Widget_Js"];
		const fallbackClass = window["Vtiger_" + widgetClassName + "_Widget_Js"];
		const yetiClass = window["YetiForce_" + widgetClassName + "_Widget_Js"];
		const basicClass = YetiForce_Widget_Js;
		let instance;
		if (typeof moduleClass !== "undefined") {
			instance = new moduleClass(container, false, widgetClassName);
		} else if (typeof fallbackClass !== "undefined") {
			instance = new fallbackClass(container, false, widgetClassName);
		} else if (typeof yetiClass !== "undefined") {
			instance = new yetiClass(container, false, widgetClassName);
		} else {
			instance = new basicClass(container, false, widgetClassName);
		}
		return instance;
	}
}, {
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
		if (typeof this.filterIds !== "undefined") {
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
			return this.widgetData = JSON.parse(widgetDataEl.val());
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
			},
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
				if (typeof this.widgetData !== 'undefined' && typeof this.widgetData.valueType !== 'undefined' && this.widgetData.valueType === 'count') {
					return App.Fields.Double.formatToDisplay(value, 0);
				}
				if (
					typeof context.chart.data.datasets[context.datasetIndex].dataFormatted !== "undefined" &&
					typeof context.chart.data.datasets[context.datasetIndex].dataFormatted[context.dataIndex] !== "undefined"
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
				if (typeof data.datasets[tooltipItem.datasetIndex].dataFormatted !== "undefined" && data.datasets[tooltipItem.datasetIndex].dataFormatted[tooltipItem.index] !== "undefined") {
					return data.datasets[tooltipItem.datasetIndex].dataFormatted[tooltipItem.index];
				}
				// if there is no formatted data so try to format it
				if (String(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]).length > 0 && !isNaN(Number(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]))) {
					if (typeof this.widgetData !== 'undefined' && typeof this.widgetData.valueType !== 'undefined' && this.widgetData.valueType === 'count') {
						return App.Fields.Double.formatToDisplay(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index], 0);
					}
					return App.Fields.Double.formatToDisplay(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]);
				}
				// return raw data at idex
				return data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
			},
			title: function tooltipTitleCallback(tooltipItems, data) {
				const tooltipItem = tooltipItems[0];
				// get already formatted title if exists
				if (typeof data.datasets[tooltipItem.datasetIndex].titlesFormatted !== "undefined" && data.datasets[tooltipItem.datasetIndex].titlesFormatted[tooltipItem.index] !== "undefined") {
					return data.datasets[tooltipItem.datasetIndex].titlesFormatted[tooltipItem.index];
				}
				// if there is no formatted title so try to format it
				if (String(data.labels[tooltipItem.index]).length > 0 && !isNaN(Number(data.labels[tooltipItem.index]))) {
					if (typeof this.widgetData !== 'undefined' && typeof this.widgetData.valueType !== 'undefined' && this.widgetData.valueType === 'count') {
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
				if (typeof Chart.defaults[type] !== "undefined") {
					return Chart.defaults[type].legend.onClick.apply(this.chartInstance, [e, legendItem]);
				}
				return Chart.defaults.global.legend.onClick.apply(this.chartInstance, [e, legendItem]);
			},
			generateLabels(chart) {
				let type = chart.config.type;
				let labels;
				if (typeof Chart.defaults[type] !== "undefined") {
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
					if (typeof data !== "undefined" && typeof data.datasets !== "undefined" && Array.isArray(data.datasets)) {
						for (let i = 0, len = data.datasets.length; i < len; i++) {
							const meta = chart.getDatasetMeta(i);
							if (typeof meta.data !== "undefined" && Array.isArray(meta.data)) {
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
					if (typeof dataset._models === "undefined") {
						dataset._models = {};
					}
					if (typeof dataset.datalabels === "undefined") {
						dataset.datalabels = {};
					}
					if (typeof dataset.datalabels.display === "undefined") {
						dataset.datalabels.display = true;
					}
					for (let iItem = 0, lenItem = metaData.length; iItem < lenItem; iItem++) {
						const dataItem = metaData[iItem];
						if (typeof dataItem.$datalabels !== "undefined" && typeof dataItem.$datalabels._model !== "undefined") {
							let model = dataItem.$datalabels._model;
							if (model !== null && typeof model !== "undefined") {
								dataset._models[iItem] = model;
							} else if (dataset._models[iItem] !== null && typeof dataset._models[iItem] !== "undefined") {
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
								}
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
					if (typeof data !== "undefined" && typeof data.datasets !== "undefined" && Array.isArray(data.datasets)) {
						for (let i = 0, len = data.datasets.length; i < len; i++) {
							const meta = chart.getDatasetMeta(i);
							if (typeof meta.data !== "undefined" && Array.isArray(meta.data)) {
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
					if (typeof dataset._models === "undefined") {
						dataset._models = {};
					}
					if (typeof dataset.datalabels === "undefined") {
						dataset.datalabels = {};
					}
					if (typeof dataset.datalabels.display === "undefined") {
						dataset.datalabels.display = true;
					}
					for (let iItem = 0, lenItem = metaData.length; iItem < lenItem; iItem++) {
						const dataItem = metaData[iItem];
						if (typeof dataItem.$datalabels !== "undefined" && typeof dataItem.$datalabels._model !== "undefined") {
							let model = dataItem.$datalabels._model;
							if (model !== null && typeof model !== "undefined") {
								dataset._models[iItem] = model;
							} else if (dataset._models[iItem] !== null && typeof dataset._models[iItem] !== "undefined") {
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
								}
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
					if (typeof options.scales === "undefined") {
						options.scales = {};
					}
					if (typeof options.scales.xAxes === "undefined") {
						options.scales.xAxes = [{}];
					}
					options.scales.xAxes.forEach((axis) => {
						if (typeof axis.ticks === "undefined") {
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
					if (typeof options.scales === "undefined") {
						options.scales = {};
					}
					if (typeof options.scales.xAxes === "undefined") {
						options.scales.xAxes = [{}];
					}
					options.scales.xAxes.forEach((axis) => {
						if (typeof axis.ticks === "undefined") {
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
								if (typeof metaDataItem._xScale.options.categoryPercentage !== "undefined") {
									// if it is bar chart there is category percentage option that we should use
									categoryWidth *= metaDataItem._xScale.options.categoryPercentage;
								}
								const fullWidth = ctx.measureText(label).width;
								if (categoryWidth < fullWidth) {
									const shortened = label.substr(0, 10) + "...";
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
												metaDataItem._view.width = (metaDataItem._xScale.width / dataset._meta[prop].data.length) * metaDataItem._xScale.options.categoryPercentage * metaDataItem._xScale.options.barPercentage;
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
					if (typeof options.scales === "undefined") {
						options.scales = {};
					}
					if (typeof options.scales.yAxes === "undefined") {
						options.scales.yAxes = [{}];
					}
					options.scales.yAxes.forEach((axis) => {
						if (typeof axis.ticks === "undefined") {
							axis.ticks = {};
						}
						axis.ticks.callback = function yAxisTickCallback(value, index, values) {
							if (value.length > 13) {
								return value.substr(0, 10) + '...';
							}
							return value;
						}
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
												if (typeof metaDataItem._xScale !== "undefined") {
													metaDataItem._view.x = metaDataItem._xScale.getPixelForValue(index, dataIndex);
													metaDataItem._view.base = metaDataItem._xScale.getBasePixel();
													metaDataItem._view.width = (metaDataItem._xScale.width / dataset._meta[prop].data.length) * metaDataItem._xScale.options.categoryPercentage * metaDataItem._xScale.options.barPercentage;
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
			},
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
			app.errorLog(new Error("Function replacement string should look like 'function:path.to.fn' not like '" + replacementStr + "'"));
		}
		let finalFunction = splitted[1].split('.').reduce((previous, current) => {
			return previous[current];
		}, this.globalChartFunctions);
		if (typeof finalFunction !== 'function') {
			app.errorLog(new Error("Global function does not exists: " + splitted[1]));
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
						xAxes: [{
							ticks: {
								autoSkip: false,
								beginAtZero: true,
								maxRotation: 90,
								callback: 'function:scales.formatAxesLabels'
							}
						}],
						yAxes: [{
							ticks: {
								autoSkip: false,
								beginAtZero: true,
								callback: 'function:scales.formatAxesLabels'
							}
						}]
					},
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
						display: 'function:datalabels.display',
					},
				},
				plugins: [{
					beforeDraw: 'function:plugins.fixXAxisLabels',
				}, {
					beforeDraw: 'function:plugins.hideVerticalBarDatalabelsIfNeeded',
				}],
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
						xAxes: [{
							stacked: true,
							ticks: {
								autoSkip: false,
								beginAtZero: true,
								maxRotation: 90,
								callback: 'function:scales.formatAxesLabels',
							}
						}],
						yAxes: [{
							stacked: true,
							ticks: {
								autoSkip: false,
								beginAtZero: true,
								callback: 'function:scales.formatAxesLabels',
							}
						}]
					},
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
						display: 'function:datalabels.display',
					},
				},
				plugins: [{
					beforeDraw: 'function:plugins.fixXAxisLabels',
				}, {
					beforeDraw: 'function:plugins.hideVerticalBarDatalabelsIfNeeded',
				}],
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
						xAxes: [{
							ticks: {
								autoSkip: false,
								beginAtZero: true,
								maxRotation: 90,
								callback: 'function:scales.formatAxesLabels'
							}
						}],
						yAxes: [{
							ticks: {
								autoSkip: false,
								beginAtZero: true,
								callback: 'function:scales.formatAxesLabels'
							}
						}]
					},
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
						display: 'function:datalabels.display',
					},
				},
				plugins: [{
					beforeDraw: 'function:plugins.fixYAxisLabels'
				}, {
					beforeDraw: 'function:plugins.hideHorizontalBarDatalabelsIfNeeded',
				}],
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
						xAxes: [{
							stacked: true,
							ticks: {
								autoSkip: false,
								beginAtZero: true,
								maxRotation: 90,
								callback: 'function:scales.formatAxesLabels'
							}
						}],
						yAxes: [{
							stacked: true,
							ticks: {
								autoSkip: false,
								beginAtZero: true,
								callback: 'function:scales.formatAxesLabels'
							}
						}]
					},
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
						display: 'function:datalabels.display',
					},
				},
				plugins: [{
					beforeDraw: 'function:plugins.fixYAxisLabels'
				}, {
					beforeDraw: 'function:plugins.hideHorizontalBarDatalabelsIfNeeded',
				}],
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
						xAxes: [{
							ticks: {
								autoSkip: false,
								beginAtZero: true,
								maxRotation: 90,
								callback: 'function:scales.formatAxesLabels',
								labelOffset: 0,
							}
						}],
						yAxes: [{
							ticks: {
								autoSkip: false,
								beginAtZero: true,
								callback: 'function:scales.formatAxesLabels'
							}
						}]
					},
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
						display: 'function:datalabels.display',
					},
				},
				plugins: [{
					beforeDraw: 'function:plugins.fixXAxisLabels'
				}],
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
						xAxes: [{
							ticks: {
								autoSkip: false,
								beginAtZero: true,
								maxRotation: 90,
								callback: 'function:scales.formatAxesLabels',
								labelOffset: 0,
							}
						}],
						yAxes: [{
							stacked: true,
							ticks: {
								autoSkip: false,
								beginAtZero: true,
								callback: 'function:scales.formatAxesLabels'
							}
						}]
					},
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
						display: 'function:datalabels.display',
					},
				},
				plugins: [{
					beforeDraw: 'function:plugins.fixXAxisLabels'
				}],
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
						xAxes: [{
							ticks: {
								autoSkip: false,
								beginAtZero: true,
								maxRotation: 90,
								callback: 'function:scales.formatAxesLabels',
								labelOffset: 0,
							}
						}],
						yAxes: [{
							ticks: {
								autoSkip: false,
								beginAtZero: true,
								callback: 'function:scales.formatAxesLabels'
							}
						}]
					},
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
						display: 'function:datalabels.display',
					},
				},
				plugins: [{
					beforeDraw: 'function:plugins.fixXAxisLabels'
				}],
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
						xAxes: [{
							ticks: {
								autoSkip: false,
								beginAtZero: true,
								maxRotation: 90,
								callback: 'function:scales.formatAxesLabels',
								labelOffset: 0,
							}
						}],
						yAxes: [{
							stacked: true,
							ticks: {
								autoSkip: false,
								beginAtZero: true,
								callback: 'function:scales.formatAxesLabels'
							}
						}]
					},
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
						display: 'function:datalabels.display',
					},
				},
				plugins: [{
					beforeDraw: 'function:plugins.fixXAxisLabels'
				}],
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
							generateLabels: 'function:legend.generateLabels',
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
					scales: {},
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
						display: 'function:datalabels.display',
					},
				},
				plugins: [],
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
							generateLabels: 'function:legend.generateLabels',
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
					scales: {},
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
						display: 'function:datalabels.display',
					},
				},
				plugins: [],
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
						yAxes: [{
							display: true,
							beginAtZero: true,
							ticks: {
								callback: 'function:scales.formatAxesLabels'
							}
						}],
					},
				},
				dataset: {
					datalabels: {
						display: false
					}
				},
				plugins: [{
					beforeDraw: 'function:plugins.fixYAxisLabels',
				}],
			},
		};
		if (typeof options[chartSubType] !== "undefined") {
			return options[chartSubType];
		}
		// if divided and standard chart types are equal
		const notStackedChartSubType = this.removeStackedFromName(chartSubType);
		if (typeof options[notStackedChartSubType] !== "undefined") {
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
		if (typeof useCache === "undefined") {
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
				recordsCountBtn.find('.fas').addClass('d-none')
					.attr('aria-hidden', true);
				recordsCountBtn.find('a').removeClass('d-none')
					.attr('aria-hidden', false);
			});
		});
	},
	loadScrollbar: function loadScrollbar() {
		const container = $(this.getChartContainer(false));
		if (typeof container === "undefined") { // if there is no data
			return false;
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
		if (typeof jData === "undefined") {
			return thisInstance.chartData = jData;
		}
		thisInstance.chartData = JSON.parse(jData);
		return thisInstance.chartData;
	},
	positionNoDataMsg: function positionNoDataMsg() {
		var container = this.getContainer();
		var widgetContentsContainer = container.find('.dashboardWidgetContent');
		var noDataMsgHolder = widgetContentsContainer.find('.noDataMsg');
		noDataMsgHolder.position({
			'my': 'center center',
			'at': 'center center',
			'of': widgetContentsContainer
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
	/**
	 * register print image fields (html2canvas)
	 */
	registerPrintAndDownload() {
		$('.js-print--download', this.getContainer()).on('click', (e) => {
			this.downloadHtmlAsImage($(e.target));
		});
		$('.js-print', this.getContainer()).on('click', (e) => {
			this.printHtml($(e.target));
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
		this.registerContentAutoResize();
		this.registerWidgetSwitch();
		this.registerChangeSorting();
		this.registerLoadMore();
		this.registerHeaderButtons();
		this.registerPrintAndDownload();
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
				drefresh = container.find('a[name="drefresh"]'),
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
			const a = $("<a>")
				.attr("href", imgEl.attr('src'))
				.attr("download", header.find('.js-widget__header__title').text() + ".png")
				.appendTo(container);
			a[0].click();
			a.remove();
		});
		container.find('.js-widget-quick-create').on('click', function (e) {
			Vtiger_Header_Js.getInstance().quickCreateModule($(this).data('module-name'));
		});
	},
	registerChangeSorting: function registerChangeSorting() {
		var thisInstance = this;
		var container = this.getContainer();
		thisInstance.setSortingButton(container.find('.changeRecordSort'));
		container.find('.changeRecordSort').on('click', function (e) {
			var drefresh = container.find('a[name="drefresh"]');
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
			var drefresh = dashboardWidgetHeader.find('a[name="drefresh"]');
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
			var drefresh = dashboardWidgetHeader.find('a[name="drefresh"]');
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
		let element = parent.find('a[name="drefresh"]');
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
		if (this.paramCache && (additionalWidgetFilters.length || widgetFilters.length || parent.find('.listSearchContributor'))) {
			thisInstance.setFilterToCache(params.url ? params.url : params, params.data ? params.data : {});
		}
		AppConnector.request(params).done((data) => {
			data = $(data);
			let footer = data.filter('.widgetFooterContent');
			refreshContainer.progressIndicator({
				'mode': 'hide'
			});
			if (footer.length) {
				footer = footer.clone(true, true);
				refreshContainerFooter.html(footer);
				data.each(function (n, e) {
					if (jQuery(this).hasClass('widgetFooterContent')) {
						data.splice(n, 1);
					}
				})
			}
			contentContainer.html(data).trigger(YetiForce_Widget_Js.widgetPostRefereshEvent);
		}).fail(() => {
			refreshContainer.progressIndicator({
				'mode': 'hide'
			});
		});
	},
	registerFilter: function registerFilter() {
		const container = this.getContainer();
		const search = container.find('.listSearchContributor');
		const refreshBtn = container.find('a[name="drefresh"]');
		const originalUrl = refreshBtn.data('url');
		const selects = container.find('.select2noactive');
		search.css('width', '100%');
		search.parent().addClass('w-100');
		search.each((index, element) => {
			const fieldInfo = $(element).data('fieldinfo');
			$(element).attr('placeholder', fieldInfo.label).data('placeholder', fieldInfo.label);
		});
		App.Fields.Picklist.changeSelectElementView(selects, 'select2', {containerCssClass: 'form-control'});
		App.Fields.Date.register(container);
		App.Fields.Date.registerRange(container);
		search.on('change apply.daterangepicker', (e) => {
			let searchParams = [];
			container.find('.listSearchContributor').each((index, domElement) => {
				let searchInfo = [];
				const searchContributorElement = $(domElement);
				const fieldInfo = searchContributorElement.data('fieldinfo');
				const fieldName = searchContributorElement.attr('name');
				let searchValue = searchContributorElement.val();
				if (typeof searchValue === "object") {
					if (searchValue == null) {
						searchValue = "";
					} else {
						searchValue = searchValue.join('##');
					}
				}
				searchValue = searchValue.trim();
				if (searchValue.length <= 0) {
					//continue
					return true;
				}
				let searchOperator = 'a';
				if (fieldInfo.hasOwnProperty("searchOperator")) {
					searchOperator = fieldInfo.searchOperator;
				} else if (jQuery.inArray(fieldInfo.type, ['modules', 'time', 'userCreator', 'owner', 'picklist', 'tree', 'boolean', 'fileLocationType', 'userRole', 'companySelect', 'multiReferenceValue']) >= 0) {
					searchOperator = 'e';
				} else if (fieldInfo.type === "date" || fieldInfo.type === "datetime") {
					searchOperator = 'bw';
				} else if (fieldInfo.type === 'multipicklist' || fieldInfo.type === 'categoryMultipicklist') {
					searchOperator = 'c';
				}
				searchInfo.push(fieldName);
				searchInfo.push(searchOperator);
				searchInfo.push(searchValue);
				if (fieldInfo.type === 'tree' || fieldInfo.type === 'categoryMultipicklist') {
					searchInfo.push($('.listViewHeaders .searchInSubcategories[data-columnname="' + fieldName + '"]').prop('checked'));
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
			container.find('a[name="drefresh"]').trigger('click');
		});
		if (container.find('.widgetFilterByField').length) {
			App.Fields.Picklist.showSelect2ElementView(container.find('.select2noactive'));
			this.getContainer().on('change', '.widgetFilterByField .form-control', (e) => {
				container.find('a[name="drefresh"]').trigger('click');
			});
		}
	},
	registerWidgetPostLoadEvent: function registerWidgetPostLoadEvent(container) {
		var thisInstance = this;
		container.on(YetiForce_Widget_Js.widgetPostLoadEvent, function (e) {
			thisInstance.postLoadWidget();
		})
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
		$(thisInstance.chartInstance.canvas).on('click', function (e) {
			if (typeof thisInstance.getDataFromEvent(e, ['links']).links !== "undefined") {
				window.location.href = thisInstance.getDataFromEvent(e, ['links']).links;
			}
		}).on('mousemove', function (e) {
			if (typeof thisInstance.getDataFromEvent(e, ['links']).links !== "undefined") {
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
		}).on('mouseout', function () {
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
			var user = parent.find('.owner').val();
			var type = parent.find("[name='type']").val();
			var url = element.data('url') + '&content=true&owner=' + user;
			if (parent.find("[name='type']").length > 0) {
				url += '&type=' + type;
			}
			if (parent.find('.changeRecordSort').length > 0) {
				url += '&sortorder=' + parent.find('.changeRecordSort').data('sort');
			}
			contentContainer.progressIndicator();
			AppConnector.request(url).done(function (data) {
				contentContainer.progressIndicator({
					'mode': 'hide'
				});
				jQuery(parent).find('.dashboardWidgetContent').append(data);
				element.parent().remove();
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
		var name = container.data('name');
		app.cacheSet(name + userId, paramCache);
	},
	registerCache: function registerCache(container) {
		if (container.data('cache') == 1) {
			this.paramCache = true;
		}
	},
	/**
	 * Auto resize charts when widget was resized
	 */
	registerContentAutoResize() {
		this.getContainer().closest('.grid-stack').on('gsresizestop', (event, elem) => {
			this.loadScrollbar();
		});
	},
	/**
	 * Load and display chart into the view
	 *
	 * @return {Chart} chartInstance
	 */
	loadChart: function loadChart() {
		if (typeof this.chartData === "undefined" || typeof this.getChartContainer() === "undefined") {
			return false;
		}
		this.getWidgetData();// load widget data for label formatters
		const type = this.getType();
		let data = this.generateData();
		data.datasets = this.loadDatasetOptions(data);
		const options = this.parseOptions(this.loadBasicOptions(data));
		const plugins = this.parseOptions(this.loadPlugins(data));
		data = this.parseOptions(data);
		const chart = this.chartInstance = new Chart(
			this.getChartContainer().getContext("2d"), {
				type,
				data,
				options,
				plugins
			}
		);
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
			value: chart.data.datasets[0].data[dataIndex],
		};
		if (typeof additionalFields !== "undefined" && Array.isArray(additionalFields)) {
			additionalFields.forEach((fieldName) => {
				if (typeof chart.data.datasets[datasetIndex][fieldName] !== "undefined" && typeof chart.data.datasets[datasetIndex][fieldName][dataIndex] !== "undefined") {
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
			this.getDefaultBasicOptions(this.getSubType(), chartData));
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
		return this.mergeOptionsArray(
			this.getPlugins(chartData),
			this.getDefaultPlugins(this.getSubType(), chartData)
		);
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
			if (typeof dataset.titlesFormatted === "undefined") {
				dataset.titlesFormatted = [];
				dataset.data.forEach((dataItem, index) => {
					let defaultLabel = data.labels[index];
					if (String(defaultLabel).length > 0 && !isNaN(Number(defaultLabel))) {
						if (typeof this.widgetData !== 'undefined' && typeof this.widgetData.valueType !== 'undefined' && this.widgetData.valueType === 'count') {
							defaultLabel = App.Fields.Double.formatToDisplay(defaultLabel, 0);
						} else {
							defaultLabel = App.Fields.Double.formatToDisplay(defaultLabel);
						}
					}
					if (typeof dataset.label !== "undefined") {
						let label = dataset.label;
						if (String(label).length > 0 && !isNaN(Number(label))) {
							if (typeof this.widgetData !== 'undefined' && typeof this.widgetData.valueType !== 'undefined' && this.widgetData.valueType === 'count') {
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
			if (typeof dataset.dataFormatted === "undefined") {
				dataset.dataFormatted = [];
				dataset.data.forEach((dataItem, index) => {
					let dataFormatted = dataItem;
					if (String(dataItem).length > 0 && !isNaN(Number(dataItem))) {
						if (typeof this.widgetData !== 'undefined' && typeof this.widgetData.valueType !== 'undefined' && this.widgetData.valueType === 'count') {
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
		if (typeof to !== "undefined") {
			return to;
		}
		to = [];
		let result = fromArray.map((from, index) => {
			if (Array.isArray(from) && !to.hasOwnProperty(key)) {
				return this.mergeOptionsArray(to[index], from);
			}
			if (typeof from === 'object' && from !== null && (typeof to[index] === "undefined" || (typeof to[index] === 'object' && to[index] !== null))) {
				return this.mergeOptionsObject(to[index], from);
			}
			return to[index];
		}).filter((item) => typeof item !== "undefined");
		return result;
	},
	/**
	 * Merge options object and do not override existing properties
	 * @param  {Object} to   object to extend
	 * @param  {Object} from copy properties from this object
	 * @return {Object}      mixed properties
	 */
	mergeOptionsObject: function mergeOptionsObject(to, from) {
		if (typeof to === "undefined") {
			to = {};
		}
		for (let key in from) {
			if (from.hasOwnProperty(key)) {
				if (Array.isArray(from[key])) {
					if (!to.hasOwnProperty(key)) {
						to[key] = this.mergeOptionsArray(undefined, from[key]);
					}
				} else if (typeof from[key] === 'object' && from[key] !== null && (!to.hasOwnProperty(key) || (typeof to[key] === 'object' && to[key] !== null && !Array.isArray(to[key])))) {
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
				app.errorLog(new Error('Options argument should be an object! Chart subType: ' + this.getSubType() + ' [' + fromArray[i].toString() + ']'));

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
			maintainAspectRatio: false,
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
});
Vtiger_Widget_Js('YetiForce_Widget_Js', {}, {});
YetiForce_Widget_Js('YetiForce_Bar_Widget_Js', {}, {
	getType: function getType() {
		return 'bar';
	},
});
YetiForce_Bar_Widget_Js('YetiForce_BarStacked_Widget_Js', {}, {
	getSubType() {
		return 'barStacked';
	}
});
YetiForce_Bar_Widget_Js('YetiForce_Horizontal_Widget_Js', {}, {
	getType: function () {
		return 'horizontalBar';
	},
});
YetiForce_Horizontal_Widget_Js('YetiForce_HorizontalStacked_Widget_Js', {}, {
	getType: function () {
		return 'horizontalBar';
	},
	getSubType() {
		return 'horizontalBarStacked';
	}
});
YetiForce_Widget_Js('YetiForce_Funnel_Widget_Js', {}, {
	getType: function getType() {
		return 'funnel';
	},
});
YetiForce_Widget_Js('YetiForce_Pie_Widget_Js', {}, {
	getType: function getType() {
		return 'pie';
	},
});
YetiForce_Pie_Widget_Js('YetiForce_PieDivided_Widget_Js', {}, {
	getSubType() {
		return 'pieDivided';
	}
});
YetiForce_Pie_Widget_Js('YetiForce_Donut_Widget_Js', {}, {
	getType: function getType() {
		return 'doughnut';
	},
});
YetiForce_Donut_Widget_Js('YetiForce_Axis_Widget_Js', {}, {});
YetiForce_Widget_Js('YetiForce_BarDivided_Widget_Js', {}, {
	getType: function getType() {
		return 'bar';
	},
	getSubType: function getSubType() {
		return 'barDivided';
	}
});
YetiForce_Widget_Js('YetiForce_Line_Widget_Js', {}, {
	getType: function getType() {
		return 'line';
	},
});
YetiForce_Line_Widget_Js('YetiForce_LineStacked_Widget_Js', {}, {
	getType() {
		return 'line';
	},
	getSubType() {
		return 'lineStacked';
	}
});
YetiForce_Line_Widget_Js('YetiForce_LinePlain_Widget_Js', {}, {
	getSubType: function getSubType() {
		return 'linePlain';
	}
});
YetiForce_LineStacked_Widget_Js('YetiForce_LinePlainStacked_Widget_Js', {}, {
	getSubType() {
		return 'linePlainStacked';
	}
});
YetiForce_Bar_Widget_Js('YetiForce_TicketsByStatus_Widget_Js', {}, {
	getBasicOptions: function () {
		return {
			legend: {
				display: true
			},
			scales: {
				xAxes: [{
					stacked: true
				}],
				yAxes: [{
					stacked: true
				}]
			}
		};
	}
});
YetiForce_Widget_Js('YetiForce_Calendar_Widget_Js', {}, {
	calendarView: false,
	calendarCreateView: false,

	registerCalendar: function () {
		var thisInstance = this;
		var userDefaultActivityView = 'month';
		var container = thisInstance.getContainer();
		//Default time format
		var userDefaultTimeFormat = CONFIG.hourFormat;
		if (userDefaultTimeFormat == 24) {
			userDefaultTimeFormat = 'H(:mm)';
		} else {
			userDefaultTimeFormat = 'h(:mm) A';
		}

		//Default first day of the week
		var convertedFirstDay = CONFIG.firstDayOfWeekNo;
		//Default first hour of the day
		var defaultFirstHour = app.getMainParams('startHour');
		var explodedTime = defaultFirstHour.split(':');
		defaultFirstHour = explodedTime['0'];
		var defaultDate = app.getMainParams('defaultDate');
		if (this.paramCache && defaultDate != moment().format('YYYY-MM-DD')) {
			defaultDate = moment(defaultDate).format('D') == 1 ? moment(defaultDate) : moment(defaultDate).add(1, 'M');
		}
		container.find('.js-widget-quick-create').on('click', function (e) {
			Vtiger_Header_Js.getInstance().quickCreateModule($(this).data('module-name'));
		});
		thisInstance.getCalendarView().fullCalendar({
			header: {
				left: ' ',
				center: 'prev title next',
				right: ' '
			},
			defaultDate: defaultDate,
			timeFormat: userDefaultTimeFormat,
			axisFormat: userDefaultTimeFormat,
			firstHour: defaultFirstHour,
			firstDay: convertedFirstDay,
			defaultView: userDefaultActivityView,
			editable: false,
			slotMinutes: 15,
			theme: false,
			defaultEventMinutes: 0,
			eventLimit: true,
			allDaySlot: false,
			monthNames: [app.vtranslate('JS_JANUARY'), app.vtranslate('JS_FEBRUARY'), app.vtranslate('JS_MARCH'),
				app.vtranslate('JS_APRIL'), app.vtranslate('JS_MAY'), app.vtranslate('JS_JUNE'), app.vtranslate('JS_JULY'),
				app.vtranslate('JS_AUGUST'), app.vtranslate('JS_SEPTEMBER'), app.vtranslate('JS_OCTOBER'),
				app.vtranslate('JS_NOVEMBER'), app.vtranslate('JS_DECEMBER')
			],
			monthNamesShort: [app.vtranslate('JS_JAN'), app.vtranslate('JS_FEB'), app.vtranslate('JS_MAR'),
				app.vtranslate('JS_APR'), app.vtranslate('JS_MAY'), app.vtranslate('JS_JUN'), app.vtranslate('JS_JUL'),
				app.vtranslate('JS_AUG'), app.vtranslate('JS_SEP'), app.vtranslate('JS_OCT'), app.vtranslate('JS_NOV'),
				app.vtranslate('JS_DEC')
			],
			dayNames: [app.vtranslate('JS_SUNDAY'), app.vtranslate('JS_MONDAY'), app.vtranslate('JS_TUESDAY'),
				app.vtranslate('JS_WEDNESDAY'), app.vtranslate('JS_THURSDAY'), app.vtranslate('JS_FRIDAY'),
				app.vtranslate('JS_SATURDAY')
			],
			dayNamesShort: [app.vtranslate('JS_SUN'), app.vtranslate('JS_MON'), app.vtranslate('JS_TUE'),
				app.vtranslate('JS_WED'), app.vtranslate('JS_THU'), app.vtranslate('JS_FRI'),
				app.vtranslate('JS_SAT')
			],
			buttonText: {
				today: app.vtranslate('JS_TODAY'),
				month: app.vtranslate('JS_MONTH'),
				week: app.vtranslate('JS_WEEK'),
				day: app.vtranslate('JS_DAY')
			},
			allDayText: app.vtranslate('JS_ALL_DAY'),
			eventLimitText: app.vtranslate('JS_MORE'),
			eventRender: function (event, element, view) {
				element = '<div class="cell-calendar">';
				for (var key in event.event) {
					element += '<a class="" href="javascript:;"' +
						' data-date="' + event.date + '"' + ' data-type="' + key + '" title="' + event.event[key].label + '">' +
						'<span class="' + event.event[key].className + ((event.width <= 20) ? ' small-badge' : '') + ((event.width >= 24) ? ' big-badge' : '') + ' badge badge-secondary u-font-size-95per">' + event.event[key].count + '</span>' +
						'</a>\n';
				}
				element += '</div>';
				return element;
			}
		});
		thisInstance.getCalendarView().find("td.fc-day-top")
			.on('mouseenter', function () {
				jQuery('<span class="plus pull-left fas fa-plus"></span>')
					.prependTo($(this))
			}).on('mouseleave', function () {
			$(this).find(".plus").remove();
		});
		let formatDate = CONFIG.dateFormat.toUpperCase();
		thisInstance.getCalendarView().find("td.fc-day-top").on('click', function () {
			let date = moment($(this).data('date')).format(formatDate);
			let params = {
				noCache: true,
				data: {
					date_start: date,
					due_date: date
				}
			};
			params.callbackFunction = function () {
				thisInstance.getCalendarView().closest('.dashboardWidget').find('a[name="drefresh"]').trigger('click');
			};
			Vtiger_Header_Js.getInstance().quickCreateModule('Calendar', params);
		});
		var switchBtn = container.find('.js-switch--calendar');
		switchBtn.on('change', (e) => {
			const currentTarget = $(e.currentTarget);
			if (typeof currentTarget.data('on-text') !== 'undefined')
				container.find('.widgetFilterSwitch').val('current');
			else if (typeof currentTarget.data('off-text') !== 'undefined')
				container.find('.widgetFilterSwitch').val('history');
			this.refreshWidget();
		})
	},
	loadCalendarData: function (allEvents) {
		var thisInstance = this;
		thisInstance.getCalendarView().fullCalendar('removeEvents');
		var view = thisInstance.getCalendarView().fullCalendar('getView');
		var formatDate = CONFIG.dateFormat.toUpperCase();
		var start_date = view.start.format(formatDate);
		var end_date = view.end.format(formatDate);
		var parent = thisInstance.getContainer();
		var user = parent.find('.owner').val();
		if (user == 'all') {
			user = '';
		}
		var params = {
			module: 'Calendar',
			action: 'Calendar',
			mode: 'getEvents',
			start: start_date,
			end: end_date,
			user: user,
			widget: true
		}
		if (parent.find('.customFilter').length > 0) {
			var customFilter = parent.find('.customFilter').val();
			params.customFilter = customFilter;
		}
		if (parent.find('.widgetFilterSwitch').length > 0) {
			params.time = parent.find('.widgetFilterSwitch').val();
		}
		if (this.paramCache) {
			var drefresh = this.getContainer().find('a[name="drefresh"]');
			var url = drefresh.data('url');
			var paramCache = {
				owner: user,
				customFilter: customFilter,
				start: start_date
			};
			thisInstance.setFilterToCache(url, paramCache);
		}
		AppConnector.request(params).done(function (events) {
			var height = (thisInstance.getCalendarView().find('.fc-bg :first').height() - thisInstance.getCalendarView().find('.fc-day-number').height()) - 10;
			var width = (thisInstance.getCalendarView().find('.fc-day-number').width() / 2) - 10;
			for (var i in events.result) {
				events.result[i]['width'] = width;
				events.result[i]['height'] = height;
			}
			thisInstance.getCalendarView().fullCalendar('addEventSource',
				events.result
			);
			thisInstance.getCalendarView().find(".cell-calendar a").on('click', function () {
				var container = thisInstance.getContainer();
				var url = 'index.php?module=Calendar&view=List';
				if (customFilter) {
					url += '&viewname=' + container.find('select.widgetFilter.customFilter').val();
				} else {
					url += '&viewname=All';
				}
				url += '&search_params=[[';
				var owner = container.find('.widgetFilter.owner option:selected');
				if (owner.val() != 'all') {
					url += '["assigned_user_id","e","' + owner.val() + '"],';
				}
				if (parent.find('.widgetFilterSwitch').length > 0) {
					var status = parent.find('.widgetFilterSwitch').data();
					url += '["activitystatus","e","' + status[params.time] + '"],';
				}
				var date = moment($(this).data('date')).format(thisInstance.getUserDateFormat().toUpperCase())
				window.location.href = url + '["activitytype","e","' + $(this).data('type') + '"],["date_start","bw","' + date + ',' + date + '"]]]';
			});
		});
	},
	getCalendarView: function () {
		if (this.calendarView == false) {
			this.calendarView = this.getContainer().find('.js-calendar__container');
		}
		return this.calendarView;
	},
	getMonthName: function () {
		var thisInstance = this;
		var month = thisInstance.getCalendarView().find('.fc-toolbar h2').text();
		if (month) {
			this.getContainer().find('.headerCalendar .month').html('<h3>' + month + '</h3>');
		}
	},
	registerChangeView: function () {
		var thisInstance = this;
		var container = this.getContainer();
		container.find('.fc-toolbar').addClass('d-none');
		var month = container.find('.fc-toolbar h2').text();
		if (month) {
			container.find('.headerCalendar').removeClass('d-none').find('.month').append('<h3>' + month + '</h3>');
			var button = container.find('.headerCalendar button');
			button.each(function () {
				var tag = jQuery(this).data('type');
				jQuery(this).on('click', function () {
					thisInstance.getCalendarView().find('.fc-toolbar .' + tag).trigger('click');
					thisInstance.loadCalendarData();
					thisInstance.getMonthName();
				})
			})
		}
	},
	postLoadWidget: function () {
		this.registerCalendar();
		this.loadCalendarData(true);
		this.registerChangeView();
		this.registerFilterChangeEvent();
	},
	refreshWidget: function () {
		var thisInstance = this;
		var refreshContainer = this.getContainer().find('.dashboardWidgetContent');
		refreshContainer.progressIndicator();
		thisInstance.loadCalendarData();
		refreshContainer.progressIndicator({
			'mode': 'hide'
		});
	},
});
YetiForce_Widget_Js('YetiForce_CalendarActivities_Widget_Js', {}, {
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
			if (typeof url !== "undefined") {
				var callbackFunction = function () {
					thisInstance.modalView = false;
				};
				thisInstance.modalView = true;
				app.showModalWindow(null, url, callbackFunction);
			}
		})
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
});
YetiForce_CalendarActivities_Widget_Js('YetiForce_AssignedUpcomingCalendarTasks_Widget_Js', {}, {});
YetiForce_CalendarActivities_Widget_Js('YetiForce_CreatedNotMineActivities_Widget_Js', {}, {});
YetiForce_CalendarActivities_Widget_Js('YetiForce_OverDueActivities_Widget_Js', {}, {});
YetiForce_CalendarActivities_Widget_Js('YetiForce_AssignedOverDueCalendarTasks_Widget_Js', {}, {});
YetiForce_CalendarActivities_Widget_Js('YetiForce_OverdueActivities_Widget_Js', {}, {});
YetiForce_Widget_Js('YetiForce_ProductsSoldToRenew_Widget_Js', {}, {
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
			if (typeof url !== "undefined") {
				var callbackFunction = function () {
					thisInstance.modalView = false;
				};
				thisInstance.modalView = true;
				app.showModalWindow(null, url, callbackFunction);
			}
		})
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
});
YetiForce_ProductsSoldToRenew_Widget_Js('YetiForce_ServicesSoldToRenew_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_AllTimeControl_Widget_Js', {}, {
	getBasicOptions: function getBasicOptions() {
		return {
			legend: {
				display: true
			},
			scales: {
				yAxes: [{
					stacked: true,
					ticks: {
						callback: function formatYAxisTick(value, index, values) {
							return app.formatToHourText(value, 'short', false, false);
						}
					}
				}],
				xAxes: [{
					stacked: true,
					ticks: {
						minRotation: 0
					}
				}]
			},
			tooltips: {
				callbacks: {
					label: function (tooltipItem, data) {
						return data.datasets[tooltipItem.datasetIndex].original_label + ': ' + app.formatToHourText(tooltipItem.yLabel);
					},
					title: function (tooltipItems, data) {
						return data.fullLabels[tooltipItems[0].index];
					}
				}
			},
		}
	},
	getDatasetOptions: function getDatasetOptions(dataset, type, datasetIndex) {
		return {
			datalabels: {
				formatter: function datalabelsFormatter(value, context) {
					return app.formatToHourText(value);
				}
			},
		};
	},
});
YetiForce_Bar_Widget_Js('YetiForce_LeadsBySource_Widget_Js', {}, {});
YetiForce_Pie_Widget_Js('YetiForce_ClosedTicketsByPriority_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_ClosedTicketsByUser_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_OpenTickets_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_AccountsByIndustry_Widget_Js', {}, {});
YetiForce_Funnel_Widget_Js('YetiForce_EstimatedvalueByStatus_Widget_Js', {}, {
	getBasicOptions: function getBasicOptions() {
		return {
			sort: 'data-desc'
		};
	},
	getPlugins: function getPlugins() {
		return [];
	}
});
YetiForce_Bar_Widget_Js('YetiForce_NotificationsBySender_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_NotificationsByRecipient_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_TeamsEstimatedSales_Widget_Js', {}, {
	generateChartData: function () {
		const thisInstance = this,
			container = this.getContainer(),
			jData = container.find('.widgetData').val(),
			data = JSON.parse(jData);
		let chartData = [
				[],
				[],
				[],
				[]
			],
			yMaxValue,
			index,
			parseData;
		if (data.hasOwnProperty('compare')) {
			for (index in data) {
				parseData = thisInstance.parseChartData(data[index], chartData);
				chartData[0].push(parseData[0]);
				chartData[3].push(parseData[3]);
				chartData = [chartData[0], parseData[1], parseData[2], chartData[3],
					['#CC6600', '#208CB3']
				];
			}
		} else {
			parseData = thisInstance.parseChartData(data, chartData);
			chartData = [
				[parseData[0]], parseData[1], parseData[2],
				[parseData[3]],
				['#208CB3']
			];
		}
		yMaxValue = chartData[1];
		yMaxValue = yMaxValue + 2 + (yMaxValue / 100) * 25;
		return {
			'chartData': chartData[0],
			'yMaxValue': yMaxValue,
			'labels': chartData[2],
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
			xLabels.push(app.getDecodedValue(row[1]))
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
		this.getContainer().off('jqplotDataClick').on('jqplotDataClick', function (ev, seriesIndex, pointIndex, args) {
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
});
YetiForce_TeamsEstimatedSales_Widget_Js('YetiForce_ActualSalesOfTeam_Widget_Js', {}, {});
YetiForce_Widget_Js('YetiForce_History_Widget_Js', {}, {
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
			var element = parent.find('a[name="drefresh"]');
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
				params.data = jQuery.extend(params.data, thisInstance.getFilterData())
			}

			// Next page.
			params.data['page'] = loadMoreHandler.data('nextpage');
			var refreshContainer = parent.find('.dashboardWidgetContent');
			refreshContainer.progressIndicator();
			AppConnector.request(params).done(function (data) {
				refreshContainer.progressIndicator({
					'mode': 'hide'
				});
				loadMoreHandler.replaceWith(data);
				thisInstance.registerLoadMore();
			}).fail(function () {
				refreshContainer.progressIndicator({
					'mode': 'hide'
				});
			});
		});
	}

});
YetiForce_Widget_Js('YetiForce_MiniList_Widget_Js', {}, {
	postLoadWidget: function () {
		app.hideModalWindow();
		this.restrictContentDrag();
		this.registerFilterChangeEvent();
		this.registerRecordsCount();
	},
	postRefreshWidget: function () {
		this.registerRecordsCount();
	}
});
YetiForce_Widget_Js('YetiForce_Notebook_Widget_Js', {}, {
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
		$('.dashboard_notebookWidget_text', this.container).show();
		$('.dashboard_notebookWidget_view', this.container).hide();
	},
	saveNotebookContent: function () {
		let textarea = $('.dashboard_notebookWidget_textarea', this.container),
			url = this.container.data('url'),
			params = url + '&content=true&mode=save&contents=' + encodeURIComponent(textarea.val()),
			refreshContainer = this.container.find('.dashboardWidgetContent');
		refreshContainer.progressIndicator();
		AppConnector.request(params).done((data) => {
			refreshContainer.progressIndicator({
				'mode': 'hide'
			});
			$('.dashboardWidgetContent', this.container).html(data);
		});
	}
});
YetiForce_Widget_Js('YetiForce_KpiBar_Widget_Js', {}, {
	generateChartData: function () {
		var container = this.getContainer();
		var jData = container.find('.widgetData').val();
		var data = JSON.parse(jData);
		var chartData = [];
		var xLabels = [];
		var yMaxValue = 0;
		return {
			'chartData': [
				[
					[data['result'], data['all']]
				]
			],
			'yMaxValue': data['maxValue'],
			'labels': ''
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
				},
			},
			axes: {
				xaxis: {
					min: 0,
					max: data['yMaxValue'],
				},
				yaxis: {
					renderer: jQuery.jqplot.CategoryAxisRenderer,
				}
			}
		});
	}
});
YetiForce_Widget_Js('YetiForce_ChartFilter_Widget_Js', {}, {
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
	},
});
YetiForce_Widget_Js('YetiForce_Multifilter_Widget_Js', {}, {
	multifilterControlsView: false,
	multifilterContentView: false,
	multifilterSettingsView: false,
	registerMultifilter() {
		let selectValue = app.cacheGet('multifilterSelectValue', null),
			multifilterSettings = this.getMultifilterSettings();
		if (null != selectValue && this.paramCache) {
			multifilterSettings.find('.js-select').val(selectValue).trigger('change.select2');
		}
		this.loadMultifilterData(true);
		multifilterSettings.find('.js-select').on('select2:select', () => {
			this.loadMultifilterData(true);
			if (this.paramCache) {
				app.cacheSet('multifilterSelectValue', multifilterSettings.find('.js-select').val());
			}
		});
		multifilterSettings.find('.js-select').on('select2:unselect', () => {
			this.loadMultifilterData(false);
			if (this.paramCache) {
				app.cacheSet('multifilterSelectValue', multifilterSettings.find('.js-select').val());
			}
		});
		this.registerShowHideModuleSettings();
	},
	loadMultifilterData(select = true) {
		const self = this;
		let widgetId = self.getMultifilterControls().attr('data-widgetid'),
			multifilterIds = self.getMultifilterSettings().find('.js-select option:selected'),
			params = [];
		if (!select) {
			self.getMultifilterContent().html('');
		}
		multifilterIds.each(function () {
			let existFilter = self.getMultifilterContent().find('[data-id="' + $(this).val() + '"]');
			let thisInstance = $(this);
			if (0 < existFilter.length) {
				return true;
			}
			params = {
				module: thisInstance.data('module'),
				modulename: thisInstance.data('module'),
				view: 'ShowWidget',
				name: 'Multifilter',
				content: true,
				widget: true,
				widgetid: widgetId,
				filterid: thisInstance.val()
			};
			self.loadListData(params);
		});
	},
	loadListData(params) {
		const self = this;
		let aDeferred = jQuery.Deferred(),
			multiFilterContent = self.getMultifilterContent();
		AppConnector.request(params).done(function (data) {
			if (self.getMultifilterSettings().find('option[value="' + params.filterid + '"]').is(':selected') && !multiFilterContent.find('.detailViewTable[data-id="' + params.filterid + '"]').length) {
				self.registerRecordsCount(multiFilterContent.append(data).children("div:last-child"));
				self.registerShowHideBlocks();
				aDeferred.resolve();
			}
		}).fail(function (error) {
			aDeferred.reject();
		});
		return aDeferred.promise();
	},
	registerShowHideModuleSettings() {
		this.getMultifilterControls().find('.js-widget-settings').on('click', () => {
			this.getMultifilterSettings().toggleClass('d-none');
		});
	},
	registerShowHideBlocks() {
		let detailContentsHolder = this.getMultifilterContent();
		detailContentsHolder.find('.blockHeader').off("click");
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
		this.registerMultifilter();
	},
	refreshWidget() {
		this.loadMultifilterData(false);
	},
});
YetiForce_Widget_Js('YetiForce_UpcomingProjectTasks_Widget_Js', {}, {
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
			url += '["projecttaskstatus","e","' + encodeURIComponent(container.find('[name="status"]').data('value')) + '"]]]';
			app.openUrl(url)
		});
	}
});
YetiForce_UpcomingProjectTasks_Widget_Js('YetiForce_CompletedProjectTasks_Widget_Js', {}, {});
