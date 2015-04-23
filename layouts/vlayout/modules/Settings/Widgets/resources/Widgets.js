/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
var Settings_Index_Js = {
	getTabId: function() {
		return $(".WidgetsManage [name='tabid']").val();
	},
	getType: function() {
		return $(".form-modalAddWidget [name='type']").val();
	},
	addWidget: function(e) {
		var progressIndicatorElement = jQuery.progressIndicator({'position' : 'html'});
		var module = $(".WidgetsManage select[name='ModulesList']").val();
		app.showModalWindow(null, "index.php?parent=Settings&module=Widgets&view=Widget&mod="+module, function(wizardContainer){
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			var form = jQuery('form', wizardContainer);
			form.submit(function(e){
				e.preventDefault();
				var type = form.find('[name="type"]').val();
				Settings_Index_Js.createStep2(type);
			});
			
		});
	},
	createStep2: function(type) {
		var tabId = Settings_Index_Js.getTabId();
		var progressIndicatorElement = jQuery.progressIndicator({'position' : 'html'});
		app.showModalWindow(null, "index.php?parent=Settings&module=Widgets&view=Widget&mode=createStep2&type="+type+"&tabId="+tabId, function(wizardContainer){
			wizardContainer.find('.HelpInfoPopover').hover(
				function () {
					$(this).popover('show');
				}, 
				function () {
					$(this).popover('hide');
				}
			);
			if(type == 'RelatedModule'){
				Settings_Index_Js.loadFilters(wizardContainer);
				wizardContainer.find("select[name='relatedmodule']").change(Settings_Index_Js.changeRelatedModule);
			}
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			var form = jQuery('form', wizardContainer);
			form.submit(function(e){
				e.preventDefault();
				var formData = form.serializeFormData();
				Settings_Index_Js.registerSaveEvent('saveWidget',{
					'data':formData,
					'tabid': tabId,
				});
				Settings_Index_Js.reloadWidgets();
			});
			
		});
		
	},
	loadWidgets: function(){
		var blocks = jQuery('.blocksSortable');
		blocks.sortable({
			'revert' : true,
			'connectWith': ".blocksSortable",
			'tolerance':'pointer',
			'cursor' : 'move',
			'placeholder': "state-highlight",
			'stop': function (event, ui ) {
				Settings_Index_Js.updateSequence();
			}
		});

	},
	updateSequence: function() {
		var params = {};
		$( ".blockSortable" ).each(function( index ) {
			params[$( this ).data('id')] = {'index': index, 'column': $( this ).closest('.blocksSortable').data('column')};
		});
		var progress = $.progressIndicator({
			'message' : app.vtranslate('Saving changes'),
			'blockInfo' : {
				'enabled' : true
			}
		});
		Settings_Index_Js.registerSaveEvent('updateSequence',{
			'data':params,
			'tabid':$("input[name='tabid']").val(),
		});
		progress.progressIndicator({'mode': 'hide'});
	},
	reloadWidgets: function() {
		var Indicator = jQuery.progressIndicator({
			'message' : app.vtranslate('Loading data'),
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});
		var params = {};
		params['module'] = 'Widgets';
		params['view'] = 'Index';
		params['parent'] = 'Settings';
		params['source'] = $("input[name='tabid']").val();
		AppConnector.request(params).then(
			function(data) {
				jQuery('div.contentsDiv').html( data );
				Settings_Index_Js.registerEvents();
				$("input[name='ModulesList']").select2();
				Indicator.progressIndicator({'mode': 'hide'});
			}
		);
	},
	changeModule: function(e) {
		var target = $(e.currentTarget);
		$("input[name='tabid']").val(target.val());
		Settings_Index_Js.reloadWidgets();
	},
	editWidget: function(e) {
		var target = $(e.currentTarget);
		var blockSortable = target.closest('.blockSortable');
		app.showModalWindow(null, "index.php?parent=Settings&module=Widgets&view=Widget&mode=edit&id="+blockSortable.data('id'), function(wizardContainer){
			wizardContainer.find('.HelpInfoPopover').hover(
				function () {
					$(this).popover('show');
				}, 
				function () {
					$(this).popover('hide');
				}
			);
			if(Settings_Index_Js.getType() == 'RelatedModule'){
				Settings_Index_Js.loadFilters(wizardContainer);
				wizardContainer.find("select[name='relatedmodule']").change(Settings_Index_Js.changeRelatedModule);
			}
			var form = jQuery('form', wizardContainer);
			form.submit(function(e){
				e.preventDefault();
				var progress = $.progressIndicator({
					'message' : app.vtranslate('Loading data'),
					'blockInfo' : {
						'enabled' : true
					}
				});
				var FormData = form.serializeFormData();
				Settings_Index_Js.registerSaveEvent('saveWidget',{
					'data':FormData,
					'tabid':$("input[name='tabid']").val(),
				});
				Settings_Index_Js.reloadWidgets();
				progress.progressIndicator({'mode': 'hide'});
			});
		});
	},
	removeWidget: function(e) {
		var target = $(e.currentTarget);
		var blockSortable = target.closest('.blockSortable');
		Settings_Index_Js.registerSaveEvent('removeWidget',{
			'wid':blockSortable.data('id'),
		});
		Settings_Index_Js.reloadWidgets();
	},
	registerSaveEvent: function(mode, data) {
		var resp = '';
		var params = {}
		params.data = {
			module: app.getModuleName(), 
			parent: app.getParentModuleName(), 
			action: 'SaveAjax', 
			mode: mode,
			params: data
		}
		if(mode == 'saveWidget'){
			params.async = false;
		}else{
			params.async = true;
		}
		params.dataType = 'json';
        AppConnector.request(params).then(
			function(data) {
				var response = data['result'];
				var params = {
					text: response['message'],
					animation: 'show',
					type: 'success'
				};
				Vtiger_Helper_Js.showPnotify(params);
				resp = response['success'];
			},
			function(data, err) {

			}
        );
	},
	loadFilters: function(contener) {
		var filters = JSON.parse(jQuery('#filters').val());
		var relatedmodule = contener.find("select[name='relatedmodule'] option:selected").val();
		var filter_field = contener.find("select[name='filter']");
		var filter_selected = contener.find("input[name='filter_selected']").val();
		filter_field.empty();
		filter_field.append($('<option/>', { value: '-',text : app.vtranslate('None') }));
		if( filters[relatedmodule] !== undefined ) {
			$.each(filters[relatedmodule], function (index, value) {
				var option = { value: index,	text : value }
				if(filter_selected == index){
					option.selected = 'selected';
				}
				filter_field.append($('<option/>', option ));
			}); 
		}
		var filterv = jQuery("input[name='filterv']").val();
		if(filterv != undefined){
			filter_field.val(filterv);
		}
		filter_field.select2();
	},
	changeRelatedModule: function(e) {
		var target = $(e.currentTarget);
		var form = target.closest('.form-modalAddWidget');
		Settings_Index_Js.loadFilters(form);	
	},
	registerEvents : function() {  
		this.loadWidgets();
		$(".WidgetsManage select[name='ModulesList']").change(this.changeModule);
		$('.WidgetsManage .addWidget').click(this.addWidget);
		$('.WidgetsManage .editWidget').click(this.editWidget);
		$('.WidgetsManage .removeWidget').click(this.removeWidget);

	}
}
$(document).ready(function(){
	Settings_Index_Js.registerEvents();
})
