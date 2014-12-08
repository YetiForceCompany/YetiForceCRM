/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/

jQuery.Class("Vtiger_DashBoard_Js", {
	gridster : false,

	//static property which will store the instance of dashboard
	currentInstance : false,

	addWidget : function(element, url) {
		var element = jQuery(element);
		var linkId = element.data('linkid');
		var name = element.data('name');
		jQuery(element).parent().hide();
		var widgetContainer = jQuery('<li class="new dashboardWidget" id="'+ linkId +'" data-name="'+name+'" data-mode="open"></li>');
		widgetContainer.data('url', url);
		var width = element.data('width');
		var height = element.data('height');
		Vtiger_DashBoard_Js.gridster.add_widget(widgetContainer, width, height);
		Vtiger_DashBoard_Js.currentInstance.loadWidget(widgetContainer);
	},

	addMiniListWidget: function(element, url) {
		// 1. Show popup window for selection (module, filter, fields)
		// 2. Compute the dynamic mini-list widget url
		// 3. Add widget with URL to the page.

		element = jQuery(element);

		app.showModalWindow(null, "index.php?module=Home&view=MiniListWizard&step=step1", function(wizardContainer){
			var form = jQuery('form', wizardContainer);

			var moduleNameSelectDOM = jQuery('select[name="module"]', wizardContainer);
			var filteridSelectDOM = jQuery('select[name="filterid"]', wizardContainer);
			var fieldsSelectDOM = jQuery('select[name="fields"]', wizardContainer);

			var moduleNameSelect2 = app.showSelect2ElementView(moduleNameSelectDOM, {
				placeholder: app.vtranslate('JS_SELECT_MODULE')
			});
			var filteridSelect2 = app.showSelect2ElementView(filteridSelectDOM,{
				placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION')
			});
			var fieldsSelect2 = app.showSelect2ElementView(fieldsSelectDOM, {
				placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION'),
				closeOnSelect: true,
				maximumSelectionSize: 6
			});
			var footer = jQuery('.modal-footer', wizardContainer);

			filteridSelectDOM.closest('tr').hide();
			fieldsSelectDOM.closest('tr').hide();
			footer.hide();

			moduleNameSelect2.change(function(){
				if (!moduleNameSelect2.val()) return;

				AppConnector.request({
					module: 'Home',
					view: 'MiniListWizard',
					step: 'step2',
					selectedModule: moduleNameSelect2.val()
				}).then(function(res) {
					filteridSelectDOM.empty().html(res).trigger('change');
					filteridSelect2.closest('tr').show();
				})
			});
			filteridSelect2.change(function(){
				if (!filteridSelect2.val()) return;

				AppConnector.request({
					module: 'Home',
					view: 'MiniListWizard',
					step: 'step3',
					selectedModule: moduleNameSelect2.val(),
					filterid: filteridSelect2.val()
				}).then(function(res){
					fieldsSelectDOM.empty().html(res).trigger('change');
					fieldsSelect2.closest('tr').show();
				});
			});
			fieldsSelect2.change(function() {
				if (!fieldsSelect2.val()) {
					footer.hide();
				} else {
					footer.show();
				}
			});

			form.submit(function(e){
				e.preventDefault();
                //To disable savebutton after one submit to prevent multiple submits
                jQuery("[name='saveButton']").attr('disabled','disabled');
				var selectedModule = moduleNameSelect2.val();
				var selectedFilterId= filteridSelect2.val();
				var selectedFields = [];
				fieldsSelect2.select2('data').map(function (obj) { 
					selectedFields.push(obj.id);
				});
				// TODO mandatory field validation
				finializeAdd(selectedModule, selectedFilterId, selectedFields);
			});
		});

		function finializeAdd(moduleName, filterid, fields) {
			var data = {
				module: moduleName
			}
			if (typeof fields != 'object') fields = [fields];
			data['fields'] = fields;

			url += '&filterid='+encodeURIComponent(filterid)+'&data=' + encodeURIComponent(JSON.stringify(data));

			var linkId = element.data('linkid');
			var name = element.data('name');
			var widgetContainer = jQuery('<li class="new dashboardWidget" id="'+ linkId +"-" + filterid +'" data-name="'+name+'" data-mode="open"></li>');
			widgetContainer.data('url', url);
			var width = element.data('width');
			var height = element.data('height');
			Vtiger_DashBoard_Js.gridster.add_widget(widgetContainer, width, height);
			Vtiger_DashBoard_Js.currentInstance.loadWidget(widgetContainer);
            app.hideModalWindow();
		}
	},


	restrictContentDrag : function(container){
		container.on('mousedown.draggable', function(e){
			var element = jQuery(e.target);
			var isHeaderElement = element.closest('.dashboardWidgetHeader').length > 0 ? true : false;
			if(isHeaderElement){
				return;
			}
			//Stop the event propagation so that drag will not start for contents
			e.stopPropagation();
		})
	},

	addNoteBookWidget : function(element, url) {
		// 1. Show popup window for selection (module, filter, fields)
		// 2. Compute the dynamic mini-list widget url
		// 3. Add widget with URL to the page.

		element = jQuery(element);

		app.showModalWindow(null, "index.php?module=Home&view=AddNotePad", function(wizardContainer){
			var form = jQuery('form', wizardContainer);
			var params = app.validationEngineOptions;
			params.onValidationComplete = function(form, valid){
				if(valid) {
                    //To prevent multiple click on save
                    jQuery("[name='saveButton']").attr('disabled','disabled');
					var notePadName = form.find('[name="notePadName"]').val();
					var notePadContent = form.find('[name="notePadContent"]').val();
					var linkId = element.data('linkid');
					var noteBookParams = {
						'module' : app.getModuleName(),
						'action' : 'NoteBook',
						'mode' : 'NoteBookCreate',
						'notePadName' : notePadName,
						'notePadContent' : notePadContent,
						'linkId' : linkId
					}
					AppConnector.request(noteBookParams).then(
						function(data){
							if(data.result.success){
								var widgetId = data.result.widgetId;
								app.hideModalWindow();

								url += '&widgetid='+widgetId

								var name = element.data('name');
								var widgetContainer = jQuery('<li class="new dashboardWidget" id="'+ linkId +"-" + widgetId +'" data-name="'+name+'" data-mode="open"></li>');
								widgetContainer.data('url', url);
								var width = element.data('width');
								var height = element.data('height');
								Vtiger_DashBoard_Js.gridster.add_widget(widgetContainer, width, height);
								Vtiger_DashBoard_Js.currentInstance.loadWidget(widgetContainer);
							}
						})
				}
				return false;
			}
			form.validationEngine(params);
		});
	}

}, {

	container : false,

	instancesCache : {},

	init : function() {
		Vtiger_DashBoard_Js.currentInstance = this;
	},

	getContainer : function() {
		if(this.container == false) {
			this.container = jQuery('.gridster ul');
		}
		return this.container;
	},

	getWidgetInstance : function(widgetContainer) {
		var id = widgetContainer.attr('id');
		if(!(id in this.instancesCache)) {
			var widgetName = widgetContainer.data('name');
			this.instancesCache[id] = Vtiger_Widget_Js.getInstance(widgetContainer, widgetName);
		}
		return this.instancesCache[id];
	},

	registerGridster : function() {
		var thisInstance = this;
		Vtiger_DashBoard_Js.gridster = this.getContainer().gridster({
			widget_margins: [7, 7],
			widget_base_dimensions: [100, 300],
			min_cols: 6,
			min_rows: 20,
			draggable: {
				'stop': function() {
					thisInstance.savePositions(jQuery('.dashboardWidget'));
				}
			}
		}).data('gridster');
	},

	savePositions: function(widgets) {
		var widgetRowColPositions = {}
		for (var index=0, len = widgets.length; index < len; ++index) {
			var widget = jQuery(widgets[index]);
			widgetRowColPositions[widget.attr('id')] = JSON.stringify({
				row: widget.attr('data-row'), col: widget.attr('data-col')
			});
		}

		AppConnector.request({module: 'Vtiger', action: 'SaveWidgetPositions', 'positionsmap': widgetRowColPositions}).then(function(data){
		});
	},

	loadWidgets : function() {
		var thisInstance = this;
		var widgetList = jQuery('.dashboardWidget');
		widgetList.each(function(index,widgetContainerELement){
			thisInstance.loadWidget(jQuery(widgetContainerELement));
		});

	},

	loadWidget : function(widgetContainer) {
		var thisInstance = this;
		var urlParams = widgetContainer.data('url');
		var mode = widgetContainer.data('mode');
		widgetContainer.progressIndicator();
		if(mode == 'open') {
			AppConnector.request(urlParams).then(
				function(data){
					widgetContainer.html(data);
					var adjustedHeight = widgetContainer.height()-50;
					app.showScrollBar(widgetContainer.find('.dashboardWidgetContent'),{'height' : adjustedHeight});
					var widgetInstance = thisInstance.getWidgetInstance(widgetContainer);
					widgetContainer.trigger(Vtiger_Widget_Js.widgetPostLoadEvent);
				},
				function(){
				}
				);
		} else {
	}
	},


	registerEvents : function() {
		this.registerGridster();
		this.loadWidgets();
		this.registerRefreshWidget();
		this.showWidgetIcons();
		this.hideWidgetIcons();
		this.removeWidget();
		this.registerFilterInitiater();
		this.gridsterStop();
		this.registerShowMailBody();
		this.registerChangeMailUser();
	},

	gridsterStop : function() {
		// TODO: we need to allow the header of the widget to be draggable
		var gridster = Vtiger_DashBoard_Js.gridster;

	},

	registerRefreshWidget : function() {
		var thisInstance = this;
		this.getContainer().on('click', 'a[name="drefresh"]', function(e) {
			var element = $(e.currentTarget);
			var parent = element.closest('li');
			var widgetInstnace = thisInstance.getWidgetInstance(parent);
			widgetInstnace.refreshWidget();
			return;
		});
	},

	showWidgetIcons : function() {
		this.getContainer().on('mouseover', 'li', function(e) {
			var element = $(e.currentTarget);
			var widgetIcons = element.find('.widgeticons');
			widgetIcons.fadeIn('slow', function() {
				widgetIcons.css('visibility', 'visible');
			});
		});
	},

	hideWidgetIcons : function() {
		this.getContainer().on('mouseout', 'li', function(e) {
			var element = $(e.currentTarget);
			var widgetIcons = element.find('.widgeticons');
			widgetIcons.css('visibility', 'hidden');
		});
	},

	removeWidget : function() {
		this.getContainer().on('click', 'li a[name="dclose"]', function(e) {
			var element = $(e.currentTarget);
            var listItem = jQuery(element).parents('li');
            var width = listItem.attr('data-sizex');
            var height = listItem.attr('data-sizey');
            
			var url = element.data('url');
			var parent = element.closest('.dashboardWidgetHeader').parent();
			var widgetName = parent.data('name');
			var widgetTitle = parent.find('.dashboardTitle').attr('title');

			var message = app.vtranslate('JS_ARE_YOU_SURE_TO_DELETE_WIDGET')+"["+widgetTitle+"]. "+app.vtranslate('JS_ARE_YOU_SURE_TO_DELETE_WIDGET_INFO');
			Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
				function(e) {
						AppConnector.request(url).then(
							function(response) {
								if (response.success) {
									var nonReversableWidgets = ['MiniList','Notebook']

									parent.fadeOut('slow', function() {
										parent.remove();
									});
									if (jQuery.inArray(widgetName, nonReversableWidgets) == -1) {
										var data = '<li><a onclick="Vtiger_DashBoard_Js.addWidget(this, \''+response.result.url+'\')" href="javascript:void(0);"';
										data += 'data-width='+width+' data-height='+height+ ' data-linkid='+response.result.linkid+' data-name='+response.result.name+'>'+response.result.title+'</a></li>';
										var divider = jQuery('.widgetsList .divider');
										if(divider.length) {
											jQuery(data).insertBefore(divider);
										} else {
											jQuery(data).insertAfter(jQuery('.widgetsList li:last'));
										}
									}
								}
							}
						);
					},
				function(error, err){
				}
			);
		});
	},

	registerFilterInitiater : function() {
		var container = this.getContainer();
		container.on('click', 'a[name="dfilter"]', function(e) {
			var widgetContainer = jQuery(e.currentTarget).closest('.dashboardWidget');
			var filterContainer = widgetContainer.find('.filterContainer');
			var dashboardWidgetHeader = jQuery('.dashboardWidgetHeader', widgetContainer);

			filterContainer.slideToggle(500);

			var callbackFunction = function() {
				filterContainer.slideUp(500);
				jQuery('.dateRange').DatePickerHide();
			}
			//adding clickoutside event on the dashboardWidgetHeader
			Vtiger_Helper_Js.addClickOutSideEvent(dashboardWidgetHeader, callbackFunction);

			return false;
		})
	},
	registerShowMailBody : function() {
		var container = this.getContainer();
		container.on('click', '.showMailBody', function(e) {
			var widgetContainer = jQuery(e.currentTarget).closest('.mailRow');
			var mailBody = widgetContainer.find('.mailBody');
			var bodyIcon = jQuery(e.currentTarget).find('.body-icon');
			if( mailBody.css( "display" ) == 'none'){
				mailBody.show();
				bodyIcon.removeClass( "icon-chevron-down" ).addClass( "icon-chevron-up" );
			}else{
				mailBody.hide();
				bodyIcon.removeClass( "icon-chevron-up" ).addClass( "icon-chevron-down" );
			}
		});
	},
	registerChangeMailUser : function() {
		var thisInstance = this;
		var container = this.getContainer();

		container.on('change', '#mailUserList', function(e) {
			var element = $(e.currentTarget);
			var parent = element.closest('li');
			var contentContainer = parent.find('.refresh');
			var optionSelected = $("option:selected", this);
			var url = parent.data('url')+'&user='+optionSelected.val();
			
			console.log( url );
			params = {};
			params.url = url
			params.data = {};
			contentContainer.progressIndicator({});
			AppConnector.request(params).then(
				function(data){
					contentContainer.progressIndicator({'mode': 'hide'});
					parent.html(data).trigger(Vtiger_Widget_Js.widgetPostRefereshEvent);
				},
				function(){
					contentContainer.progressIndicator({'mode': 'hide'});
				}
			);
		});
	}
});
