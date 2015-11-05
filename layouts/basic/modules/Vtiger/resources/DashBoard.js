/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 ************************************************************************************/

jQuery.Class("Vtiger_DashBoard_Js", {
	gridster: false,
	//static property which will store the instance of dashboard
	currentInstance: false,
	addWidget: function (element, url) {
		var element = jQuery(element);
		var linkId = element.data('linkid');
		var name = element.data('name');
		jQuery(element).parent().remove();
		if (jQuery('ul.widgetsList li').size() < 1) {
			jQuery('ul.widgetsList').prev('button').css('visibility', 'hidden');
		}
		var widgetContainer = jQuery('<li class="new dashboardWidget" id="' + linkId + '" data-name="' + name + '" data-mode="open"></li>');
		widgetContainer.data('url', url);
		var width = element.data('width');
		var height = element.data('height');
		Vtiger_DashBoard_Js.gridster.add_widget(widgetContainer, width, height);
		Vtiger_DashBoard_Js.currentInstance.loadWidget(widgetContainer);
	},
	restrictContentDrag: function (container) {
		container.on('mousedown.draggable', function (e) {
			var element = jQuery(e.target);
			var isHeaderElement = element.closest('.dashboardWidgetHeader').length > 0 ? true : false;
			if (isHeaderElement) {
				return;
			}
			//Stop the event propagation so that drag will not start for contents
			e.stopPropagation();
		})
	},
}, {
	container: false,
	instancesCache: {},
	init: function () {
		Vtiger_DashBoard_Js.currentInstance = this;
	},
	getContainer: function () {
		if (this.container == false) {
			this.container = jQuery('.gridster ul');
		}
		return this.container;
	},
	getWidgetInstance: function (widgetContainer) {
		var id = widgetContainer.attr('id');
		if (!(id in this.instancesCache)) {
			var widgetName = widgetContainer.data('name');
			this.instancesCache[id] = Vtiger_Widget_Js.getInstance(widgetContainer, widgetName);
		}
		return this.instancesCache[id];
	},
	registerGridster: function () {
		var thisInstance = this;
		Vtiger_DashBoard_Js.gridster = this.getContainer().gridster({
			widget_margins: [7, 7],
			widget_base_dimensions: [((thisInstance.getContainer().width() / 12) - 14), 100],
			min_cols: 6,
			min_rows: 20,
			max_size_x: 12,
			draggable: {
				'stop': function () {
					thisInstance.savePositions(jQuery('.dashboardWidget'));
				}
			}
		}).data('gridster');
	},
	savePositions: function (widgets) {
		var widgetRowColPositions = {}
		for (var index = 0, len = widgets.length; index < len; ++index) {
			var widget = jQuery(widgets[index]);
			widgetRowColPositions[widget.attr('id')] = JSON.stringify({
				row: widget.attr('data-row'), col: widget.attr('data-col')
			});
		}

		AppConnector.request({module: 'Vtiger', action: 'SaveWidgetPositions', 'positionsmap': widgetRowColPositions}).then(function (data) {
		});
	},
	loadWidgets: function () {
		var thisInstance = this;
		var widgetList = jQuery('.dashboardWidget');
		widgetList.each(function (index, widgetContainerELement) {
			thisInstance.loadWidget(jQuery(widgetContainerELement));
		});

	},
	loadWidget: function (widgetContainer) {
		var thisInstance = this;
		var urlParams = widgetContainer.data('url');
		var mode = widgetContainer.data('mode');
		widgetContainer.progressIndicator();
		if (mode == 'open') {
			AppConnector.request(urlParams).then(
					function (data) {
						widgetContainer.html(data);

						var headerHeight = widgetContainer.find('.dashboardWidgetHeader').height() + 15;
						var adjustedHeight = widgetContainer.height() - headerHeight;
						app.showScrollBar(widgetContainer.find('.dashboardWidgetContent'), {'height': adjustedHeight});
						var widgetInstance = thisInstance.getWidgetInstance(widgetContainer);
						widgetContainer.trigger(Vtiger_Widget_Js.widgetPostLoadEvent);
					},
					function () {
					}
			);
		} else {
		}
	},
	gridsterStop: function () {
		// TODO: we need to allow the header of the widget to be draggable
		var gridster = Vtiger_DashBoard_Js.gridster;

	},
	registerRefreshWidget: function () {
		var thisInstance = this;
		this.getContainer().on('click', 'a[name="drefresh"]', function (e) {
			var element = $(e.currentTarget);
			var parent = element.closest('li');
			var widgetInstnace = thisInstance.getWidgetInstance(parent);
			widgetInstnace.refreshWidget();
			return;
		});
	},
	removeWidget: function () {
		this.getContainer().on('click', 'li a[name="dclose"]', function (e) {
			var element = $(e.currentTarget);
			var listItem = jQuery(element).parents('li');
			var width = listItem.attr('data-sizex');
			var height = listItem.attr('data-sizey');

			var url = element.data('url');
			var parent = element.closest('.dashboardWidgetHeader').parent();
			var widgetName = parent.data('name');
			var widgetTitle = parent.find('.dashboardTitle').attr('title');

			var message = app.vtranslate('JS_ARE_YOU_SURE_TO_DELETE_WIDGET') + "[" + widgetTitle + "]. " + app.vtranslate('JS_ARE_YOU_SURE_TO_DELETE_WIDGET_INFO');
			Vtiger_Helper_Js.showConfirmationBox({'message': message}).then(
					function (e) {
						AppConnector.request(url).then(
								function (response) {
									if (response.success) {
										var nonReversableWidgets = []

										parent.fadeOut('slow', function () {
											parent.remove();
										});
										if (jQuery.inArray(widgetName, nonReversableWidgets) == -1) {
											Vtiger_DashBoard_Js.gridster.remove_widget(element.closest('li'));
											jQuery('.widgetsList').prev('button').css('visibility', 'visible');
											var data = '<li><a onclick="Vtiger_DashBoard_Js.addWidget(this, \'' + response.result.url + '\')" href="javascript:void(0);"';
											data += 'data-width=' + width + ' data-height=' + height + ' data-linkid=' + response.result.linkid + ' data-name=' + response.result.name + '>' + response.result.title + '</a></li>';
											var divider = jQuery('.widgetsList .divider');
											if (divider.length) {
												jQuery(data).insertBefore(divider);
											} else {
												jQuery('.widgetsList').append(data);
											}
										}
									}
								}
						);
					},
					function (error, err) {
					}
			);
		});
	},
	registerDatePickerHideInitiater: function () {
		var container = this.getContainer();
		container.on('click', 'input.dateRange', function (e) {
			var widgetContainer = jQuery(e.currentTarget).closest('.dashboardWidget');
			var dashboardWidgetHeader = jQuery('.dashboardWidgetHeader', widgetContainer);

			var callbackFunction = function () {
				jQuery('.dateRange').DatePickerHide();
			}
			//adding clickoutside event on the dashboardWidgetHeader
			Vtiger_Helper_Js.addClickOutSideEvent(dashboardWidgetHeader, callbackFunction);
			return false;
		})
	},
	registerShowMailBody: function () {
		var container = this.getContainer();
		container.on('click', '.showMailBody', function (e) {
			var widgetContainer = jQuery(e.currentTarget).closest('.mailRow');
			var mailBody = widgetContainer.find('.mailBody');
			var bodyIcon = jQuery(e.currentTarget).find('.body-icon');
			if (mailBody.css("display") == 'none') {
				mailBody.show();
				bodyIcon.removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-up");
			} else {
				mailBody.hide();
				bodyIcon.removeClass("glyphicon-chevron-up").addClass("glyphicon-chevron-down");
			}
		});
	},
	registerChangeMailUser: function () {
		var thisInstance = this;
		var container = this.getContainer();

		container.on('change', '#mailUserList', function (e) {
			var element = $(e.currentTarget);
			var parent = element.closest('li');
			var contentContainer = parent.find('.dashboardWidgetContent');
			var optionSelected = $("option:selected", this);
			var url = parent.data('url') + '&user=' + optionSelected.val();

			params = {};
			params.url = url
			params.data = {};
			contentContainer.progressIndicator({});
			AppConnector.request(params).then(
					function (data) {
						contentContainer.progressIndicator({'mode': 'hide'});
						parent.html(data).trigger(Vtiger_Widget_Js.widgetPostRefereshEvent);
					},
					function () {
						contentContainer.progressIndicator({'mode': 'hide'});
					}
			);
		});
	},
	registerMiniListWidget: function () {
		var thisInstance = this;
		$('.dashboardHeading').on('click', '.addFilter', function (e) {
			var element = $(e.currentTarget);

			app.showModalWindow(null, "index.php?module=Home&view=MiniListWizard&step=step1", function (wizardContainer) {
				var form = jQuery('form', wizardContainer);

				var moduleNameSelectDOM = jQuery('select[name="module"]', wizardContainer);
				var filteridSelectDOM = jQuery('select[name="filterid"]', wizardContainer);
				var fieldsSelectDOM = jQuery('select[name="fields"]', wizardContainer);

				var moduleNameSelect2 = app.showSelect2ElementView(moduleNameSelectDOM, {
					placeholder: app.vtranslate('JS_SELECT_MODULE')
				});
				var filteridSelect2 = app.showSelect2ElementView(filteridSelectDOM, {
					placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION')
				});
				var fieldsSelect2 = app.showSelect2ElementView(fieldsSelectDOM, {
					placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION'),
					closeOnSelect: true,
					maximumSelectionLength: 6
				});
				var footer = jQuery('.modal-footer', wizardContainer);

				filteridSelectDOM.closest('tr').hide();
				fieldsSelectDOM.closest('tr').hide();
				footer.hide();

				moduleNameSelect2.change(function () {
					if (!moduleNameSelect2.val())
						return;

					AppConnector.request({
						module: 'Home',
						view: 'MiniListWizard',
						step: 'step2',
						selectedModule: moduleNameSelect2.val()
					}).then(function (res) {
						filteridSelectDOM.empty().html(res).trigger('change');
						filteridSelect2.closest('tr').show();
					})
				});
				filteridSelect2.change(function () {
					if (!filteridSelect2.val())
						return;

					AppConnector.request({
						module: 'Home',
						view: 'MiniListWizard',
						step: 'step3',
						selectedModule: moduleNameSelect2.val(),
						filterid: filteridSelect2.val()
					}).then(function (res) {
						fieldsSelectDOM.empty().html(res).trigger('change');
						fieldsSelect2.closest('tr').show();
						fieldsSelect2.data('select2').$selection.find('.select2-search__field').parent().css('width', '100%');
					});
				});
				fieldsSelect2.change(function () {
					if (!fieldsSelect2.val()) {
						footer.hide();
					} else {
						footer.show();
					}
				});

				form.submit(function (e) {
					e.preventDefault();
					var selectedModule = moduleNameSelect2.val();
					var selectedModuleLabel = moduleNameSelect2.find(':selected').text();
					var selectedFilterId = filteridSelect2.val();
					var selectedFilterLabel = filteridSelect2.find(':selected').text();
					var selectedFields = [];
					fieldsSelect2.select2('data').map(function (obj) {
						selectedFields.push(obj.id);
					});

					var data = {
						module: selectedModule
					}
					if (typeof selectedFields != 'object')
						selectedFields = [selectedFields];
					data['fields'] = selectedFields;
					thisInstance.saveMiniListWidget(data, element, selectedModuleLabel, selectedFilterId, selectedFilterLabel, form);
				});
			});
		});
	},
	saveMiniListWidget: function (data, element, moduleNameLabel, filterid, filterLabel, form) {
		var thisInstance = this;
		var paramsForm = {
			data: JSON.stringify(data),
			action: 'addWidget',
			blockid: element.data('block-id'),
			linkid: element.data('linkid'),
			label: moduleNameLabel + ' - ' + filterLabel,
			name: 'Mini List',
			filterid: filterid,
			isdefault: 0,
			height: 3,
			width: 4,
			owners_all: ["mine", "all", "users", "groups"],
			default_owner: 'mine',
		};
		thisInstance.saveWidget(paramsForm, 'save').then(
				function (data) {
					var result = data['result'];
					var params = {};
					if (data['success']) {
						app.hideModalWindow();
						paramsForm['id'] = result['id'];
						paramsForm['status'] = result['status'];
						params['text'] = result['text'];
						params['type'] = 'success';
						var linkElement = element.clone();
						linkElement.data('name', 'MiniList')
						Vtiger_DashBoard_Js.addWidget(linkElement, 'index.php?module=Home&view=ShowWidget&name=MiniList&linkid=' + element.data('linkid') + '&widgetid=' + result['wid'] + '&active=0')
						Vtiger_Helper_Js.showMessage(params);
					} else {
						var message = data['error']['message'];
						if (data['error']['code'] != 513) {
							var errorField = form.find('[name="fieldName"]');
						} else {
							var errorField = form.find('[name="fieldLabel"]');
						}
						errorField.validationEngine('showPrompt', message, 'error', 'topLeft', true);
					}
				}
		);
	},
	saveWidget: function (form, mode) {
		var aDeferred = jQuery.Deferred();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});

		var params = {
			form: form,
			module: 'WidgetsManagement',
			parent: 'Settings',
			sourceModule: app.getModuleName(),
			action: 'SaveAjax',
			mode: mode,
			addToUser: true,
		};

		AppConnector.request(params).then(
				function (data) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					aDeferred.resolve(data);
				},
				function (error) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					aDeferred.reject(error);
				}
		);
		return aDeferred.promise();
	},
	registerEvents: function () {
		this.registerGridster();
		this.loadWidgets();
		this.registerRefreshWidget();
		this.removeWidget();
		this.registerDatePickerHideInitiater();
		this.gridsterStop();
		this.registerShowMailBody();
		this.registerChangeMailUser();
		this.registerMiniListWidget();
	},
});
