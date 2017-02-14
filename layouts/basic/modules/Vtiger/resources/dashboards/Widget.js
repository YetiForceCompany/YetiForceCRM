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
	getInstance: function (container, widgetName, moduleName) {
		if (typeof moduleName == 'undefined') {
			moduleName = app.getModuleName();
		}
		var widgetClassName = widgetName.toCamelCase();
		var moduleClass = window[moduleName + "_" + widgetClassName + "_Widget_Js"];
		var fallbackClass = window["Vtiger_" + widgetClassName + "_Widget_Js"];
		var yetiClass = window["YetiForce_" + widgetClassName + "_Widget_Js"];
		var basicClass = Vtiger_Widget_Js;
		if (typeof moduleClass != 'undefined') {
			var instance = new moduleClass(container);
		} else if (typeof fallbackClass != 'undefined') {
			var instance = new fallbackClass(container);
		} else if (typeof yetiClass != 'undefined') {
			var instance = new yetiClass(container);
		} else {
			var instance = new basicClass(container);
		}
		return instance;
	}
}, {
	container: false,
	plotContainer: false,
	plotInstance: false,
	chartData: [],
	paramCache: false,
	init: function (container) {
		this.setContainer(jQuery(container));
		this.registerWidgetPostLoadEvent(container);
		this.registerWidgetPostRefreshEvent(container);
		this.registerCache(container);
	},
	getContainer: function () {
		return this.container;
	},
	setContainer: function (element) {
		this.container = element;
		return this;
	},
	isEmptyData: function () {
		var container = this.getContainer();
		return (container.find('.noDataMsg').length > 0) ? true : false;
	},
	getUserDateFormat: function () {
		return jQuery('#userDateFormat').val();
	},
	getPlotContainer: function (useCache) {
		if (typeof useCache == 'undefined') {
			useCache = false;
		}
		if (this.plotContainer == false || !useCache) {
			var container = this.getContainer();
			this.plotContainer = container.find('.widgetChartContainer');
		}
		return this.plotContainer;
	},
	registerRecordsCount: function () {
		var thisInstance = this;
		var recordsCountBtn = thisInstance.getContainer().find('.recordCount');
		recordsCountBtn.on('click', function () {
			var url = recordsCountBtn.data('url');
			AppConnector.request(url).then(function (response) {
				recordsCountBtn.find('.count').html(response.result.totalCount);
				recordsCountBtn.find('span:not(.count)').addClass('hide');
				recordsCountBtn.find('a').removeClass('hide');
			});
		});
	},
	restrictContentDrag: function () {
		this.getContainer().on('mousedown.draggable', function (e) {
			var element = jQuery(e.target);
			var isHeaderElement = element.closest('.dashboardWidgetHeader').length > 0 ? true : false;
			if (isHeaderElement) {
				return;
			}
			//Stop the event propagation so that drag will not start for contents
			e.stopPropagation();
		})
	},
	convertToDateRangePicketFormat: function (userDateFormat) {
		switch (userDateFormat) {
			case 'yyyy-mm-dd':
				return 'yyyy-MM-dd';
				break;
			case 'mm-dd-yyyy':
				return 'MM-dd-yyyy';
				break;
			case 'dd-mm-yyyy':
				return 'dd-MM-yyyy';
				break;
			case 'yyyy.mm.dd':
				return 'yyyy.MM.dd';
				break;
			case 'mm.dd.yyyy':
				return 'MM.dd.yyyy';
				break;
			case 'dd.mm.yyyy':
				return 'dd.MM.yyyy';
				break;
			case 'yyyy/mm/dd':
				return 'yyyy/MM/dd';
				break;
			case 'mm/dd/yyyy':
				return 'MM/dd/yyyy';
				break;
			case 'dd/mm/yyyy':
				return 'dd/MM/yyyy';
				break;
		}
	},
	generateData: function () {
		var thisInstance = this;
		var container = thisInstance.getContainer();
		var jData = container.find('.widgetData').val();
		var data = JSON.parse(jData);
		var chartData = [];
		for (var index in data) {
			chartData.push(data[index]);
			thisInstance.chartData[data[index].id] = data[index];
		}
		return {'chartData': chartData};
	},
	loadChart: function () {

	},
	positionNoDataMsg: function () {
		var container = this.getContainer();
		var widgetContentsContainer = container.find('.dashboardWidgetContent');
		var noDataMsgHolder = widgetContentsContainer.find('.noDataMsg');
		noDataMsgHolder.position({
			'my': 'center center',
			'at': 'center center',
			'of': widgetContentsContainer
		})
	},
	//Place holdet can be extended by child classes and can use this to handle the post load
	postLoadWidget: function () {
		if (!this.isEmptyData()) {
			this.loadChart();
		} else {
			this.positionNoDataMsg();
		}
		this.registerSectionClick();
		this.registerFilter();
		this.registerFilterChangeEvent();
		this.restrictContentDrag();
		app.showBtnSwitch(this.getContainer().find('.switchBtn'));
		this.registerWidgetSwitch();
		this.registerChangeSorting();
		this.registerLoadMore();
	},
	postRefreshWidget: function () {
		if (!this.isEmptyData()) {
			this.loadChart();
		} else {
			this.positionNoDataMsg();
		}
		this.registerSectionClick();
		this.registerLoadMore();
	},
	setSortingButton: function (currentElement) {
		if (currentElement.length) {
			var container = this.getContainer();
			var drefresh = container.find('a[name="drefresh"]');
			var url = drefresh.data('url');
			url = url.replace('&sortorder=desc', '');
			url = url.replace('&sortorder=asc', '');
			url += '&sortorder=';
			var sort = currentElement.data('sort');
			var sortorder = 'desc';
			var icon = 'glyphicon-sort-by-attributes-alt';
			if (sort == 'desc') {
				sortorder = 'asc';
				icon = 'glyphicon-sort-by-attributes';
			}
			currentElement.data('sort', sortorder);
			currentElement.attr('title', currentElement.data(sortorder));
			currentElement.attr('alt', currentElement.data(sortorder));
			url += sortorder;
			var glyphicon = currentElement.find('.glyphicon');
			glyphicon.removeClass().addClass('glyphicon').addClass(icon);
			drefresh.data('url', url);
		}
	},
	/**
	 * Change of widget entries sorting
	 * @license licenses/License.html
	 * @package YetiForce.Dashboards
	 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
	 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
	 */
	registerChangeSorting: function () {
		var thisInstance = this;
		var container = this.getContainer();
		thisInstance.setSortingButton(container.find('.changeRecordSort'));
		container.find('.changeRecordSort').click(function (e) {
			var drefresh = container.find('a[name="drefresh"]');
			thisInstance.setSortingButton(jQuery(e.currentTarget));
			drefresh.click();
		});
	},
	registerWidgetSwitch: function () {
		var thisInstance = this;
		var switchButtons = this.getContainer().find('.dashboardWidgetHeader .switchBtnReload');
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
	setUrlSwitch: function (switchButtons) {
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
	getFilterData: function () {
		return {};
	},
	refreshWidget: function () {
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
			params.data = {}
			widgetFilters.each(function (index, domElement) {
				var widgetFilter = jQuery(domElement);
				if (widgetFilter.is('.dateRange')) {
					var dateRangeVal = widgetFilter.val();
					//If not value exists for date field then dont send the value
					if (dateRangeVal.length <= 0) {
						return true;
					}
					var name = widgetFilter.attr('name');
					var dateRangeValComponents = dateRangeVal.split(',');
					params.data[name] = {};
					params.data[name].start = dateRangeValComponents[0];
					params.data[name].end = dateRangeValComponents[1];
				} else {
					var filterType = widgetFilter.attr('type');
					var filterName = widgetFilter.attr('name');
					if ('checkbox' == filterType) {
						var filterValue = widgetFilter.is(':checked');
						params.data[filterName] = filterValue;
					} else {
						var filterValue = widgetFilter.val();
						params.data[filterName] = filterValue;
					}
				}
			});
		}
		var filterData = this.getFilterData();
		if (!jQuery.isEmptyObject(filterData)) {
			if (typeof params == 'string') {
				url = params;
				params = {};
				params.url = url
				params.data = {};
			}
			params.data = jQuery.extend(params.data, this.getFilterData())
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
					contentContainer.html(data).trigger(Vtiger_Widget_Js.widgetPostRefereshEvent);
				},
				function () {
					refreshContainer.progressIndicator({'mode': 'hide'});
				}
		);
	},
	registerFilter: function () {
		var thisInstance = this;
		var container = this.getContainer();
		var dateRangeElement = container.find('input.dateRange');
		var dateChanged = false;
		if (dateRangeElement.length <= 0) {
			return;
		}
		var customParams = {
			calendars: 3,
			mode: 'range',
			className: 'rangeCalendar',
			onChange: function (formated) {
				dateChanged = true;
				var element = jQuery(this).data('datepicker').el;
				jQuery(element).val(formated);
			},
			onHide: function () {
				if (dateChanged) {
					container.find('a[name="drefresh"]').trigger('click');
					dateChanged = false;
				}
			},
			onBeforeShow: function (elem) {
				jQuery(elem).css('z-index', '3');
			}
		}
		dateRangeElement.addClass('dateField').attr('data-date-format', thisInstance.getUserDateFormat());
		app.registerEventForDatePickerFields(dateRangeElement, false, customParams);
	},
	registerFilterChangeEvent: function () {
		this.getContainer().on('change', '.widgetFilter', function (e) {
			var widgetContainer = jQuery(e.currentTarget).closest('li');
			widgetContainer.find('a[name="drefresh"]').trigger('click');
		})
	},
	registerWidgetPostLoadEvent: function (container) {
		var thisInstance = this;
		container.on(Vtiger_Widget_Js.widgetPostLoadEvent, function (e) {
			thisInstance.postLoadWidget();
		})
	},
	registerWidgetPostRefreshEvent: function (container) {
		var thisInstance = this;
		container.on(Vtiger_Widget_Js.widgetPostRefereshEvent, function (e) {
			thisInstance.postRefreshWidget();
		});
	},
	registerSectionClick: function () {
	},
	registerLoadMore: function () {
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
	setFilterToCache: function (url, data) {
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
	registerCache: function (container) {
		if (container.data('cache') == 1) {
			this.paramCache = true;
		}
	}
});

Vtiger_Widget_Js('Vtiger_History_Widget_Js', {}, {
	postLoadWidget: function () {
		this._super();
		var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
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


Vtiger_Widget_Js('Vtiger_Funnel_Widget_Js', {}, {
	postLoadWidget: function () {
		this._super();
		var thisInstance = this;

		this.getContainer().on('jqplotDataClick', function (ev, gridpos, datapos, neighbor, plot) {
			var jData = thisInstance.getContainer().find('.widgetData').val();
			var data = JSON.parse(jData);
			var linkUrl = data[datapos][3];
			if (linkUrl)
				window.location.href = linkUrl;
		});

		this.getContainer().on("jqplotDataHighlight", function (evt, seriesIndex, pointIndex, neighbor) {
			$('.jqplot-event-canvas').css('cursor', 'pointer');
		});
		this.getContainer().on("jqplotDataUnhighlight", function (evt, seriesIndex, pointIndex, neighbor) {
			$('.jqplot-event-canvas').css('cursor', 'auto');
		});
	},
	loadChart: function () {
		var container = this.getContainer();
		var data = container.find('.widgetData').val();
		var dataInfo = JSON.parse(data);
		if (dataInfo.length > 0) {
			this.getPlotContainer(false).jqplot([dataInfo], {
				seriesDefaults: {
					renderer: jQuery.jqplot.FunnelRenderer,
					rendererOptions: {
						sectionMargin: 0,
						widthRatio: 0.1,
						showDataLabels: true,
						dataLabelThreshold: 0,
						dataLabels: 'value',
						highlightMouseDown: true
					}
				},
				legend: {
					show: true,
					location: 'e',
				}
			});
			this.registerSectionClick();
		}
	},
	registerSectionClick: function () {
		var container = this.getContainer();
		var data = container.find('.widgetData').val();
		var dataInfo = JSON.parse(data);
		this.getContainer().on('jqplotDataClick', function (ev, seriesIndex, pointIndex, arguments) {
			var url = dataInfo[pointIndex][2];
			window.location.href = url;
		});
	}
});



Vtiger_Widget_Js('Vtiger_Pie_Widget_Js', {}, {
	/**
	 * Function which will give chart related Data
	 */
	generateData: function () {
		var container = this.getContainer();
		var jData = container.find('.widgetData').val();
		var data = JSON.parse(jData);
		var chartData = [];
		for (var index in data) {
			var row = data[index];
			var rowData = [row.last_name, row.id];
			chartData.push(rowData);
		}
		return {'chartData': chartData};
	},
	loadChart: function () {
		var chartData = this.generateData();
		if (chartData['chartData'].length > 0) {
			this.getPlotContainer(false).jqplot([chartData['chartData']], {
				seriesDefaults: {
					renderer: jQuery.jqplot.PieRenderer,
					rendererOptions: {
						showDataLabels: true,
						dataLabels: 'value'
					}
				},
				legend: {
					show: true,
					location: 'e'
				},
				title: chartData['title']
			});
			this.registerSectionClick();
		}
	},
	registerSectionClick: function () {
		var container = this.getContainer();
		var data = container.find('.widgetData').val();
		var dataInfo = JSON.parse(data);
		this.getContainer().on('jqplotDataClick', function (ev, seriesIndex, pointIndex, arguments) {
			var url = dataInfo[pointIndex][2];
			window.location.href = url;
		});
	}
});


Vtiger_Widget_Js('Vtiger_Barchat_Widget_Js', {}, {
	generateChartData: function () {
		var container = this.getContainer();
		var jData = container.find('.widgetData').val();
		var data = JSON.parse(jData);
		var chartData = [];
		var xLabels = new Array();
		var yMaxValue = 0;
		for (var index in data) {
			var row = data[index];
			row[0] = parseInt(row[0]);
			xLabels.push(app.getDecodedValue(row[1]))
			chartData.push(row[0]);
			if (parseInt(row[0]) > yMaxValue) {
				yMaxValue = parseInt(row[0]);
			}
		}
		// yMaxValue Should be 25% more than Maximum Value
		yMaxValue = yMaxValue + 2 + (yMaxValue / 100) * 25;
		return {'chartData': [chartData], 'yMaxValue': yMaxValue, 'labels': xLabels};
	},
	loadChart: function () {
		var isColored = false;
		var container = this.getContainer();
		var isColoredInput = container.find('.color');
		if (isColoredInput.length) {
			isColored = isColoredInput.val() == 0 ? false : true;
		}
		var data = this.generateChartData();
		if (data['chartData'][0].length > 0) {
			this.getPlotContainer(false).jqplot(data['chartData'], {
				title: data['title'],
				animate: !$.jqplot.use_excanvas,
				seriesDefaults: {
					renderer: jQuery.jqplot.BarRenderer,
					rendererOptions: {
						showDataLabels: true,
						dataLabels: 'value',
						barDirection: 'vertical',
						varyBarColor: isColored
					},
					pointLabels: {show: true, edgeTolerance: -15}
				},
				axes: {
					xaxis: {
						tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
						renderer: jQuery.jqplot.CategoryAxisRenderer,
						ticks: data['labels'],
						tickOptions: {
							angle: -45,
							labelPosition: 'auto'
						}
					},
					yaxis: {
						min: 0,
						max: data['yMaxValue'],
						tickOptions: {
							formatString: '%d'
						},
						pad: 1.2
					}
				},
				legend: {
					show: (data['data_labels']) ? true : false,
					location: 'e',
					placement: 'outside',
					showLabels: (data['data_labels']) ? true : false,
					showSwatch: (data['data_labels']) ? true : false,
					labels: data['data_labels']
				}
			});
			this.registerSectionClick();
		}
	},
	registerSectionClick: function () {
		var container = this.getContainer();
		var data = container.find('.widgetData').val();
		var dataInfo = JSON.parse(data);
		this.getContainer().on('jqplotDataClick', function (ev, seriesIndex, pointIndex, arguments) {
			var url = dataInfo[pointIndex][2];
			window.location.href = url;
		});
	}
});
Vtiger_Barchat_Widget_Js('Vtiger_Horizontal_Widget_Js', {}, {
	loadChart: function () {
		var isColored = false;
		var container = this.getContainer();
		var isColoredInput = container.find('.color');
		if (isColoredInput.length) {
			isColored = isColoredInput.val() == 0 ? false : true;
		}
		var data = this.generateChartData();
		this.getPlotContainer(false).jqplot(data['chartData'], {
			title: data['title'],
			animate: !$.jqplot.use_excanvas,
			seriesDefaults: {
				renderer: $.jqplot.BarRenderer,
				showDataLabels: true,
				pointLabels: {show: true, location: 'e', edgeTolerance: -15},
				shadowAngle: 135,
				rendererOptions: {
					barDirection: 'horizontal',
					varyBarColor: isColored
				}
			},
			axes: {
				yaxis: {
					tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
					renderer: jQuery.jqplot.CategoryAxisRenderer,
					ticks: data['labels'],
					tickOptions: {
						angle: -45
					}
				}
			},
			legend: {
				show: false,
				location: 'e',
				placement: 'outside',
				showSwatch: true,
				showLabels: true,
				labels: data['data_labels']
			}
		});
		this.registerSectionClick();
	}
});
Vtiger_Barchat_Widget_Js('Vtiger_Line_Widget_Js', {}, {
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
Vtiger_Widget_Js('Vtiger_MultiBarchat_Widget_Js', {
	/**
	 * Function which will give char related Data like data , x labels and legend labels as map
	 */
	getCharRelatedData: function () {
		var container = this.getContainer();
		var data = container.find('.widgetData').val();
		var users = new Array();
		var stages = new Array();
		var count = new Array();
		for (var i = 0; i < data.length; i++) {
			if ($.inArray(data[i].last_name, users) == -1) {
				users.push(data[i].last_name);
			}
			if ($.inArray(data[i].sales_stage, stages) == -1) {
				stages.push(data[i].sales_stage);
			}
		}

		for (j in stages) {
			var salesStageCount = new Array();
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
				placement: 'outside',
				labels: labels
			}
		});
	}
});

// NOTE Widget-class name camel-case convention
Vtiger_Widget_Js('Vtiger_Minilist_Widget_Js', {}, {
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
Vtiger_Widget_Js('YetiForce_Charts_Widget_Js', {}, {
	loadChart: function () {
		var container = this.getContainer();
		var chartType = container.find('[name="typeChart"]').val();
		var chartClassName = chartType.toCamelCase();
		var chartClass = window["Report_" + chartClassName + "_Js"];
		var instance = false;
		if (typeof chartClass != 'undefined') {
			instance = new chartClass();
			instance.setContainer(container);
			instance.loadChart();
			instance.postInitializeCalls();
		}
	}
});

/* Notebook Widget */
Vtiger_Widget_Js('Vtiger_Notebook_Widget_Js', {
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

Vtiger_Widget_Js('Vtiger_KpiBarchat_Widget_Js', {}, {
	generateChartData: function () {
		var container = this.getContainer();
		var jData = container.find('.widgetData').val();
		var data = JSON.parse(jData);
		var chartData = [];
		var xLabels = new Array();
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

Vtiger_Widget_Js('YetiForce_Pie_Widget_Js', {}, {
	loadChart: function () {
		var thisInstance = this;
		var chartData = thisInstance.generateData();
		thisInstance.plotInstance = $.plot(thisInstance.getPlotContainer(false), chartData['chartData'], {
			series: {
				pie: {
					show: true,
					label: {
						formatter: thisInstance.getLabelFormat
					}
				}
			},
			legend: {
				show: false
			},
			grid: {
				hoverable: true,
				clickable: true
			},
		});
	},
	getLabelFormat: function (label, slice) {
		return "<div style='font-size:x-small;text-align:center;padding:2px;color:" + slice.color + ";'>" + label + "<br/>" + slice.data[0][1] + "</div>";
	},
	registerSectionClick: function () {
		var thisInstance = this;
		thisInstance.getPlotContainer().bind("plothover", function (event, pos, item) {
			if (item) {
				$(this).css('cursor', 'pointer');
			} else {
				$(this).css('cursor', 'auto');
			}
		});
		thisInstance.getPlotContainer().bind("plotclick", function (event, pos, item) {
			if (item) {
				if (item.series.links)
					window.location.href = item.series.links;
			}
		});
	}
});

Vtiger_Widget_Js('YetiForce_Bar_Widget_Js', {}, {
	generateData: function () {
		var thisInstance = this;
		var container = thisInstance.getContainer();
		var jData = container.find('.widgetData').val();
		var data = JSON.parse(jData);
		var chartData = [];
		for (var index in data['chart']) {
			chartData.push(data['chart'][index]);
			thisInstance.chartData[data['chart'][index].id] = data['chart'][index];
		}

		return {'chartData': chartData, 'ticks': data['ticks'], 'links': data['links'], 'legend': data['legend'], 'valueLabels': data['valueLabels'] ? data['valueLabels'] : {}};
	},
	loadChart: function () {
		var thisInstance = this;
		var chartData = thisInstance.generateData();
		var options = {
			xaxis: {
				minTickSize: 1,
				ticks: chartData['ticks']
			},
			yaxis: {
				min: 0,
				tickDecimals: 0
			},
			grid: {
				hoverable: true,
				clickable: true
			},
			series: {
				bars: {
					show: true,
					barWidth: .9,
					dataLabels: false,
					align: "center",
					lineWidth: 0
				},
				valueLabels: chartData['valueLabels'],
				stack: true
			},
		};
		thisInstance.plotInstance = $.plot(thisInstance.getPlotContainer(false), chartData['chartData'], options);
	},
	getLabelFormat: function (label, slice) {
		return "<div style='font-size:x-small;text-align:center;padding:2px;color:" + slice.color + ";'>" + label + "<br/>" + slice.data[0][1] + "</div>";
	},
	registerSectionClick: function () {
		var thisInstance = this;
		var chartData = thisInstance.generateData();
		thisInstance.getPlotContainer().bind("plothover", function (event, pos, item) {
			if (item) {
				$(this).css('cursor', 'pointer');
			} else {
				$(this).css('cursor', 'auto');
			}
		});
		thisInstance.getPlotContainer().bind("plotclick", function (event, pos, item) {
			if (item) {
				$(chartData['links']).each(function () {
					if (item.dataIndex == this[0])
						window.location.href = this[1];
				});
			}
		});
	}
});
YetiForce_Bar_Widget_Js('YetiForce_Ticketsbystatus_Widget_Js',{},{
		loadChart: function () {
		var thisInstance = this;
		var chartData = thisInstance.generateData();
		var options = {
			xaxis: {
				minTickSize: 1,
				ticks: chartData['ticks']
			},
			yaxis: {
				min: 0,
				tickDecimals: 0
			},
			grid: {
				hoverable: true,
				clickable: true
			},
			series: {
				bars: {
					show: true,
					barWidth: .9,
					dataLabels: false,
					align: "center",
					lineWidth: 0
				},
				valueLabels: chartData['valueLabels'],
				stack: true
			},
			legend: {
				show: true,
				sorted: 'reverse'
			},
		};
		thisInstance.plotInstance = $.plot(thisInstance.getPlotContainer(false), chartData['chartData'], options);
	},
});
Vtiger_Widget_Js('YetiForce_Calendar_Widget_Js', {}, {
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
			eventLimitText: app.vtranslate('JS_MORE')
		});

		thisInstance.getCalendarView().find("td.fc-day-number")
				.mouseenter(function () {
					jQuery('<span class="plus pull-left glyphicon glyphicon-plus"></span>')
							.prependTo($(this))
				}).mouseleave(function () {
			$(this).find(".plus").remove();
		});

		thisInstance.getCalendarView().find("td.fc-day-number").click(function () {
			var date = $(this).data('date');
			var params = {noCache: true};
			params.data = {date_start: date, due_date: date};
			params.callbackFunction = function () {
				thisInstance.getCalendarView().closest('.dashboardWidget').find('a[name="drefresh"]').trigger('click');
			};
			Vtiger_Header_Js.getInstance().quickCreateModule('Calendar', params);
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
		var start_date = view.start.format();
		var end_date = view.end.format();

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
			thisInstance.getCalendarView().find(".fc-event-container a").click(function () {
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
				window.location.href = url + '["activitytype","e","' + $(this).data('type') + '"],["date_start","ir","' + $(this).data('date') + '"]]]';
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
		container.find('.fc-toolbar').addClass('hide');
		var month = container.find('.fc-toolbar h2').text();
		if (month) {
			var headerCalendar = container.find('.headerCalendar').removeClass('hide').find('.month').append('<h3>' + month + '</h3>');
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
Vtiger_Widget_Js('YetiForce_Calendaractivities_Widget_Js', {}, {
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
				url += '["assigned_user_id","c","' + owner.val() + '"],';
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
Vtiger_Widget_Js('YetiForce_Productssoldtorenew_Widget_Js', {}, {
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
		this.registerRecordsCount();
	}
});
YetiForce_Bar_Widget_Js('YetiForce_Alltimecontrol_Widget_Js', {}, {
	loadChart: function () {
		var thisInstance = this;
		var chartData = thisInstance.generateData();
		var options = {
			xaxis: {
				minTickSize: 1,
				ticks: chartData['ticks']
			},
			yaxis: {
				min: 0,
				tickDecimals: 0
			},
			grid: {
				hoverable: true,
				clickable: true
			},
			series: {
				bars: {
					show: true,
					barWidth: 0.8,
					dataLabels: false,
					align: "center",
					lineWidth: 0,
				},
				stack: true
			},
			legend: {
				show: true,
				labelFormatter: function (label, series) {
					return('<b>' + label + '</b>: ' + chartData['legend'][label] + ' h');
				}
			}
		};
		thisInstance.plotInstance = $.plot(thisInstance.getPlotContainer(false), chartData['chartData'], options);
	}
});
YetiForce_Bar_Widget_Js('YetiForce_Leadsbysource_Widget_Js', {}, {
	registerSectionClick: function () {
		var thisInstance = this;
		var chartData = thisInstance.generateData();
		thisInstance.getPlotContainer().bind("plothover", function (event, pos, item) {
			if (item) {
				$(this).css('cursor', 'pointer');
			} else {
				$(this).css('cursor', 'auto');
			}
		});
		thisInstance.getPlotContainer().bind("plotclick", function (event, pos, item) {
			if (item) {
				$(chartData['links']).each(function () {
					if (item.seriesIndex == this[0])
						window.location.href = this[1];
				});
			}
		});
	}
});
Vtiger_Pie_Widget_Js('YetiForce_Closedticketsbypriority_Widget_Js', {}, {
	generateData: function () {
		var container = this.getContainer();
		var jData = container.find('.widgetData').val();
		var data = JSON.parse(jData);
		var chartData = [];
		var colorData = [];
		var urlData = [];
		for (var index in data) {
			var row = data[index];
			var rowData = [row.name, row.count];
			chartData.push(rowData);
			colorData.push(row.color);
			urlData.push(row.url);
		}

		return {'chartData': chartData, color: colorData, url: urlData};
	},
	loadChart: function () {
		var chartData = this.generateData();
		if (chartData['chartData'].length > 0) {
			this.getPlotContainer(false).jqplot([chartData['chartData']], {
				seriesDefaults: {
					renderer: jQuery.jqplot.PieRenderer,
					rendererOptions: {
						showDataLabels: true,
						dataLabels: 'value'
					}
				},
				seriesColors: chartData['color'],
				legend: {
					show: true,
					location: 'e'
				},
				title: chartData['title']
			});
			this.registerSectionClick();
		}
	},
	registerSectionClick: function () {
		var chartData = this.generateData();
		this.getContainer().on('jqplotDataClick', function (ev, seriesIndex, pointIndex, arguments) {
			var url = chartData['url'][pointIndex];
			window.location.href = url;
		});
	}
});
Vtiger_Barchat_Widget_Js('YetiForce_Closedticketsbyuser_Widget_Js', {}, {});
Vtiger_Barchat_Widget_Js('YetiForce_Opentickets_Widget_Js', {}, {
	generateChartData: function () {
		var container = this.getContainer();
		var jData = container.find('.widgetData').val();
		var data = JSON.parse(jData);
		var chartData = [];
		var xLabels = new Array();
		var yMaxValue = 0;
		var color = [];
		for (var index in data) {
			var row = data[index];
			row[0] = parseInt(row[0]);
			xLabels.push(app.getDecodedValue(row[1]))
			chartData.push([app.getDecodedValue(row[1]), row[0]]);
			if (parseInt(row[0]) > yMaxValue) {
				yMaxValue = parseInt(row[0]);
			}
			color.push(row[3]);
		}
		yMaxValue = yMaxValue + 2 + (yMaxValue / 100) * 25;
		return {'chartData': [chartData], 'yMaxValue': yMaxValue, 'labels': xLabels, 'colors': color};
	},
	loadChart: function () {
		var data = this.generateChartData();
		if (data['chartData'][0].length > 0) {
			this.getPlotContainer(false).jqplot(data['chartData'], {
				title: data['title'],
				animate: !$.jqplot.use_excanvas,
				seriesColors: data['colors'],
				seriesDefaults: {
					renderer: jQuery.jqplot.BarRenderer,
					rendererOptions: {
						showDataLabels: true,
						dataLabels: 'value',
						barDirection: 'vertical',
						 varyBarColor: true
					},
					pointLabels: {show: true, edgeTolerance: -15}
				},
				axes: {
					xaxis: {
						tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
						renderer: jQuery.jqplot.CategoryAxisRenderer,
			
						tickOptions: {
							angle: -45,
							labelPosition: 'auto'
						}
					},
					yaxis: {
						min: 0,
						max: data['yMaxValue'],
						tickOptions: {
							formatString: '%d'
						},
						pad: 1.2
					}
				},
				legend: {
					show: false,
				}
			});
			this.registerSectionClick();
		}
	},
});
YetiForce_Bar_Widget_Js('YetiForce_Accountsbyindustry_Widget_Js', {}, {
	registerSectionClick: function () {
		var thisInstance = this;
		var chartData = thisInstance.generateData();
		thisInstance.getPlotContainer().bind("plothover", function (event, pos, item) {
			if (item) {
				$(this).css('cursor', 'pointer');
			} else {
				$(this).css('cursor', 'auto');
			}
		});
		thisInstance.getPlotContainer().bind("plotclick", function (event, pos, item) {
			if (item) {
				$(chartData['links']).each(function () {
					if (item.seriesIndex == this[0])
						window.location.href = this[1];
				});
			}
		});
	}
});
Vtiger_Funnel_Widget_Js('YetiForce_Estimatedvaluebystatus_Widget_Js', {}, {
	generateData: function () {
		var container = this.getContainer();
		var data = container.find('.widgetData').val();
		var dataInfo = JSON.parse(data);
		return dataInfo;
	},
	loadChart: function () {
		var dataInfo = this.generateData();
		if (dataInfo.length > 0) {
			this.getPlotContainer(false).jqplot([dataInfo], {
				seriesDefaults: {
					renderer: jQuery.jqplot.FunnelRenderer,
					rendererOptions: {
						sectionMargin: 0,
						widthRatio: 0.3,
						showDataLabels: true,
						dataLabelThreshold: 0,
						dataLabels: 'label',
						highlightMouseDown: true
					}
				},
				legend: {
					show: false,
					location: 'e',
				}
			});
			this.registerSectionClick();
		}
	}
});
Vtiger_Barchat_Widget_Js('YetiForce_Notificationsbysender_Widget_Js', {}, {});
Vtiger_Barchat_Widget_Js('YetiForce_Notificationsbyrecipient_Widget_Js', {}, {});
