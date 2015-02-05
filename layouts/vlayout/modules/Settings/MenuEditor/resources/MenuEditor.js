/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
jQuery.Class('Settings_Menu_Editor_Js', {}, {
	//This will store the MenuEditor Container
	menuEditorContainer : false,
	
	/**
	 * Function to get the MenuEditor container
	 */
	getContainer : function() {
		if(this.menuEditorContainer == false) {
			this.menuEditorContainer = jQuery('#menuEditorContainer');
		}
		return this.menuEditorContainer;
	},
	
	updateColor: function(e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		var container = target.closest('#menuEditorContainer');
		var editColorModal = container.find('.editColorContainer');
		var clonedContainer = editColorModal.clone(true, true);
		
		var callBackFunction = function(data) {
			data.find('.editColorContainer').removeClass('hide');
			var selectedColor = data.find('.selectedColor');
			selectedColor.val( closestTrElement.data('color') );
			//register color picker
			var params = {
				flat : true,
				color : closestTrElement.data('color'),
				onChange : function(hsb, hex, rgb) {
					selectedColor.val('#'+hex);
				}
			};
			if(typeof customParams != 'undefined'){
				params = jQuery.extend(params,customParams);
			}

			data.find('.calendarColorPicker').ColorPicker(params);
			
			//save the user calendar with color
			data.find('[name="saveButton"]').click(function(e) {
				var progress = $.progressIndicator({
					'message' : app.vtranslate('Update labels'),
					'blockInfo' : {
						'enabled' : true
					}
				});
				var settingMenuEditorInstance = new Settings_Menu_Editor_Js();
				settingMenuEditorInstance.registerSaveEvent('UpdateColor',{
					'color': selectedColor.val(),
					'id':closestTrElement.data('id'),
				});

				closestTrElement.find('.moduleColor').css('background',selectedColor.val());
				closestTrElement.data('color', selectedColor.val());
				app.hideModalWindow();
				progress.progressIndicator({'mode': 'hide'});
			});
		};
		app.showModalWindow(clonedContainer,function(data) {
			if(typeof callBackFunction == 'function') {
				callBackFunction(data);
			}
		}, {'width':'1000px'});
	},
	activeColor: function(e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		var params = {}
		params.data = {
			module: app.getModuleName(), 
			parent: app.getParentModuleName(), 
			action: 'SaveAjax', 
			mode: 'ActiveColor',
			params: {
				'status': target.is(':checked'),
				'color': closestTrElement.data('color'),
				'id':closestTrElement.data('id')
			}
		}
		params.async = false;
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
				if( closestTrElement.data('color') == ''){
					closestTrElement.find('.moduleColor').css('background', '#'+response['color']);
					closestTrElement.data('color','#'+response['color']);
				}
			}
        );
	},
	registerSaveEvent: function(mode, data) {
		var params = {}
		params.data = {
			module: app.getModuleName(), 
			parent: app.getParentModuleName(), 
			action: 'SaveAjax', 
			mode: mode,
			params: data
		}
		params.async = false;
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
			},
			function(data, err) {}
        );
	},
	
	registerEvents : function(e){
		var thisInstance = this;
		var container = thisInstance.getContainer();
		container.find('.updateColor').click(thisInstance.updateColor);
		container.find('.activeColor').click(thisInstance.activeColor);
	}
});


jQuery(document).ready(function(){
	var settingMenuEditorInstance = new Settings_Menu_Editor_Js();
	settingMenuEditorInstance.registerEvents();
})
