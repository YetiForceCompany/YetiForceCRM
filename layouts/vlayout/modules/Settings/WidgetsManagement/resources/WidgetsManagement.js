/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
 
function WidgetsManagement() {

	this.addCondition = function() {
		var thisInstance = this;
		jQuery('button.addCondition').on('click', function(){
			var roles = thisInstance.getRoles(jQuery(this).closest('.active'));
			var copyElement = jQuery(this).closest('.active').find('.copyRow');
			var element = copyElement.clone(true,true);
			element.removeAttr('class');
			element.attr('class', 'rowtr');
			element.find('select.role option').each(function(){
				if(thisInstance.in_array(jQuery(this).val(), roles)){
					jQuery(this).remove();
				}
			});
			copyElement.closest('table').append(element);
			thisInstance.deleteRow(element);
			thisInstance.lastRow();
			
			var widgetsList = element.find('select');
			widgetsList.select2({});
			
			thisInstance.setReadOnly(element);
		});
	},
	
	this.getRoles = function(continer) {
		var thisInstance = this;
		var roles = new Array();
		continer.find('.rowtr select.role :selected').each(function(){
			if(jQuery(this).val() != 0)
				roles.push( jQuery(this).val() );
		});
		return roles;
	},
	
	this.setReadOnly = function(element) {
		var thisInstance = this;
		element.find('select.role').on('change', function(){
		jQuery(this).parent().hide();
		html = '<big>'+jQuery(this).find(':selected').text()+'</big>';
		jQuery(this).parent().parent().append(html);
		});
	},
	
	this.deleteRow = function(element) {
		var thisInstance = this;
		jQuery('.deleteRecordButton', element).on('click', function(){
			var removeRow = jQuery(this).closest('tr');
			role = removeRow.find('[name="role"]').val();
			storedValue = thisInstance.checkStoredValue(role);
			if(storedValue){
				var oldWidgetsToRole = Array();
				oldWidgetsToRole[role] = storedValue[role];
				overlap = removeRow.closest('.active').data('save');
				
				oldWidgetsToRole = jQuery.extend({}, oldWidgetsToRole);
				var params = {};
				params.data = {module: 'WidgetsManagement', parent: 'Settings', action: 'SaveWidgets', 'widgetsToRole': Array(),'oldWidgetsToRole': oldWidgetsToRole, 'overlap': overlap}
				params.async = false;
				params.dataType = 'json';
				AppConnector.request(params).then(
					function(data) {
						var response = data['result'];
						if ( response['success'] ) {    
							var parametry = {
								text:  response['message'],
								type: 'info'
								};
							Vtiger_Helper_Js.showPnotify(parametry);
							storedValue[role] = Array();
							var newValue = JSON.stringify(storedValue);
							jQuery('.active input[name="oldWidgets"]').val(newValue);
							removeRow.remove();
							thisInstance.lastRow();
						}
					},
					function(data,err){
						var parametry = {
						text: app.vtranslate('JS_ERROR_CONNECTING'),
						type: 'error'
						};
					Vtiger_Helper_Js.showPnotify(parametry);
					}
				);
			}else{
				removeRow.remove();
				thisInstance.lastRow();
			}
			
		});
	},

	this.checkStoredValue = function(role){
		var thisInstance = this;
		var storedValue = jQuery('.active input[name="oldWidgets"]');
		var oldValue = storedValue.val();
		if( oldValue != '' && typeof oldValue != 'undefined' ){
			oldValueArray = JSON.parse(oldValue);
			if(typeof oldValueArray[role] != 'undefined')
				return oldValueArray;
		}
		return false;
	},
	
	this.lastRow = function() {
		var thisInstance = this;
			jQuery('.active').each(function(){
				table = jQuery(this).find('.overlap');
				var element = jQuery(this).find('tr');
				var numRow = element.length;
				if(numRow == 2)
					table.hide();
				else
					table.show();
			});

	},

	this.checkDuplicate = function(elements, nawValues) {
		var thisInstance = this;
		var tab = Array();
		var i=0;
		var result = new Array();
		result['success']=false;
		if(elements.data('save') == 'inactive'){
			otherOverlap = 'mandatory';
			result['otherOverlap'] = 'JS_MANDATORY';
		}else{
			otherOverlap = 'inactive';
			result['otherOverlap'] = 'JS_INACTIVE';
		}
		var jsonOverlap = jQuery('.'+otherOverlap+' input[name="oldWidgets"]').val();
		if( jsonOverlap != '' && typeof jsonOverlap != 'undefined' )
			otherOverlapValue = JSON.parse(jsonOverlap);
		else
			otherOverlapValue = Array();
		for(role in otherOverlapValue){
			for( linkId in otherOverlapValue[role] ){
				if(nawValues[role] && thisInstance.in_array(otherOverlapValue[role][linkId], nawValues[role])){
					result['success'] = true;
					result['valueError'] = otherOverlapValue[role][linkId];
					break;
				}
			}
		}
		if(!result['success']){
			elements.find('tr.rowtr .role :selected').each(function(){
				if(thisInstance.in_array(jQuery(this).val(), tab)){
					result['success'] = true;
					return result;
				}
				tab[i] = jQuery(this).val();
				i++;
			});
		}
		return result;
	},

	this.in_array = function(s,a,b,i,t){for(i in a){t=a[i]==s;b?t=a[i]===s:'';if(t)break}return t||!1},
	
	this.saveRole = function() {
		var thisInstance = this;
		jQuery('.saveCondition').on('click', function(){
			var elements = jQuery(this).closest('.active');
			var saveBlock = elements.find('tr.rowtr');
			var tab = new Array();
			var element = '';
			saveBlock.each(function(){
				element = jQuery(this).find('[name="role"]').val();
				tab[element] = [];
				jQuery(this).find('[name^="widgets"] :selected').each( function() {
					tab[element].push( jQuery(this).val() );
				});
			});
			tab = jQuery.extend({}, tab);
			var checkDuplicate = thisInstance.checkDuplicate(elements, tab);
			if(checkDuplicate['success']){
				if(checkDuplicate['valueError']){
					var msg = app.vtranslate(checkDuplicate['otherOverlap'])+jQuery('[name^="widgets"] [value="'+checkDuplicate['valueError']+'"]').html();
				}else
					var msg = app.vtranslate('JS_DUPLIACATE_ENTRIES_FOUND_FOR_THE_VALUE');
				var parametry = {
					text: msg,
					type: 'error'
					};
				Vtiger_Helper_Js.showPnotify(parametry);
				return false;
			}
			var params = {};
			params.data = {module: 'WidgetsManagement', parent: 'Settings', action: 'SaveWidgets', 'widgetsToRole': tab,'oldWidgetsToRole': elements.find('[name="oldWidgets"]').val(), 'overlap': elements.data('save')}
			params.async = false;
			params.dataType = 'json';
			AppConnector.request(params).then(
				function(data) {
					var response = data['result'];
					if ( response['success'] ) {    
						var parametry = {
							text:  response['message'],
							type: 'info'
							};
						Vtiger_Helper_Js.showPnotify(parametry);
						var newValue = JSON.stringify(tab);
						jQuery('.'+elements.data('save')+' input[name="oldWidgets"]').val(newValue);
						
					}
				},
				function(data,err){
					var parametry = {
					text: app.vtranslate('JS_ERROR_CONNECTING'),
					type: 'error'
					};
				Vtiger_Helper_Js.showPnotify(parametry);
				}
			);
		});
	},
	
	this.registerModulesChangeEvent = function() {
		var thisInstance = this;
		var container = jQuery('#widgetsManagementEditorContainer');
		var contentsDiv = container.closest('.contentsDiv');

		app.showSelect2ElementView(container.find('[name="widgetsManagementEditorModules"]'), {dropdownCss : {'z-index' : 0}});

		container.on('change', '[name="widgetsManagementEditorModules"]', function(e) {
			var currentTarget = jQuery(e.currentTarget);
			var selectedModule = currentTarget.val();
			thisInstance.getModuleWidgetsManagement(selectedModule).then(
				function(data) {
					contentsDiv.html(data);
					thisInstance.registerEvents();
				}
			);
		});
	},
	
	this.getModuleWidgetsManagement = function(selectedModule) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});

		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['view'] = 'Configuration';
		params['sourceModule'] = selectedModule;

		AppConnector.requestPjax(params).then(
			function(data) {
				progressIndicatorElement.progressIndicator({'mode' : 'hide'});
				aDeferred.resolve(data);
			},
			function(error) {
				progressIndicatorElement.progressIndicator({'mode' : 'hide'});
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},
	this.multiselectAdd = function(){
		var widgetsList = jQuery('.rowtr select[name^="widgets"]');
			widgetsList.select2({});
	},
	
	this.registerEvents = function() {
		var thisInstance = this;
		var view = app.getViewName();
		var moduleName = app.getModuleName();
		thisInstance.registerModulesChangeEvent();
		thisInstance.addCondition();
		thisInstance.multiselectAdd();
		var element = jQuery('.contents');
		thisInstance.deleteRow(element);
		thisInstance.lastRow();
		thisInstance.saveRole();
	};
}

jQuery(document).ready(function() {
    var dc = new WidgetsManagement();
    dc.registerEvents();
})