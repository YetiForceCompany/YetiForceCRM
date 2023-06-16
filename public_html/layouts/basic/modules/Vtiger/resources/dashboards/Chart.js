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
		customOption: {},

		/**
		 * Get plot container
		 * @param {Boolean} useCache
		 * @returns {HTMLElement}
		 */
		getChartContainer: function getChartContainer(useCache = false) {
			if (!this.plotContainer || !useCache) {
				this.plotContainer = this.getContainer().find('.js-chart-container').get(0);
			}
			return this.plotContainer;
		},
		/**
		 * Get chart instance
		 * @returns
		 */
		getChartInstance: function getChartInstance() {
			if (!this.chartInstance && this.getChartContainer()) {
				this.chartInstance =
					echarts.getInstanceByDom(this.getChartContainer()) || echarts.init(this.getChartContainer());
			}
			return this.chartInstance;
		},
		/**
		 * Destroy chart instance
		 */
		destroyChartInstance: function () {
			let chart = this.chartInstance || echarts.getInstanceByDom(this.getChartContainer() || '');
			if (chart) {
				chart.isDisposed() ? chart.dispose() : null;
				this.chartInstance = null;
				this.plotContainer = null;
			}
		},
		/**
		 * Register rezise event
		 */
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
		 * @returns {object} chart options
		 */
		getBasicOptions: function getBasicOptions() {
			return {};
		},

		/**
		 * Get data from JSON encoded input value
		 *
		 * @return {object} data for chart
		 */
		generateData: function generateData() {
			return this.getWidgetData();
		},

		/**
		 * Load and display chart into the view
		 *
		 * @return {*} chartInstance
		 */
		loadChart: function loadChart() {
			if (typeof this.chartData === 'undefined' || typeof this.getChartContainer() === 'undefined') {
				return false;
			}

			let data = this.generateData();
			data = this.mergeAll([data, this.customOption, this.getBasicOptions()]);
			this.destroyChartInstance();

			let chart = this.getChartInstance();
			chart.setOption(data);

			return chart;
		},
		/**
		 * Merge data
		 * @param {Object} target
		 * @param {Object} source
		 * @param {Object} overwrite
		 * @returns {Object}
		 */
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
		/**
		 * Register click event for chart element
		 */
		registerSectionClick: function registerSectionClick() {
			let chart = this.getChartInstance();
			if (chart) {
				chart.on('click', (e) => {
					let links = this.getWidgetData().links || null;
					if (links && this.isObject(links) && Object.keys(links).length > 0) {
						let link = links[e.seriesIndex][e.dataIndex] || '';
						if (link) {
							window.location.href = link;
						}
					} else {
						for (let key in e.data) {
							if (key === 'link' && e.data[key]) {
								window.location.href = e.data[key];
							} else if (this.isObject(e.data[key]) && e.data[key].link) {
								window.location.href = e.data[key].link;
							}
						}
					}
				});
			}
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
		}
	}
);
YetiForce_Chart_Widget_Js(
	'YetiForce_Bar_Widget_Js',
	{},
	{
		/** @inheritdoc */
		getBasicOptions: function getBasicOptions() {
			return {
				legend: { type: 'scroll' },
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
					valueFormatter: (value) => (typeof value === 'number' ? App.Fields.Double.formatToDisplay(value) : value),
					appendToBody: true
				},

				label: {
					show: true,
					position: 'top',
					formatter: function (data) {
						let value;
						if (typeof data.value === 'number') {
							value = data.value;
						} else if (data.encode && typeof data.value[data.encode.y[0]] === 'number') {
							value = data.value[data.encode.y[0]];
						} else if (typeof data.value[data.seriesName] === 'number') {
							value = data.value[data.seriesName];
						}

						return value !== undefined ? App.Fields.Double.formatToDisplay(value) : data.value;
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
		/** @inheritdoc */
		getBasicOptions: function getBasicOptions() {
			let options = this._super();
			options.label = {
				show: true,
				position: 'inside'
			};
			options.series = [
				{
					type: 'bar',
					stack: 'total'
				}
			];

			return options;
		},
		getSubType() {
			return 'barStacked';
		}
	}
);
YetiForce_Bar_Widget_Js(
	'YetiForce_Horizontal_Widget_Js',
	{},
	{
		/** @inheritdoc */
		getBasicOptions: function getBasicOptions() {
			let options = this._super();
			options.xAxis.type = 'value';
			options.yAxis.type = 'category';
			options.grid.top = '10%';
			options.label.position = 'inside';
			options.label.formatter = function (data) {
				let value;
				if (typeof data.value === 'number') {
					value = data.value;
				} else if (data.encode && typeof data.value[data.encode.x[0]] === 'number') {
					value = data.value[data.encode.x[0]];
				} else if (typeof data.value[data.seriesName] === 'number') {
					value = data.value[data.seriesName];
				}

				return value !== undefined ? App.Fields.Double.formatToDisplay(value) : data.value;
			};

			return options;
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
		/** @inheritdoc */
		getBasicOptions: function getBasicOptions() {
			return {
				legend: { type: 'scroll' },
				tooltip: {
					trigger: 'item',
					valueFormatter: (value) => (typeof value === 'number' ? App.Fields.Double.formatToDisplay(value) : value),
					appendToBody: true
				},
				series: [
					{
						type: 'funnel',
						bottom: 10,
						label: {
							show: true,
							position: 'left'
						}
					}
				]
			};
		},
		getType: function getType() {
			return 'funnel';
		}
	}
);
YetiForce_Chart_Widget_Js(
	'YetiForce_Pie_Widget_Js',
	{},
	{
		/** @inheritdoc */
		generateData: function generateData() {
			let dataChart = this.getWidgetData();
			let groupKey = '|x|';
			let convert =
				dataChart.dataset &&
				dataChart.dataset.dimensions &&
				dataChart.dataset.source &&
				dataChart.dataset.source[groupKey];
			if (!convert) {
				return dataChart;
			}

			dataChart = { ...this.getWidgetData() };
			let dimensions = dataChart.dataset.dimensions || [];
			let newSeries = [];
			let step = 100 / (dimensions.length - (dimensions.length > 5 ? 0 : 1));
			let keyIterator = 0;

			let dataGroups = dataChart.dataset.source[groupKey];
			let maxLenght = dataGroups.length;
			for (let dim of dimensions) {
				if (dim === groupKey) {
					continue;
				}
				let series = {
					name: dim,
					type: 'pie',
					label: { show: false },
					radius: [step * keyIterator + '%', step * keyIterator + step - step / 10 + '%'],
					top: '20',
					itemStyle: {
						borderRadius: 2,
						borderColor: '#fff',
						borderWidth: 2
					}
				};
				let newData = [];
				let data = dataChart.dataset.source[dim];
				for (let i = 0; i < maxLenght; i++) {
					let value = data[i] !== undefined ? data[i] : null;
					newData.push({ value: value, name: dataGroups[i] });
				}
				series.data = newData;
				newSeries.push(series);
				keyIterator++;
			}
			delete dataChart.dataset;
			dataChart = this.mergeAll([{ series: newSeries }, dataChart]);

			return dataChart;
		},
		/** @inheritdoc */
		getBasicOptions: function getBasicOptions() {
			return {
				legend: { type: 'scroll' },
				tooltip: {
					trigger: 'item',
					valueFormatter: (value) => (typeof value === 'number' ? App.Fields.Double.formatToDisplay(value) : value),
					appendToBody: true
				},
				series: [
					{
						type: 'pie'
					}
				]
			};
		},
		getType: function getType() {
			return 'pie';
		}
	}
);
YetiForce_Pie_Widget_Js(
	'YetiForce_Donut_Widget_Js',
	{},
	{
		getBasicOptions: function getBasicOptions() {
			let options = this._super();
			options.series = [
				{
					type: 'pie',
					radius: ['40%', '70%']
				}
			];

			return options;
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
YetiForce_Chart_Widget_Js(
	'YetiForce_Line_Widget_Js',
	{},
	{
		getBasicOptions: function getBasicOptions() {
			return {
				legend: { type: 'scroll' },
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
						} else if (data.encode && typeof data.value[data.encode.y[0]] === 'number') {
							value = data.value[data.encode.y[0]];
						} else if (typeof data.value[data.seriesName] === 'number') {
							value = data.value[data.seriesName];
						}

						return value !== undefined ? App.Fields.Double.formatToDisplay(value) : data.value;
					}
				},
				labelLayout: {
					hideOverlap: true
				},
				series: [
					{
						type: 'line'
					}
				]
			};
		},
		getType: function getType() {
			return 'line';
		}
	}
);
YetiForce_Line_Widget_Js(
	'YetiForce_LinePlain_Widget_Js',
	{},
	{
		/** @inheritdoc */
		generateData: function generateData() {
			let dataChart = this.getWidgetData();
			if (Object.keys(dataChart.series).length > 1) {
				dataChart = { ...this.getWidgetData() };
				let defaultSeries = this.getBasicOptions().series;
				for (let i in dataChart.series) {
					dataChart.series[i] = this.mergeAll([dataChart.series[i], defaultSeries[0]]);
				}
			}

			return dataChart;
		},
		getBasicOptions: function getBasicOptions() {
			let options = this._super();
			options.series = [
				{
					type: 'line',
					smooth: true
				}
			];

			return options;
		},
		getSubType: function getSubType() {
			return 'linePlain';
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

YetiForce_LineStacked_Widget_Js(
	'YetiForce_LinePlainStacked_Widget_Js',
	{},
	{
		getSubType() {
			return 'linePlainStacked';
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
