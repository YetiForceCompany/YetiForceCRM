/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

YetiForce_Widget_Js(
	'YetiForce_Chart_Widget_Js',
	{},
	{
		plotContainer: null,
		chartInstance: null,
		chartData: [],
		filterIds: [],

		getChartContainer: function getChartContainer(useCache = false) {
			if (!this.plotContainer || !useCache) {
				this.plotContainer = this.getContainer().find('.js-chart-container').get(0);
			}
			return this.plotContainer;
		},
		getChartInstance: function getChartInstance(reload = false) {
			if (!this.chartInstance && this.getChartContainer()) {
				this.chartInstance =
					echarts.getInstanceByDom(this.getChartContainer()) || echarts.init(this.getChartContainer());
			}
			return this.chartInstance;
		},
		destroyChartInstance: function () {
			let chart = this.chartInstance || echarts.getInstanceByDom(this.getChartContainer() || '');
			if (chart) {
				chart.isDisposed() ? chart.dispose() : null;
				this.chartInstance = null;
				this.plotContainer = null;
			}
		},
		registerResize: function resize() {
			let container = this.getChartContainer();
			if (!container) {
				return false;
			}
			let chart = this.getChartInstance();
			new ResizeObserver((e) => {
				this.loadScrollbar();
				const boundingRect = e[0].target.getBoundingClientRect();
				chart.resize({
					width: boundingRect.width,
					height: boundingRect.height
				});
			}).observe(container.parentNode);
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
			// const type = this.getType();
			let data = this.generateData();
			data = this.mergeAll([data, this.getBasicOptions(data)]);
			console.log(data);
			this.destroyChartInstance();

			let chart = this.getChartInstance();
			chart.setOption(data);

			return chart;
		},
		merge: function merge(target, source, overwrite) {
			if (!this.isObject(source) || !this.isObject(target)) {
				return overwrite ? this.clone(source) : target;
			}
			for (var key in source) {
				if (source.hasOwnProperty(key) && key !== '__proto__') {
					var targetProp = target[key];
					var sourceProp = source[key];
					if (
						this.isObject(sourceProp) &&
						this.isObject(targetProp) &&
						!this.isArray(sourceProp) &&
						!this.isArray(targetProp)
					) {
						this.merge(targetProp, sourceProp, overwrite);
					} else if (overwrite || !(key in target)) {
						target[key] = this.clone(source[key]);
					} else if (this.isArray(sourceProp) && this.isArray(targetProp)) {
						target[key] = this.mergeAll([targetProp, sourceProp]);
					}
				}
			}
			return target;
		},
		mergeAll: function mergeAll(targetAndSources, overwrite) {
			var result = targetAndSources[0];
			for (var i = 1, len = targetAndSources.length; i < len; i++) {
				result = this.merge(result, targetAndSources[i], overwrite);
			}
			return result;
		},
		isObject: function isObject(value) {
			var type = typeof value;
			return type === 'function' || (!!value && type === 'object');
		},
		isArray: function isArray(value) {
			if (Array.isArray) {
				return Array.isArray(value);
			}
			return objToString.call(value) === '[object Array]';
		},
		clone: function clone(source) {
			if (source == null || typeof source !== 'object') {
				return source;
			}
			var result = source;
			var typeStr = Object.prototype.toString.call(source);
			if (typeStr === '[object Array]') {
				result = [];
				for (var i = 0, len = source.length; i < len; i++) {
					result[i] = this.clone(source[i]);
				}
			} else {
				result = {};
				for (var key in source) {
					if (source.hasOwnProperty(key) && key !== '__proto__') {
						result[key] = this.clone(source[key]);
					}
				}
			}
			return result;
		},
		clear: function clear() {
			this.destroyChartInstance();
			this._super();
		},
		postLoadWidget: function postLoadWidget() {
			if (!this.isEmptyData()) {
				this.loadChart(this.options);
			}
			this._super();
			this.registerRecordsCount();
			this.registerSectionClick();
		},
		postRefreshWidget: function postRefreshWidget() {
			if (!this.isEmptyData()) {
				this.loadChart(this.options);
			}
			this._super();
			this.registerResize();
			this.registerSectionClick();
		},
		getChartImage() {
			const base64Image = this.chartInstance.getDataURL({
				backgroundColor: '#fff'
			});
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
			this._super();
		},
		registerSectionClick: function registerSectionClick() {
			console.log(this.className);
			let chart = this.getChartInstance();
			if (chart) {
				chart.on('click', (e) => {
					for (let key in e.data) {
						if (key === 'link' && e.data[key]) {
							window.location.href = e.data[key];
						} else if (this.isObject(e.data[key]) && e.data[key].link) {
							window.location.href = e.data[key].link;
						}
					}
				});
			}
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
		},

		isMultiFilter() {
			if (typeof this.filterIds !== 'undefined') {
				return this.filterIds.length > 1;
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
					return this.isMultiFilter() || this.areColorsFromDividingField();
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
		}
	}
);
YetiForce_Chart_Widget_Js(
	'YetiForce_Bar_Widget_Js',
	{},
	{
		getBasicOptions: function getBasicOptions() {
			return {
				legend: {},
				xAxis: {
					type: 'category'
				},
				yAxis: {
					type: 'value',
					axisLabel: {
						formatter: (value) => (typeof value === 'number' ? App.Fields.Double.formatToDisplay(value) : value)
					}
				},
				grid: {
					left: '3%',
					right: '4%',
					bottom: '3%',
					top: '15%',
					containLabel: true
				},
				tooltip: {
					valueFormatter: (value) => (typeof value === 'number' ? App.Fields.Double.formatToDisplay(value) : value)
				},
				label: {
					show: true,
					position: 'top',
					formatter: function (data) {
						let value;
						if (typeof data.value === 'number') {
							value = data.value;
						} else if (typeof data.value[data.encode.y[0]] === 'number') {
							value = data.value[data.encode.y[0]];
						} else if (typeof data.value[data.seriesName] === 'number') {
							value = data.value[data.seriesName];
						}

						return value !== undefined ? App.Fields.Double.formatToDisplay(value) : null;
					}
				},
				labelLayout: {
					hideOverlap: true
				},
				series: [
					{
						type: 'bar'
					}
				]
			};
		},
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
		getBasicOptions: function getBasicOptions() {
			return {
				xAxis: {
					type: 'value',
					axisLabel: {
						formatter: (value) => (typeof value === 'number' ? App.Fields.Double.formatToDisplay(value) : value)
					}
				},
				yAxis: {
					type: 'category'
				},
				grid: {
					left: '3%',
					right: '4%',
					bottom: '3%',
					top: '4%',
					containLabel: true
				},
				tooltip: {
					valueFormatter: (value) => (typeof value === 'number' ? App.Fields.Double.formatToDisplay(value) : value)
				},
				label: {
					show: true,
					position: 'inside',
					formatter: function (data) {
						let value;
						if (typeof data.value === 'number') {
							value = data.value;
						} else if (typeof data.value[data.encode.x[0]] === 'number') {
							value = data.value[data.encode.x[0]];
						} else if (typeof data.value[data.seriesName] === 'number') {
							value = data.value[data.seriesName];
						}

						return value !== undefined ? App.Fields.Double.formatToDisplay(value) : null;
					}
				},
				labelLayout: {
					hideOverlap: true
				},
				series: [
					{
						type: 'bar'
					}
				]
			};
		},

		getType: function () {
			return 'bar';
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
YetiForce_Chart_Widget_Js(
	'YetiForce_Funnel_Widget_Js',
	{},
	{
		getType: function getType() {
			return 'funnel';
		}
	}
);
YetiForce_Chart_Widget_Js(
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
YetiForce_Chart_Widget_Js(
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
YetiForce_Chart_Widget_Js(
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
YetiForce_Chart_Widget_Js(
	'YetiForce_ChartFilter_Widget_Js',
	{},
	{
		init: function (container, reload, widgetClassName) {
			container = $(container);
			this.setContainer(container);
			let chartClassName = container.find('[name="typeChart"]').val();
			const stacked = !!Number(container.find('[name="stacked"]').val());
			if (stacked) {
				chartClassName += 'Stacked';
			}
			let instance = YetiForce_Chart_Widget_Js.getInstance(container, chartClassName);
			if (instance) {
				const filterIdsStr = instance.getContainer().find('[name="filterIds"]').val();
				if (filterIdsStr) {
					instance.filterIds = JSON.parse(filterIdsStr);
				}
			}
			this.registerCache(container);
		}
	}
);
YetiForce_Bar_Widget_Js(
	'YetiForce_AllTimeControl_Widget_Js',
	{},
	{
		getBasicOptions: function getBasicOptions() {
			return {
				xAxis: {
					type: 'category'
				},
				yAxis: {
					type: 'value'
				},
				series: [
					{
						type: 'bar'
					}
				]
			};
		}
		// getDatasetOptions: function getDatasetOptions(dataset, type, datasetIndex) {
		// 	return {
		// 		datalabels: {
		// 			formatter: function datalabelsFormatter(value, context) {
		// 				return context.dataset.dataFormatted[context.dataIndex];
		// 			}
		// 		}
		// 	};
		// }
	}
);

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
			// const container = this.getContainer(),
			// 	data = container.find('.widgetData').val(),
			// 	dataInfo = JSON.parse(data),
			// 	compare = dataInfo && dataInfo.hasOwnProperty('compare');
			// let url;
			// this.getContainer()
			// 	.off('jqplotDataClick')
			// 	.on('jqplotDataClick', function (ev, seriesIndex, pointIndex, args) {
			// 		if (seriesIndex) {
			// 			url = dataInfo['compare'][pointIndex][2];
			// 		} else if (compare) {
			// 			url = dataInfo[0][pointIndex][2];
			// 		} else {
			// 			url = dataInfo[pointIndex][2];
			// 		}
			// 		window.location.href = url;
			// 	});
		}
	}
);

YetiForce_TeamsEstimatedSales_Widget_Js('YetiForce_ActualSalesOfTeam_Widget_Js', {}, {});
YetiForce_Chart_Widget_Js(
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
