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

			var copyElement = jQuery(this).closest('td').find('.copyRow');
			var element = copyElement.clone(true,true);

			element.removeAttr('class');
			element.attr('class', 'rowtr');
			copyElement.closest('table').append(element);

			thisInstance.deleteRow();
			thisInstance.lastRow(copyElement.closest('table'));
			var widgetsList = element.find('[name^="widgets"]');
			widgetsList.select2({});
		});
	},
	
	this.deleteRow = function() {
		var thisInstance = this;
		jQuery('.deleteRecordButton').on('click', function(){
			var allRowInTable = jQuery(this).closest('table');
			var removeRow = jQuery(this).closest('tr');
			removeRow.remove();
			thisInstance.lastRow(allRowInTable);
		});
	},

	this.lastRow = function(allRowInTable) {
		var thisInstance = this;
		if(allRowInTable == false){
			jQuery('.condition').each(function(){
				allRowInTable = jQuery(this);
				var element = allRowInTable.find('.deleteRecordButton');
				var numRow = element.length;
				if(numRow == 2)
					element.hide();
				else
					element.show();
			});
		} else {
			var element = allRowInTable.find('.deleteRecordButton');
			var numRow = element.length;
			if(numRow == 2)
				element.hide();
			else
				element.show();
		}
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
		if(typeof jsonOverlap != 'undefined')
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
			var elements = jQuery(this).closest('td');
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
		var widgetsList = jQuery('.rowtr [name^="widgets"]');
			widgetsList.select2({});
	},
	
	this.registerEvents = function() {
		var thisInstance = this;
		var view = app.getViewName();
		var moduleName = app.getModuleName();
		thisInstance.registerModulesChangeEvent();
		thisInstance.addCondition();
		thisInstance.multiselectAdd();
		thisInstance.deleteRow();
		thisInstance.lastRow(false);
		thisInstance.saveRole();
	};
}

jQuery(document).ready(function() {
    var dc = new WidgetsManagement();
    dc.registerEvents();
})