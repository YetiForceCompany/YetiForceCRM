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
		if (typeof moduleName == 'undefined') {
			moduleName = app.getModuleName();
		}
		var widgetClassName = widgetName.toCamelCase();
		var moduleClass = window[moduleName + "_" + widgetClassName + "_Widget_Js"];
		var fallbackClass = window["Vtiger_" + widgetClassName + "_Widget_Js"];
		var yetiClass = window["YetiForce_" + widgetClassName + "_Widget_Js"];
		var basicClass = YetiForce_Widget_Js;
		var instance;
		if (typeof moduleClass != 'undefined') {
			instance = new moduleClass(container, false, widgetClassName);
		} else if (typeof fallbackClass != 'undefined') {
			instance = new fallbackClass(container, false, widgetClassName);
		} else if (typeof yetiClass != 'undefined') {
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
	getContainer: function getContainer() {
		return this.container;
	},
	setContainer: function setContainer(element) {
		this.container = element;
		return this;
	},
	isEmptyData: function isEmptyData() {
		var container = this.getContainer();
		return (container.find('.noDataMsg').length > 0) ? true : false;
	},
	getUserDateFormat: function getUserDateFormat() {
		return jQuery('#userDateFormat').val();
	},
	getPlotContainer: function getPlotContainer(useCache) {
		if (typeof useCache == 'undefined') {
			useCache = false;
		}
		if (this.plotContainer == false || !useCache) {
			var container = this.getContainer();
			this.plotContainer = container.find('.widgetChartContainer').find('canvas').get(0);
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
		const container = this.getPlotContainer(false);
		if (typeof container === 'undefined') { // if there is no data
			return false;
		}
		const widget = $(container.closest('.dashboardWidget'));
		const content = widget.find('.dashboardWidgetContent');
		const footer = widget.find('.dashboardWidgetFooter');
		const header = widget.find('.dashboardWidgetHeader');
		const headerHeight = header.outerHeight();
		const adjustedHeight = widget.height() - headerHeight;
		if (footer.length)
			adjustedHeight -= footer.outerHeight();
		if (!content.length)
			return;
		content.css('height', adjustedHeight + 'px');
		app.showNewScrollbar(content, {wheelPropagation: true});
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
	convertToDateRangePicketFormat: function convertToDateRangePicketFormat(userDateFormat) {
		switch (userDateFormat) {
			case 'yyyy-mm-dd':
				return 'yyyy-MM-dd';
			case 'mm-dd-yyyy':
				return 'MM-dd-yyyy';
			case 'dd-mm-yyyy':
				return 'dd-MM-yyyy';
			case 'yyyy.mm.dd':
				return 'yyyy.MM.dd';
			case 'mm.dd.yyyy':
				return 'MM.dd.yyyy';
			case 'dd.mm.yyyy':
				return 'dd.MM.yyyy';
			case 'yyyy/mm/dd':
				return 'yyyy/MM/dd';
			case 'mm/dd/yyyy':
				return 'MM/dd/yyyy';
			case 'dd/mm/yyyy':
				return 'dd/MM/yyyy';
		}
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
	/**
	 * Load and display chart into the view
	 *
	 * @return {undefined}
	 */
	loadChart: function loadChart() {
		const thisInstance = this;
		if (typeof thisInstance.chartData === 'undefined' || typeof thisInstance.getPlotContainer() === 'undefined') {
			return false;
		}
		const data = thisInstance.applyDatalabelsOptions(thisInstance.generateData());
		thisInstance.chartInstance = new Chart(
				thisInstance.getPlotContainer().getContext("2d"),
				{
					type: thisInstance.getType(),
					data,
					// each chart type should have default options as applyDefaultOptions method
					// which adds default options only if not specified in instance(!)
					options: thisInstance.applyDefaultOptions(data, thisInstance.getOptions()),
					plugins: thisInstance.getPlugins()
				}
		);
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
		app.showPopoverElementView(this.getContainer().find('.popoverTooltip'));
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
				$.each(jQuery.parseJSON(owners), function (key, value) {
					select.append($('<option>', {value: key}).text(value));
				});
			}
		}
	},
	registerHeaderButtons: function registerHeaderButtons() {
		var container = this.getContainer();
		var header = container.find('.dashboardWidgetHeader');
		var downloadWidget = header.find('.downloadWidget');
		var printWidget = header.find('.printWidget');
		printWidget.click(function (e) {
			var imgEl = $(this.chartInstance.jqplotToImageElem());
			var print = window.open('', 'PRINT', 'height=400,width=600');
			print.document.write('<html><head><title>' + header.find('.dashboardTitle').text() + '</title>');
			print.document.write('</head><body >');
			print.document.write($('<div>').append(imgEl.clone()).html());
			print.document.write('</body></html>');
			print.document.close(); // necessary for IE >= 10
			print.focus(); // necessary for IE >= 10*/
			setTimeout(function () {
				print.print();
				print.close();
			}, 1000);
		});
		downloadWidget.click({chart: $(this)}, function (e) {
			var imgEl = $(this.chartInstance.jqplotToImageElem());
			var a = $("<a>")
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
		container.find('.changeRecordSort').click(function (e) {
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
		AppConnector.request(params).then(
				function (data) {
					var data = jQuery(data);
					var footer = data.filter('.widgetFooterContent');
					refreshContainer.progressIndicator({'mode': 'hide'});
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
				},
				function () {
					refreshContainer.progressIndicator({'mode': 'hide'});
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
		app.registerDateRangePickerFields(dateRangeElement, {opens: "auto"});
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
			app.showSelect2ElementView(container.find('.select2noactive'));
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
				contentContainer.progressIndicator({'mode': 'hide'});
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
	 * Get data from event like mouse hover,click etc - get data which belongs to pointed element
	 *
	 * @param {event obj} e
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
	 * Get datalabels configuration - unified datalabels configuration for all chart types - might be overrided
	 *
	 * @param {type} dataset
	 * @param {type} type
	 * @returns {object}
	 */
	getDatalabelsOptions: function getDatalabelsOptions(dataset, type = 'bar') {
		let borderRadius = 2;
		switch (type) {
			case 'pie':
			case 'donut':
			case 'doughnut':
				borderRadius = 5;
				break;
		}
		return {
			font: {
				size: 11
			},
			color: 'white',
			backgroundColor: 'rgba(0,0,0,0.2)',
			borderColor: 'rgba(255,255,255,0.2)',
			borderWidth: 2,
			borderRadius: borderRadius,
			anchor: 'center',
			align: 'center',
			formatter: function (value, context) {
				if (typeof context.chart.data.datasets[context.datasetIndex].dataFormatted !== 'undefined' && typeof context.chart.data.datasets[context.datasetIndex].dataFormatted[context.dataIndex]) {
					// data presented in different format usually exists in alternative dataFormatted array
					return context.chart.data.datasets[context.datasetIndex].dataFormatted[context.dataIndex];
				}
				if (!isNaN(Number(value))) {
					return app.parseNumberToShow(value);
				}
				return value;
			},
		};
	},
	/**
	 * Apply default datalabels config
	 *
	 * @param {object} chartData from request
	 * @param {string} chartType 'bar','pie' etc..
	 * @returns {object} chartData
	 */
	applyDatalabelsOptions: function applyDatalabelsOptions(chartData, chartType) {
		if (typeof chartData === 'undefined' || typeof chartData.datasets === 'undefined' || chartData.datasets.length === 0) {
			return false;
		}
		chartData.datasets.forEach((dataset) => {
			// TODO: only if not specified!
			dataset.datalabels = this.getDatalabelsOptions(dataset, chartType);
		});
		return chartData;
	},
	/**
	 * Get tooltips configuration - this method can be overrided if needed
	 *
	 * @param {object} data - chartData
	 * @returns {object} default options
	 */
	getTooltipsOptions: function getTooltipsOptions(data) {
		return {
			tooltips: {
				callbacks: {

					label: function tooltipLabelCallback(tooltipItem, data) {
						if (typeof data.datasets[tooltipItem.datasetIndex].dataFormatted !== 'undefined' && data.datasets[tooltipItem.datasetIndex].dataFormatted[tooltipItem.index] !== 'undefined') {
							return data.datasets[tooltipItem.datasetIndex].dataFormatted[tooltipItem.index];
						}
						if (!isNaN(Number(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]))) {
							return app.parseNumberToShow(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]);
						}
						return data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
					},

					title: function tooltipTitleCallback(tooltipItems, data) {
						const tooltipItem = tooltipItems[0];
						if (typeof data.datasets[tooltipItem.datasetIndex].titlesFormatted !== 'undefined' && data.datasets[tooltipItem.datasetIndex].titlesFormatted[tooltipItem.index] !== 'undefined') {
							return data.datasets[tooltipItem.datasetIndex].titlesFormatted[tooltipItem.index];
						}
						if (!isNaN(Number(data.labels[tooltipItem.index]))) {
							return app.parseNumberToShow(data.labels[tooltipItem.index]);
						}
						return data.labels[tooltipItem.index];
					}

				}
			}
		};
	},
	/**
	 * Apply unified tooltips configuration
	 *
	 * @param {chartData} data - data from request
	 * @param {object} options - predefined options
	 * @returns {object} options
	 */
	applyDefaultTooltipsOptions: function applyDefaultTooltipsOptions(data, options = {}) {
		const defaultOptions = this.getTooltipsOptions(data);
		if (typeof options.tooltips === 'undefined') {
			options.tooltips = {};
		}
		if (typeof options.tooltips.callbacks === 'undefined') {
			options.tooltips.callbacks = {};
		}
		this.formatTooltipTitles(data);// titles are now in dataset.titlesFormatted
		if (typeof options.tooltips.callbacks.label === 'undefined') {
			options.tooltips.callbacks.label = defaultOptions.tooltips.callbacks.label;
		}
		if (typeof options.tooltips.callbacks.title === 'undefined') {
			options.tooltips.callbacks.title = defaultOptions.tooltips.callbacks.title;
		}
		return options;
	},
	/**
	 * Format tooltip titles to user number format
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
					if (!isNaN(Number(defaultLabel))) {
						defaultLabel = app.parseNumberToShow(defaultLabel);
					}
					if (typeof dataset.label !== 'undefined') {
						let label = dataset.label;
						if (!isNaN(Number(label))) {
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
	 * Placeholder for individual chart type options
	 * If you want to customize default options this is the right place - override this
	 *
	 * @returns {object} chart options
	 */
	getOptions: function getOptions() {
		return {};
	},
	/**
	 * Placeholder for individual chart type plugins
	 * You can add custom plugins for individual charts by overriding this method
	 * see: http://www.chartjs.org/docs/latest/developers/plugins.html
	 *
	 * @returns {Array} plugins
	 */
	getPlugins: function getPlugins() {
		return [];
	},
	/**
	 * Get chart type
	 * We don't wan't to override loadChart method - it is good practice
	 * so we can extend some chart type and change its type only to show data in different manner
	 *
	 * @returns {String}
	 */
	getType: function getType() {
		return 'bar';
	},
});
Vtiger_Widget_Js('YetiForce_Widget_Js', {}, {});
YetiForce_Widget_Js('YetiForce_Bar_Widget_Js', {}, {
	applyDefaultAxesLabelsConfig: function (options = {}) {
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
			if (typeof axis.ticks.autoSkip === 'undefined') {
				axis.ticks.autoSkip = false;
			}
			if (typeof axis.ticks.beginAtZero === 'undefined') {
				axis.ticks.beginAtZero = true;
			}
			axis.ticks.maxRotation = 90;
		});

		if (typeof options.scales.yAxes === 'undefined') {
			options.scales.yAxes = [{}];
		}
		options.scales.yAxes.forEach((axis) => {
			if (typeof axis.ticks === 'undefined') {
				axis.ticks = {};
			}
			if (typeof axis.ticks.callback === 'undefined') {
				axis.ticks.callback = function defaultYTicksCallback(value, index, values) {
					if (!isNaN(Number(value))) {
						return app.parseNumberToShow(value);
					}
					return value;
				}
			}
			if (typeof axis.ticks.autoSkip === 'undefined') {
				axis.ticks.autoSkip = false;
			}
			if (typeof axis.ticks.beginAtZero === 'undefined') {
				axis.ticks.beginAtZero = true;
			}
		});
		return options;
	},
	applyDefaultOptions: function (data, options = {}){
		if (typeof options.maintainAspectRatio === 'undefined') {
			options.maintainAspectRatio = false;
		}
		if (typeof options.title === 'undefined') {
			options.title = {};
		}
		if (typeof options.title.display === 'undefined') {
			options.title.display = false;
		}
		if (typeof options.legend === 'undefined') {
			options.legend = {};
		}
		if (typeof options.legend.display === 'undefined') {
			options.legend.display = false;
		}
		if (typeof options.events === 'undefined') {
			options.events = ["mousemove", "mouseout", "click", "touchstart", "touchmove", "touchend"];
		}
		this.applyDefaultTooltipsOptions(data, options);
		this.applyDefaultAxesLabelsConfig(options);
		return options;
	},
	getDatasetsMeta: function (chart) {
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
	},
	hideDatalabelsIfNeeded: function (chart) {
		const data = chart.data;
		let datasetsMeta = this.getDatasetsMeta(chart);
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
	shortenXTicks: function (data, options) {
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
			}
		});
		return options;
	},
	rotateXLabels90: function (data, options) {
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
	},
	beforeDraw: function (chart) {
		this.hideDatalabelsIfNeeded(chart);
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
						const ctx = metaDataItem._xScale.ctx;
						const categoryWidth = (metaDataItem._xScale.width / dataset._meta[prop].data.length) * metaDataItem._xScale.options.categoryPercentage;
						const fullWidth = ctx.measureText(label).width;
						if (categoryWidth < fullWidth) {
							const shortened = label.substr(0, 10) + "...";
							const shortenedWidth = ctx.measureText(shortened).width;
							if (categoryWidth < shortenedWidth) {
								chart.options = this.rotateXLabels90(chart.data, chart.options);
								chart.options = this.shortenXTicks(chart.data, chart.options);
							} else {
								chart.options = this.shortenXTicks(chart.data, chart.options);
							}
							if (!dataset._updated) {
								dataset._updated = true;
								chart.update();
								// recalculate positions for smooth animation
								dataset._meta[prop].data.forEach((metaDataItem, dataIndex) => {
									metaDataItem._view.x = metaDataItem._xScale.getPixelForValue(index, dataIndex);
									metaDataItem._view.base = metaDataItem._xScale.getBasePixel();
									metaDataItem._view.width = (metaDataItem._xScale.width / dataset._meta[prop].data.length) * metaDataItem._xScale.options.categoryPercentage * metaDataItem._xScale.options.barPercentage;
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
	getPlugins: function () {
		const thisInstance = this;
		return[
			{
				beforeDraw: thisInstance.beforeDraw.bind(thisInstance),
			}
		]
	},
	loadChart: function () {
		const thisInstance = this;
		const data = thisInstance.applyDatalabelsOptions(thisInstance.generateData());
		thisInstance.chartInstance = new Chart(
				thisInstance.getPlotContainer().getContext("2d"),
				{
					type: thisInstance.getType(),
					data,
					options: thisInstance.applyDefaultOptions(data, thisInstance.getOptions()),
					plugins: thisInstance.getPlugins()
				}
		);
		thisInstance.hideDatalabelsIfNeeded(thisInstance.chartInstance);
	},
});
YetiForce_Bar_Widget_Js('YetiForce_Barchat_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_Horizontal_Widget_Js', {}, {
	getType: function () {
		return 'horizontalBar';
	},
	hideDatalabelsIfNeeded: function (chart) {
		const data = chart.data;
		let datasetsMeta = this.getDatasetsMeta(chart);
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
						console.log('hide label', dataItem, model, labelWidth, labelHeight, barHeight);
						dataItem.$datalabels._model = null;
					} else {
						dataItem.$datalabels._model = model;
					}
				}
			}
		}

	},
	shortenYTicks: function shortenYTicks(data, options) {
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
	},
	beforeDraw: function (chart) {
		this.hideDatalabelsIfNeeded(chart);
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
							chart.options = this.shortenYTicks(chart.data, chart.options);
							if (!dataset._updated) {
								dataset._updated = true;
								chart.update();
								// recalculate positions for smooth animation
								dataset._meta[prop].data.forEach((metaDataItem, dataIndex) => {
									metaDataItem._view.x = metaDataItem._xScale.getPixelForValue(index, dataIndex);
									metaDataItem._view.base = metaDataItem._xScale.getBasePixel();
									metaDataItem._view.width = (metaDataItem._xScale.width / dataset._meta[prop].data.length) * metaDataItem._xScale.options.categoryPercentage * metaDataItem._xScale.options.barPercentage;
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
		loadMoreHandler.click(function () {
			var parent = thisInstance.getContainer();
			var element = parent.find('a[name="drefresh"]');
			var url = element.data('url');
			var params = url;
			var widgetFilters = parent.find('.widgetFilter');
			if (widgetFilters.length > 0) {
				params = {url: url, data: {}};
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
					params = {url: url, data: {}};
				}
				params.data = jQuery.extend(params.data, thisInstance.getFilterData())
			}

			// Next page.
			params.data['page'] = loadMoreHandler.data('nextpage');
			var refreshContainer = parent.find('.dashboardWidgetContent');
			refreshContainer.progressIndicator();
			AppConnector.request(params).then(function (data) {
				refreshContainer.progressIndicator({'mode': 'hide'});
				loadMoreHandler.replaceWith(data);
				thisInstance.registerLoadMore();
			}, function () {
				refreshContainer.progressIndicator({'mode': 'hide'});
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
	applyDefaultOptions: function (data, options = {}){
		if (typeof options.maintainAspectRatio === 'undefined') {
			options.maintainAspectRatio = false;
		}
		if (typeof options.title === 'undefined') {
			options.title = {};
		}
		if (typeof options.title.display === 'undefined') {
			options.title.display = false;
		}
		if (typeof options.legend === 'undefined') {
			options.legend = {};
		}
		if (typeof options.legend.display === 'undefined') {
			options.legend.display = false;
		}
		if (typeof options.events === 'undefined') {
			options.events = ["mousemove", "mouseout", "click", "touchstart", "touchmove", "touchend"];
		}
		if (typeof options.sort === 'undefined') {
			options.sort = 'data-desc';
		}
		if (typeof options.scales === 'undefined') {
			options.scales = {};
		}
		if (typeof options.scales.yAxes === 'undefined') {
			options.scales.yAxes = [{}];
		}
		options.scales.yAxes.forEach((axis) => {
			axis.display = true;
		});
		this.applyDefaultTooltipsOptions(data, options);
		return options;
	},
	applyDatalabelsOptions: function (data, type) {
		data.datasets.forEach((dataset) => {
			dataset.datalabels = {display: false};
		});
		return data;
	},
});
YetiForce_Widget_Js('YetiForce_Pie_Widget_Js', {}, {
	getType: function getType() {
		return 'pie';
	},
	applyDefaultOptions: function (data, options = {}){
		if (typeof options.maintainAspectRatio === 'undefined') {
			options.maintainAspectRatio = false;
		}
		if (typeof options.title === 'undefined') {
			options.title = {};
		}
		if (typeof options.title.display === 'undefined') {
			options.title.display = false;
		}
		if (typeof options.legend === 'undefined') {
			options.legend = {};
		}
		if (typeof options.legend.display === 'undefined') {
			options.legend.display = true;
		}
		if (typeof options.events === 'undefined') {
			options.events = ["mousemove", "mouseout", "click", "touchstart", "touchmove", "touchend"];
		}
		if (typeof options.cutoutPercentage === 'undefined') {
			options.cutoutPercentage = 0;
		}
		this.applyDefaultTooltipsOptions(data, options);
		return options;
	},
});
YetiForce_Pie_Widget_Js('YetiForce_Donut_Widget_Js', {}, {
	getType: function getType() {
		return 'doughnut';
	},
	applyDefaultOptions: function (data, options = {}){
		if (typeof options.maintainAspectRatio === 'undefined') {
			options.maintainAspectRatio = false;
		}
		if (typeof options.title === 'undefined') {
			options.title = {};
		}
		if (typeof options.title.display === 'undefined') {
			options.title.display = false;
		}
		if (typeof options.legend === 'undefined') {
			options.legend = {};
		}
		if (typeof options.legend.display === 'undefined') {
			options.legend.display = true;
		}
		if (typeof options.events === 'undefined') {
			options.events = ["mousemove", "mouseout", "click", "touchstart", "touchmove", "touchend"];
		}
		if (typeof options.cutoutPercentage === 'undefined') {
			options.cutoutPercentage = 50;
		}
		this.applyDefaultTooltipsOptions(data, options);
		return options;
	},
});
YetiForce_Donut_Widget_Js('YetiForce_Axis_Widget_Js', {}, {});
YetiForce_Widget_Js('YetiForce_Bardivided_Widget_Js', {}, {
	/**
	 * Function which will give chart related Data
	 */
	generateData: function () {
		var container = this.getContainer();
		var jData = container.find('.widgetData').val();
		var data = JSON.parse(jData);
		return data;
	},
	loadChart: function () {
		var data = this.generateData();
		var series = [];
		$.each(data['divided'], function (index, value) {
			series[index] = {label: value};
		});
		if (data['chartData'].length > 0) {
			this.chartInstance = this.getPlotContainer(false).jqplot(data['chartData'], {
				stackSeries: true,
				captureRightClick: true,
				seriesDefaults: {
					renderer: jQuery.jqplot.BarRenderer,
					rendererOptions: {
						highlightMouseOver: true,
						varyBarColor: true
					},
					pointLabels: {show: true}
				},
				series: series,
				axes: {
					xaxis: {
						renderer: $.jqplot.CategoryAxisRenderer,
						tickRenderer: $.jqplot.CanvasAxisTickRenderer,
						ticks: data['group'],
						tickOptions: {
							angle: -65,
						}
					}
				},
				legend: {
					show: true,
					location: 'e'
				}
			});
		}
	},
	registerSectionClick: function () {
		var data = this.generateData();
		var links = data['links'];
		this.getContainer().off('jqplotDataClick').on('jqplotDataClick', function (ev, seriesIndex, pointIndex, data) {
			var url = links[seriesIndex][pointIndex];
			window.location.href = url;
		});
	},
});

YetiForce_Barchat_Widget_Js('YetiForce_Line_Widget_Js', {}, {
	loadChart: function () {
		var data = this.generateChartData();
		if (data['chartData'][0].length > 0) {
			this.getPlotContainer(false).jqplot(data['chartData'], {
				title: data['title'],
				legend: {
					show: false,
					labels: data['labels'],
					location: 'ne',
					showSwatch: true,
					showLabels: true,
					placement: 'outside'
				},
				seriesDefaults: {
					pointLabels: {
						show: true
					}
				},
				axes: {
					xaxis: {
						min: 0,
						pad: 1,
						renderer: $.jqplot.CategoryAxisRenderer,
						ticks: data['labels'],
						tickOptions: {
							formatString: '%b %#d'
						}
					}
				},
				cursor: {
					show: true
				}
			});
		}
	}
});
YetiForce_Barchat_Widget_Js('YetiForce_Lineplain_Widget_Js', {}, {
	loadChart: function () {
		var data = this.generateChartData();
		if (data['chartData'][0].length > 0) {
			this.getPlotContainer(false).jqplot(data['chartData'], {
				title: data['title'],
				axesDefaults: {
					labelRenderer: $.jqplot.CanvasAxisLabelRenderer
				},
				seriesDefaults: {
					rendererOptions: {
						smooth: true
					}
				},
				axes: {
					xaxis: {
						min: 0,
						pad: 0,
						renderer: $.jqplot.CategoryAxisRenderer,
						ticks: data['labels'],
						tickOptions: {
							formatString: '%b %#d'
						}
					}
				},
				cursor: {
					show: true
				}
			});
		}
	}
});
YetiForce_Widget_Js('YetiForce_MultiBarchat_Widget_Js', {
	/**
	 * Function which will give char related Data like data , x labels and legend labels as map
	 */
	getCharRelatedData: function () {
		var container = this.getContainer();
		var data = container.find('.widgetData').val();
		var users = [];
		var stages = [];
		var count = [];
		for (var i = 0; i < data.length; i++) {
			if ($.inArray(data[i].last_name, users) == -1) {
				users.push(data[i].last_name);
			}
			if ($.inArray(data[i].sales_stage, stages) == -1) {
				stages.push(data[i].sales_stage);
			}
		}

		for (var j in stages) {
			var salesStageCount = [];
			for (i in users) {
				var salesCount = 0;
				for (var k in data) {
					var userData = data[k];
					if (userData.sales_stage == stages[j] && userData.last_name == users[i]) {
						salesCount = parseInt(userData.count);
						break;
					}
				}
				salesStageCount.push(salesCount);
			}
			count.push(salesStageCount);
		}
		return {
			'data': count,
			'ticks': users,
			'labels': stages
		}
	},
	loadChart: function () {
		var chartRelatedData = this.getCharRelatedData();
		var chartData = chartRelatedData.data;
		var ticks = chartRelatedData.ticks;
		var labels = chartRelatedData.labels;
		$.jqplot.CanvasAxisTickRenderer.pt2px = 2.4;
		this.getPlotContainer(false).jqplot(chartData, {
			stackSeries: true,
			captureRightClick: true,
			seriesDefaults: {
				renderer: $.jqplot.BarRenderer,
				rendererOptions: {
					// Put a 30 pixel margin between bars.
					barMargin: 10,
					// Highlight bars when mouse button pressed.
					// Disables default highlighting on mouse over.
					highlightMouseDown: true,
					highlightMouseOver: true
				},
				pointLabels: {show: true, hideZeros: true}
			},
			axes: {
				xaxis: {
					renderer: $.jqplot.CategoryAxisRenderer,
					tickRenderer: $.jqplot.CanvasAxisTickRenderer,
					tickOptions: {
						angle: -45,
						pt2px: 4.0
					},
					ticks: ticks
				},
				yaxis: {
					// Don't pad out the bottom of the data range.  By default,
					// axes scaled as if data extended 10% above and below the
					// actual range to prevent data points right on grid boundaries.
					// Don't want to do that here.
					padMin: 0,
					min: 0
				}
			},
			legend: {
				show: true,
				location: 'e',
				renderer: $.jqplot.EnhancedLegendRenderer,
				placement: 'outside',
				labels: labels
			}
		});
	}
});
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
YetiForce_Widget_Js('YetiForce_Notebook_Widget_Js', {
}, {
	// Override widget specific functions.
	postLoadWidget: function () {
		this.reinitNotebookView();
	},
	reinitNotebookView: function () {
		var self = this;
		app.showScrollBar(jQuery('.dashboard_notebookWidget_viewarea', this.container), {'height': '200px'});
		jQuery('.dashboard_notebookWidget_edit', this.container).click(function () {
			self.editNotebookContent();
		});
		jQuery('.dashboard_notebookWidget_save', this.container).click(function () {
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
			refreshContainer.progressIndicator({'mode': 'hide'});
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
		return {'chartData': [[[data['result'], data['all']]]], 'yMaxValue': data['maxValue'], 'labels': ''};
	},
	loadChart: function () {
		var data = this.generateChartData();
		this.getPlotContainer(false).jqplot(data['chartData'], {
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
	getOptions: function () {
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
	weekDaysArray: {
		Sunday: 0,
		Monday: 1,
		Tuesday: 2,
		Wednesday: 3,
		Thursday: 4,
		Friday: 5,
		Saturday: 6
	},
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
		var defaultFirstDay = jQuery('#start_day').val();
		var convertedFirstDay = thisInstance.weekDaysArray[defaultFirstDay];
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
				app.vtranslate('JS_NOVEMBER'), app.vtranslate('JS_DECEMBER')],
			monthNamesShort: [app.vtranslate('JS_JAN'), app.vtranslate('JS_FEB'), app.vtranslate('JS_MAR'),
				app.vtranslate('JS_APR'), app.vtranslate('JS_MAY'), app.vtranslate('JS_JUN'), app.vtranslate('JS_JUL'),
				app.vtranslate('JS_AUG'), app.vtranslate('JS_SEP'), app.vtranslate('JS_OCT'), app.vtranslate('JS_NOV'),
				app.vtranslate('JS_DEC')],
			dayNames: [app.vtranslate('JS_SUNDAY'), app.vtranslate('JS_MONDAY'), app.vtranslate('JS_TUESDAY'),
				app.vtranslate('JS_WEDNESDAY'), app.vtranslate('JS_THURSDAY'), app.vtranslate('JS_FRIDAY'),
				app.vtranslate('JS_SATURDAY')],
			dayNamesShort: [app.vtranslate('JS_SUN'), app.vtranslate('JS_MON'), app.vtranslate('JS_TUE'),
				app.vtranslate('JS_WED'), app.vtranslate('JS_THU'), app.vtranslate('JS_FRI'),
				app.vtranslate('JS_SAT')],
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
				.mouseenter(function () {
					jQuery('<span class="plus pull-left fas fa-plus"></span>')
							.prependTo($(this))
				}).mouseleave(function () {
			$(this).find(".plus").remove();
		});
		thisInstance.getCalendarView().find("td.fc-day-top").click(function () {
			var date = $(this).data('date');
			var params = {noCache: true};
			params.data = {date_start: date, due_date: date};
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
		var formatDate = app.getMainParams('userDateFormat').toUpperCase();
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
			var paramCache = {owner: user, customFilter: customFilter, start: start_date};
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
			thisInstance.getCalendarView().find(".cell-calendar a").click(function () {
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
		refreshContainer.progressIndicator({'mode': 'hide'});
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
	loadChart: function () {
		const thisInstance = this;
		const options = {
			maintainAspectRatio: false,
			title: {
				display: false
			},
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
			events: ["mousemove", "mouseout", "click", "touchstart", "touchmove", "touchend"],
		};
		const data = thisInstance.generateData();
		thisInstance.applyDatalabelsOptions(data);
		thisInstance.applyDefaultAxesLabelsConfig(options);
		data.datasets.forEach((dataset) => {
			// https://chartjs-plugin-datalabels.netlify.com/options.html
			dataset.datalabels.formatter = function datalabelsFormatter(value, context) {
				return app.formatToHourText(value);
			};
		});
		thisInstance.chartInstance = new Chart(
				thisInstance.getPlotContainer().getContext("2d"),
				{
					type: 'bar',
					data: data,
					options: options,
					plugins: thisInstance.getPlugins()
				}
		);
		thisInstance.hideDatalabelsIfNeeded(thisInstance.chartInstance);
	}
});
YetiForce_Bar_Widget_Js('YetiForce_Leadsbysource_Widget_Js', {}, {});
YetiForce_Pie_Widget_Js('YetiForce_Closedticketsbypriority_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_Closedticketsbyuser_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_Opentickets_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_Accountsbyindustry_Widget_Js', {}, {});
YetiForce_Funnel_Widget_Js('YetiForce_Estimatedvaluebystatus_Widget_Js', {}, {});
YetiForce_Barchat_Widget_Js('YetiForce_Notificationsbysender_Widget_Js', {}, {});
YetiForce_Barchat_Widget_Js('YetiForce_Notificationsbyrecipient_Widget_Js', {}, {});
YetiForce_Bar_Widget_Js('YetiForce_Teamsestimatedsales_Widget_Js', {}, {
	generateChartData: function () {
		var thisInstance = this;
		var container = this.getContainer();
		var jData = container.find('.widgetData').val();
		var data = JSON.parse(jData);
		var chartData = [[], [], [], []];
		var yMaxValue = 0;
		if (data.hasOwnProperty('compare')) {
			for (var index in data) {
				var parseData = thisInstance.parseChartData(data[index], chartData);
				chartData[0].push(parseData[0]);
				chartData[3].push(parseData[3]);
				chartData = [chartData[0], parseData[1], parseData[2], chartData[3], ['#CC6600', '#208CB3']];
			}
		} else {
			var parseData = thisInstance.parseChartData(data, chartData);
			chartData = [[parseData[0]], parseData[1], parseData[2], [parseData[3]], ['#208CB3']];
		}
		var yMaxValue = chartData[1];
		yMaxValue = yMaxValue + 2 + (yMaxValue / 100) * 25;
		return {'chartData': chartData[0], 'yMaxValue': yMaxValue, 'labels': chartData[2], data_labels: chartData[3], placement: 'inside', location: 'n', colors: chartData[4]};
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
