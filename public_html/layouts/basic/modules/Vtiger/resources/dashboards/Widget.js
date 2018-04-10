/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
jQuery.Class('Vtiger_Widget_Js', {
	widgetPostLoadEvent: 'Vtiget.Dashboard.PostLoad',
	widgetPostRefereshEvent: 'Vtiger.Dashboard.PostRefresh',
	getInstance: function getInstance(container, widgetName, moduleName) {
		if (typeof moduleName === 'undefined') {
			moduleName = app.getModuleName();
		}
		const widgetClassName = widgetName.toCamelCase();
		const moduleClass = window[moduleName + "_" + widgetClassName + "_Widget_Js"];
		const fallbackClass = window["Vtiger_" + widgetClassName + "_Widget_Js"];
		const yetiClass = window["YetiForce_" + widgetClassName + "_Widget_Js"];
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
}, {
	container: false,
	plotContainer: false,
	chartInstance: false,
	chartData: [],
	paramCache: false,
	init: function init(container, reload, widgetClassName) {
		this.setContainer(jQuery(container));
		this.registerWidgetPostLoadEvent(container);
		if (!reload) {
			this.registerWidgetPostRefreshEvent(container);
		}
		this.registerCache(container);
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
					return app.parseNumberToShow(value);
				}
				return value;
			},
		},
		/**
		 * Functions for datalabels
		 */
		datalabels: {
			formatter: function datalabelsFormatter(value, context) {
				if (
					typeof context.chart.data.datasets[context.datasetIndex].dataFormatted !== 'undefined' &&
					typeof context.chart.data.datasets[context.datasetIndex].dataFormatted[context.dataIndex] !== 'undefined'
				) {
					// data presented in different format usually exists in alternative dataFormatted array
					return context.chart.data.datasets[context.datasetIndex].dataFormatted[context.dataIndex];
				}
				if (String(value).length > 0 && isNaN(Number(value))) {
					return app.parseNumberToShow(value);
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
				if (typeof data.datasets[tooltipItem.datasetIndex].dataFormatted !== 'undefined' && data.datasets[tooltipItem.datasetIndex].dataFormatted[tooltipItem.index] !== 'undefined') {
					return data.datasets[tooltipItem.datasetIndex].dataFormatted[tooltipItem.index];
				}
				// if there is no formatted data so try to format it
				if (String(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]).length > 0 && !isNaN(Number(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]))) {
					return app.parseNumberToShow(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]);
				}
				// return raw data at idex
				return data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
			},
			title: function tooltipTitleCallback(tooltipItems, data) {
				const tooltipItem = tooltipItems[0];
				// get already formatted title if exists
				if (typeof data.datasets[tooltipItem.datasetIndex].titlesFormatted !== 'undefined' && data.datasets[tooltipItem.datasetIndex].titlesFormatted[tooltipItem.index] !== 'undefined') {
					return data.datasets[tooltipItem.datasetIndex].titlesFormatted[tooltipItem.index];
				}
				// if there is no formatted title so try to format it
				if (String(data.labels[tooltipItem.index]).length > 0 && !isNaN(Number(data.labels[tooltipItem.index]))) {
					return app.parseNumberToShow(data.labels[tooltipItem.index]);
				}
				// return label at index
				return data.labels[tooltipItem.index];
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
				const data = chart.data;
				let datasetsMeta = getDatasetsMeta(chart);
				let datasets = chart.data.datasets;
				for (let i = 0, len = datasets.length; i < len; i++) {
					const dataset = datasets[i];
					const metaData = datasetsMeta[i].data;
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
							if (dataItem._view.width < labelWidth || barHeight < labelHeight) {
								dataItem.$datalabels._model = null;
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
				const data = chart.data;
				let datasetsMeta = getDatasetsMeta(chart);
				let datasets = chart.data.datasets;
				for (let i = 0, len = datasets.length; i < len; i++) {
					const dataset = datasets[i];
					const metaData = datasetsMeta[i].data;
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
							if (dataItem._view.height < labelHeight || barWidth < labelWidth) {
								dataItem.$datalabels._model = null;
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
												if (typeof metaDataItem._xScale !== 'undefined') {
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
		return finalFunction.bind(this);
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
	 * @return {Object} options with replaced string functions
	 */
	parseOptionsObject: function parseOptionsObject(options) {
		let result = {};
		for (let propertyName in options) {
			let value = options[propertyName];
			if (this.isReplacementString(value)) {
				result[propertyName] = this.getFunctionFromReplacementString(value);
			} else if (Array.isArray(value)) {
				result[propertyName] = this.parseOptionsArray(value);
			} else if (typeof value === 'object' && value !== null) {
				result[propertyName] = this.parseOptionsObject(value);
			} else {
				result[propertyName] = value;
			}
		}
		return result;
	},
	/**
	 * Recursively parse options in array form and replace function replacement string with functions
	 * @param  {Array} arr
	 * @return {Array}
	 */
	parseOptionsArray: function parseOptionsArray(arr) {
		return arr.map((item, index) => {
			if (this.isReplacementString(item)) {
				return this.getFunctionFromReplacementString(value);
			} else if (Array.isArray(item)) {
				return this.parseOptionsArray(item);
			} else if (typeof item === 'object' && item !== null) {
				return this.parseOptionsObject(item);
			}
			return item;
		});
	},
	/**
	 * Recursively parse options object and replace function replacement strings with global functions
	 * @param  {Object} options
	 * @return {Object}
	 */
	parseOptions: function parseOptions(options) {
		if (Array.isArray(options)) {
			return this.parseOptionsArray(options);
		} else if (typeof options === 'object' && options !== null) {
			return this.parseOptionsObject(options);
		}
		app.errorLog(new Error('Unknown options format [' + typeof options + '] - should be object.'));
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
		const options = this.parseOptions({
			bar: {
				basic: {
					maintainAspectRatio: false,
					title: {
						display: false
					},
					legend: {
						display: false
					},
					tooltips: {
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
					},
				},
				plugins: [{
					beforeDraw: 'function:plugins.fixXAxisLabels',
				}, {
					beforeDraw: 'function:plugins.hideVerticalBarDatalabelsIfNeeded',
				}],
			},
			bardivided: {
				basic: {
					maintainAspectRatio: false,
					title: {
						display: false
					},
					legend: {
						display: false
					},
					tooltips: {
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
					},
				},
				plugins: [{
					beforeDraw: 'function:plugins.fixXAxisLabels',
				}, {
					beforeDraw: 'function:plugins.hideVerticalBarDatalabelsIfNeeded',
				}],
			},
			horizontalbar: {
				basic: {
					maintainAspectRatio: false,
					title: {
						display: false
					},
					legend: {
						display: false
					},
					tooltips: {
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
					},
				},
				plugins: [{
					beforeDraw: 'function:plugins.fixYAxisLabels'
				}, {
					beforeDraw: 'function:plugins.hideHorizontalBarDatalabelsIfNeeded',
				}],
			},
			// hard edges line
			line: {
				basic: {
					maintainAspectRatio: false,
					title: {
						display: false
					},
					legend: {
						display: false
					},
					tooltips: {
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
					},
				},
				plugins: [{
					beforeDraw: 'function:plugins.fixXAxisLabels'
				}],
			},
			// smooth line
			lineplain: {
				basic: {
					maintainAspectRatio: false,
					title: {
						display: false
					},
					legend: {
						display: false
					},
					tooltips: {
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
						display: true
					},
					cutoutPercentage: 0,
					layout: {
						padding: {
							bottom: 12
						}
					},
					tooltips: {},
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
						display: true
					},
					cutoutPercentage: 50,
					layout: {
						padding: {
							bottom: 12
						}
					},
					tooltips: {
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
						callbacks: {
							label: 'function:tooltips.label',
							title: 'function:tooltips.title'
						}
					},
					scales: {
						yAxes: [{
							display: true,
							beginAtZero: true,
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
		});
		chartSubType = chartSubType.toLowerCase();
		if (typeof options[chartSubType] !== 'undefined') {
			return options[chartSubType];
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
		return this.getContainer().find('.noDataMsg').length > 0;
	},
	getUserDateFormat: function getUserDateFormat() {
		return jQuery('#userDateFormat').val();
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
			AppConnector.request(url).then(function (response) {
				recordsCountBtn.find('.count').html(response.result.totalCount);
				recordsCountBtn.find('span:not(.count)').addClass('d-none');
				recordsCountBtn.find('a').removeClass('d-none');
			});
		});
	},
	loadScrollbar: function loadScrollbar() {
		const container = this.getChartContainer(false);
		if (typeof container === 'undefined') { // if there is no data
			return false;
		}
		const widget = $(container.closest('.dashboardWidget'));
		const content = widget.find('.dashboardWidgetContent');
		const footer = widget.find('.dashboardWidgetFooter');
		const header = widget.find('.dashboardWidgetHeader');
		const headerHeight = header.outerHeight();
		let adjustedHeight = widget.height() - headerHeight;
		if (footer.length)
			adjustedHeight -= footer.outerHeight();
		if (!content.length)
			return;
		content.css('height', adjustedHeight + 'px');
		app.showNewScrollbar(content, {
			wheelPropagation: true
		});
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
	//Place holdet can be extended by child classes and can use this to handle the post load
	postLoadWidget: function postLoadWidget() {
		this.loadScrollbar();
		if (!this.isEmptyData()) {
			this.loadChart(this.options);
		} else {
			this.positionNoDataMsg();
		}
		this.registerSectionClick();
		this.registerFilter();
		this.registerFilterChangeEvent();
		this.restrictContentDrag();
		app.showBtnSwitch(this.getContainer().find('.switchBtn'));
		app.showPopoverElementView(this.getContainer().find('.js-popover-tooltip'));
		this.registerWidgetSwitch();
		this.registerChangeSorting();
		this.registerLoadMore();
		this.registerUserList();
		this.registerHeaderButtons();
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
			var container = this.getContainer();
			var drefresh = container.find('a[name="drefresh"]');
			var url = drefresh.data('url');
			url = url.replace('&sortorder=desc', '');
			url = url.replace('&sortorder=asc', '');
			url += '&sortorder=';
			var sort = currentElement.data('sort');
			var sortorder = 'desc';
			var icon = 'fa-sort-amount-down';
			if (sort == 'desc') {
				sortorder = 'asc';
				icon = 'fa-sort-amount-up';
			}
			currentElement.data('sort', sortorder);
			currentElement.attr('title', currentElement.data(sortorder));
			currentElement.attr('alt', currentElement.data(sortorder));
			url += sortorder;
			var faIcon = currentElement.find('[data-fa-i2svg]');
			faIcon.removeClass().addClass('[data-fa-i2svg]').addClass(icon);
			drefresh.data('url', url);
		}
	},
	registerUserList: function registerUserList() {
		var container = this.getContainer();
		var header = container.find('.dashboardWidgetHeader');
		var ownersFilter = header.find('.ownersFilter')
		if (ownersFilter.length) {
			var owners = container.find('.widgetOwners').val();
			if (owners) {
				var select = ownersFilter.find('select');
				$.each(JSON.parse(owners), function (key, value) {
					select.append($('<option>', {
						value: key
					}).text(value));
				});
			}
		}
	},
	getChartImage() {
		const base64Image = this.chartInstance.toBase64Image();
		const image = new Image();
		image.src = base64Image;
		return image;
	},
	registerHeaderButtons: function registerHeaderButtons() {
		const container = this.getContainer();
		const header = container.find('.dashboardWidgetHeader');
		const downloadWidget = header.find('.downloadWidget');
		const printWidget = header.find('.printWidget');
		printWidget.on('click', (e) => {
			const imgEl = this.getChartImage();
			const print = window.open('', 'PRINT', 'height=400,width=600');
			print.document.write('<html><head><title>' + header.find('.dashboardTitle').text() + '</title>');
			print.document.write('</head><body >');
			print.document.write($('<div>').append(imgEl).html());
			print.document.write('</body></html>');
			print.document.close(); // necessary for IE >= 10
			print.focus(); // necessary for IE >= 10
			setTimeout(function () {
				print.print();
				print.close();
			}, 1000);
		});
		downloadWidget.on('click', (e) => {
			const imgEl = $(this.getChartImage());
			const a = $("<a>")
				.attr("href", imgEl.attr('src'))
				.attr("download", header.find('.dashboardTitle').text() + ".png")
				.appendTo(container);
			a[0].click();
			a.remove();
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
		var switchButtons = this.getContainer().find('.dashboardWidgetHeader .js-calcuations-switch');
		thisInstance.setUrlSwitch(switchButtons);
		switchButtons.on('switchChange.bootstrapSwitch', function (e, state) {
			var currentElement = jQuery(e.currentTarget);
			var dashboardWidgetHeader = currentElement.closest('.dashboardWidgetHeader');
			var drefresh = dashboardWidgetHeader.find('a[name="drefresh"]');
			thisInstance.setUrlSwitch(currentElement).then(function (data) {
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
			if (urlparams != '') {
				var onval = currentElement.data('on-val');
				var offval = currentElement.data('off-val');
				url = url.replace('&' + urlparams + '=' + onval, '');
				url = url.replace('&' + urlparams + '=' + offval, '');
				url += '&' + urlparams + '=';
				if (currentElement.prop('checked'))
					url += onval;
				else
					url += offval;
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
		var thisInstance = this;
		var parent = this.getContainer();
		var element = parent.find('a[name="drefresh"]');
		var url = element.data('url');
		var contentContainer = parent.find('.dashboardWidgetContent');
		var params = url;
		var widgetFilters = parent.find('.widgetFilter');
		if (widgetFilters.length > 0) {
			params = {};
			params.url = url;
			params.data = {};
			widgetFilters.each(function (index, domElement) {
				var widgetFilter = jQuery(domElement);
				var filterType = widgetFilter.attr('type');
				var filterName = widgetFilter.attr('name');
				if ('checkbox' == filterType) {
					var filterValue = widgetFilter.is(':checked');
					params.data[filterName] = filterValue;
				} else {
					var filterValue = widgetFilter.val();
					params.data[filterName] = filterValue;
				}
			});
		}
		var widgetFilterByField = parent.find('.widgetFilterByField');
		if (widgetFilterByField.length) {
			var searchParams = [];
			widgetFilterByField.find('.listSearchContributor').each(function (index, domElement) {
				var searchInfo = [];
				var searchContributorElement = jQuery(domElement);
				var fieldInfo = searchContributorElement.data('fieldinfo');
				var fieldName = searchContributorElement.attr('name');
				var searchValue = searchContributorElement.val();
				if (typeof searchValue == "object") {
					if (searchValue == null) {
						searchValue = "";
					} else {
						searchValue = searchValue.join(',');
					}
				}
				searchValue = searchValue.trim();
				if (searchValue.length <= 0) {
					return true;
				}
				var searchOperator = 'a';
				if (fieldInfo.hasOwnProperty("searchOperator")) {
					searchOperator = fieldInfo.searchOperator;
				} else if (jQuery.inArray(fieldInfo.type, ['modules', 'time', 'userCreator', 'owner', 'picklist', 'tree', 'boolean', 'fileLocationType', 'userRole', 'companySelect', 'multiReferenceValue']) >= 0) {
					searchOperator = 'e';
				} else if (fieldInfo.type == "date" || fieldInfo.type == "datetime") {
					searchOperator = 'bw';
				} else if (fieldInfo.type == 'multipicklist' || fieldInfo.type == 'categoryMultipicklist') {
					searchOperator = 'c';
				}
				searchInfo.push(fieldName);
				searchInfo.push(searchOperator);
				searchInfo.push(searchValue);
				if (fieldInfo.type == 'tree' || fieldInfo.type == 'categoryMultipicklist') {
					var searchInSubcategories = parent.find('.searchInSubcategories[data-columnname="' + fieldName + '"]').prop('checked');
					searchInfo.push(searchInSubcategories);
				}
				searchParams.push(searchInfo);
			});
			if (searchParams.length) {
				params.data.search_params = new Array(searchParams);
			}
		}

		var filterData = this.getFilterData();
		if (!jQuery.isEmptyObject(filterData)) {
			if (typeof params == 'string') {
				url = params;
				params = {};
				params.url = url;
				params.data = {};
			}
			params.data = jQuery.extend(params.data, this.getFilterData());
		}
		var refreshContainer = parent.find('.dashboardWidgetContent');
		var refreshContainerFooter = parent.find('.dashboardWidgetFooter');
		refreshContainer.html('');
		refreshContainerFooter.html('');
		refreshContainer.progressIndicator();
		if (this.paramCache && widgetFilters.length > 0) {
			thisInstance.setFilterToCache(params.url, params.data);
		}
		AppConnector.request(params).then(function (data) {
				var data = jQuery(data);
				var footer = data.filter('.widgetFooterContent');
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
			}, function () {
				refreshContainer.progressIndicator({
					'mode': 'hide'
				});
			}
		);
	},
	registerFilter: function registerFilter() {
		var thisInstance = this;
		var container = this.getContainer();
		var dateRangeElement = container.find('input.dateRangeField');
		if (dateRangeElement.length <= 0) {
			return;
		}
		dateRangeElement.addClass('dateRangeField').attr('data-date-format', thisInstance.getUserDateFormat());
		App.Fields.Date.registerRange(dateRangeElement, {
			opens: "auto"
		});
		dateRangeElement.on('apply.daterangepicker', function (ev, picker) {
			container.find('a[name="drefresh"]').trigger('click');
		});
	},
	registerFilterChangeEvent: function registerFilterChangeEvent() {
		var container = this.getContainer();
		container.on('change', '.widgetFilter', function (e) {
			var widgetContainer = jQuery(e.currentTarget).closest('li');
			widgetContainer.find('a[name="drefresh"]').trigger('click');
		});
		if (container.find('.widgetFilterByField').length) {
			App.Fields.Picklist.showSelect2ElementView(container.find('.select2noactive'));
			this.getContainer().on('change', '.widgetFilterByField .form-control', function (e) {
				var widgetContainer = jQuery(e.currentTarget).closest('li');
				widgetContainer.find('a[name="drefresh"]').trigger('click');
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
			if (typeof thisInstance.getDataFromEvent(e, ['links']).links !== 'undefined') {
				window.location.href = thisInstance.getDataFromEvent(e, ['links']).links;
			}
		}).on('mousemove', function (e) {
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
			AppConnector.request(url).then(function (data) {
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
		var userId = app.getMainParams('current_user_id');
		var name = container.data('name');
		app.cacheSet(name + userId, paramCache);
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
		const type = this.getType();
		const data = this.generateData();
		data.datasets = this.loadDatasetOptions(data);
		const options = this.loadBasicOptions(data);
		const plugins = this.loadPlugins(data);
		return this.chartInstance = new Chart(
			this.getChartContainer().getContext("2d"), {
				type,
				data,
				options,
				plugins
			}
		);
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
		if (typeof additionalFields !== 'undefined' && Array.isArray(additionalFields)) {
			additionalFields.forEach((fieldName) => {
				if (typeof chart.data.datasets[datasetIndex][fieldName] !== 'undefined' && typeof chart.data.datasets[datasetIndex][fieldName][dataIndex] !== 'undefined') {
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
			this.getDefaultBasicOptions(this.getSubType(), chartData),
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
			if (typeof dataset.titlesFormatted === 'undefined') {
				dataset.titlesFormatted = [];
				dataset.data.forEach((dataItem, index) => {
					let defaultLabel = data.labels[index];
					if (String(defaultLabel).length > 0 && !isNaN(Number(defaultLabel))) {
						defaultLabel = app.parseNumberToShow(defaultLabel);
					}
					if (typeof dataset.label !== 'undefined') {
						let label = dataset.label;
						if (String(label).length > 0 && !isNaN(Number(label))) {
							label = app.parseNumberToShow(label);
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
						dataFormatted = app.parseNumberToShow(dataItem);
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
		let result = fromArray.map((from, index) => {
			if (Array.isArray(from) && !to.hasOwnProperty(key)) {
				return this.mergeOptionsArray(to[index], from);
			}
			if (typeof from === 'object' && from !== null && (typeof to[index] === 'undefined' || (typeof to[index] === 'object' && to[index] !== null))) {
				return this.mergeOptionsObject(to[index], from);
			}
			return to[index];
		}).filter((item) => typeof item !== 'undefined');
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
		return {};
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
YetiForce_Bar_Widget_Js('YetiForce_Barchat_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_Horizontal_Widget_Js', {}, {
	getType: function () {
		return 'horizontalBar';
	},
});
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
			AppConnector.request(params).then(function (data) {
				refreshContainer.progressIndicator({
					'mode': 'hide'
				});
				loadMoreHandler.replaceWith(data);
				thisInstance.registerLoadMore();
			}, function () {
				refreshContainer.progressIndicator({
					'mode': 'hide'
				});
			});
		});
	}

});
YetiForce_Widget_Js('YetiForce_Chartfilter_Widget_Js', {}, {
	chartfilterInstance: false,
	init: function (container, reload, widgetClassName) {
		this.setContainer(jQuery(container));
		var chartType = container.find('[name="typeChart"]').val();
		var chartClassName = chartType.toCamelCase();
		this.chartfilterInstance = YetiForce_Widget_Js.getInstance(container, chartClassName);
		this.registerRecordsCount();
	},
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
YetiForce_Pie_Widget_Js('YetiForce_Donut_Widget_Js', {}, {
	getType: function getType() {
		return 'doughnut';
	},
});
YetiForce_Donut_Widget_Js('YetiForce_Axis_Widget_Js', {}, {});
YetiForce_Widget_Js('YetiForce_Bardivided_Widget_Js', {}, {
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
YetiForce_Line_Widget_Js('YetiForce_Lineplain_Widget_Js', {}, {
	getSubType: function getSubType() {
		return 'linePlain';
	}
});
YetiForce_Bar_Widget_Js('YetiForce_MultiBarchat_Widget_Js', {});
// NOTE Widget-class name camel-case convention
YetiForce_Widget_Js('YetiForce_Minilist_Widget_Js', {}, {
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
YetiForce_Widget_Js('YetiForce_Charts_Widget_Js', {}, {
	loadChart: function () {
		var container = this.getContainer();
		var chartType = container.find('[name="typeChart"]').val();
		var chartClassName = chartType.toCamelCase();
		var chartClass = window["Report_" + chartClassName + "_Js"];
		var instance = false;
		if (typeof chartClass != 'undefined') {
			instance = new chartClass(container, true);
			instance.loadChart();
		}
	}
});
/* Notebook Widget */
YetiForce_Widget_Js('YetiForce_Notebook_Widget_Js', {}, {
	// Override widget specific functions.
	postLoadWidget: function () {
		this.reinitNotebookView();
	},
	reinitNotebookView: function () {
		var self = this;
		app.showScrollBar(jQuery('.dashboard_notebookWidget_viewarea', this.container), {
			'height': '200px'
		});
		jQuery('.dashboard_notebookWidget_edit', this.container).on('click', function () {
			self.editNotebookContent();
		});
		jQuery('.dashboard_notebookWidget_save', this.container).on('click', function () {
			self.saveNotebookContent();
		});
	},
	editNotebookContent: function () {
		jQuery('.dashboard_notebookWidget_text', this.container).show();
		jQuery('.dashboard_notebookWidget_view', this.container).hide();
		$('body').on('click', function (e) {
			if ($(e.target).closest('.dashboard_notebookWidget_view').length === 0 && $(e.target).closest('.dashboard_notebookWidget_text').length === 0) {
				$('.dashboard_notebookWidget_save').trigger('click');
			}
		});
	},
	saveNotebookContent: function () {
		$('body').off('click');
		var self = this;
		var textarea = jQuery('.dashboard_notebookWidget_textarea', this.container);
		var url = this.container.data('url');
		var params = url + '&content=true&mode=save&contents=' + encodeURIComponent(textarea.val());
		var refreshContainer = this.container.find('.dashboardWidgetContent');
		refreshContainer.progressIndicator();
		AppConnector.request(params).then(function (data) {
			refreshContainer.progressIndicator({
				'mode': 'hide'
			});
			jQuery('.dashboardWidgetContent', self.container).html(data);
			self.reinitNotebookView();
		});
	}
});
YetiForce_Widget_Js('YetiForce_KpiBarchat_Widget_Js', {}, {
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
YetiForce_Bar_Widget_Js('YetiForce_Ticketsbystatus_Widget_Js', {}, {
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
		var userDefaultTimeFormat = jQuery('#time_format').val();
		if (userDefaultTimeFormat == 24) {
			userDefaultTimeFormat = 'H(:mm)';
		} else {
			userDefaultTimeFormat = 'h(:mm) A';
		}

		//Default first day of the week
		var convertedFirstDay = CONFIG.firstDayOfWeekNo;
		//Default first hour of the day
		var defaultFirstHour = jQuery('#start_hour').val();
		var explodedTime = defaultFirstHour.split(':');
		defaultFirstHour = explodedTime['0'];
		var defaultDate = app.getMainParams('defaultDate');
		if (this.paramCache && defaultDate != moment().format('YYYY-MM-DD')) {
			defaultDate = moment(defaultDate).format('D') == 1 ? moment(defaultDate) : moment(defaultDate).add(1, 'M');
		}

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
						'<span class="' + event.event[key].className + ((event.width <= 20) ? ' small-badge' : '') + ((event.width >= 24) ? ' big-badge' : '') + ' badge badge-secondary">' + event.event[key].count + '</span>' +
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
		thisInstance.getCalendarView().find("td.fc-day-top").on('click', function () {
			var date = $(this).data('date');
			var params = {
				noCache: true
			};
			params.data = {
				date_start: date,
				due_date: date
			};
			params.callbackFunction = function () {
				thisInstance.getCalendarView().closest('.dashboardWidget').find('a[name="drefresh"]').trigger('click');
			};
			YetiForce_Header_Js.getInstance().quickCreateModule('Calendar', params);
		});
		var switchBtn = container.find('.switchBtn');
		app.showBtnSwitch(switchBtn);
		switchBtn.on('switchChange.bootstrapSwitch', function (e, state) {
			if (state)
				container.find('.widgetFilterSwitch').val('current');
			else
				container.find('.widgetFilterSwitch').val('history');
			thisInstance.refreshWidget();
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
		AppConnector.request(params).then(function (events) {
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
			this.calendarView = this.getContainer().find('#calendarview');
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
			var headerCalendar = container.find('.headerCalendar').removeClass('d-none').find('.month').append('<h3>' + month + '</h3>');
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
YetiForce_Widget_Js('YetiForce_Calendaractivities_Widget_Js', {}, {
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
			if (typeof url != 'undefined') {
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
		container.find('.goToListView').on('click', function () {
			if (container.data('name') == 'OverdueActivities') {
				var status = 'PLL_OVERDUE';
			} else {
				var status = 'PLL_IN_REALIZATION,PLL_PLANNED';
			}
			var url = 'index.php?module=Calendar&view=List&viewname=All';
			url += '&search_params=[[';
			var owner = container.find('.widgetFilter.owner option:selected');
			if (owner.val() != 'all') {
				url += '["assigned_user_id","e","' + owner.val() + '"],';
			}
			url += '["activitystatus","e","' + status + '"]]]';
			window.location.href = url;
		});
	}
});
YetiForce_Calendaractivities_Widget_Js('YetiForce_Assignedupcomingcalendartasks_Widget_Js', {}, {});
YetiForce_Calendaractivities_Widget_Js('YetiForce_Creatednotmineactivities_Widget_Js', {}, {});
YetiForce_Calendaractivities_Widget_Js('YetiForce_Overdueactivities_Widget_Js', {}, {});
YetiForce_Calendaractivities_Widget_Js('YetiForce_Assignedoverduecalendartasks_Widget_Js', {}, {});
YetiForce_Widget_Js('YetiForce_Productssoldtorenew_Widget_Js', {}, {
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
			if (typeof url != 'undefined') {
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
		container.find('.goToListView').on('click', function () {
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
YetiForce_Productssoldtorenew_Widget_Js('YetiForce_Servicessoldtorenew_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_Alltimecontrol_Widget_Js', {}, {
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
YetiForce_Bar_Widget_Js('YetiForce_Leadsbysource_Widget_Js', {}, {});
YetiForce_Pie_Widget_Js('YetiForce_Closedticketsbypriority_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_Closedticketsbyuser_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_Opentickets_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_Accountsbyindustry_Widget_Js', {}, {});
YetiForce_Funnel_Widget_Js('YetiForce_Estimatedvaluebystatus_Widget_Js', {}, {
	getBasicOptions: function getBasicOptions() {
		return {
			sort: 'data-desc'
		};
	},
	getPlugins: function getPlugins() {
		return [];
	}
});
YetiForce_Barchat_Widget_Js('YetiForce_Notificationsbysender_Widget_Js', {}, {});
YetiForce_Barchat_Widget_Js('YetiForce_Notificationsbyrecipient_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_Teamsestimatedsales_Widget_Js', {}, {
	generateChartData: function () {
		var thisInstance = this;
		var container = this.getContainer();
		var jData = container.find('.widgetData').val();
		var data = JSON.parse(jData);
		var chartData = [
			[],
			[],
			[],
			[]
		];
		var yMaxValue = 0;
		if (data.hasOwnProperty('compare')) {
			for (var index in data) {
				var parseData = thisInstance.parseChartData(data[index], chartData);
				chartData[0].push(parseData[0]);
				chartData[3].push(parseData[3]);
				chartData = [chartData[0], parseData[1], parseData[2], chartData[3],
					['#CC6600', '#208CB3']
				];
			}
		} else {
			var parseData = thisInstance.parseChartData(data, chartData);
			chartData = [
				[parseData[0]], parseData[1], parseData[2],
				[parseData[3]],
				['#208CB3']
			];
		}
		var yMaxValue = chartData[1];
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
		var container = this.getContainer();
		var data = container.find('.widgetData').val();
		var dataInfo = JSON.parse(data);
		var compare = dataInfo && dataInfo.hasOwnProperty('compare');
		this.getContainer().off('jqplotDataClick').on('jqplotDataClick', function (ev, seriesIndex, pointIndex, args) {
			if (seriesIndex) {
				var url = dataInfo['compare'][pointIndex][2];
			} else if (compare) {
				var url = dataInfo[0][pointIndex][2];
			} else {
				var url = dataInfo[pointIndex][2];
			}
			window.location.href = url;
		});
	}
});
YetiForce_Teamsestimatedsales_Widget_Js('YetiForce_Actualsalesofteam_Widget_Js', {}, {});
