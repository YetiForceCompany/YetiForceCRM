/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Settings_Vtiger_Index_Js",{

	showMessage : function(customParams){
		var params = {};
		params.animation = "show";
		params.type = 'info';
		params.title = app.vtranslate('JS_MESSAGE');

		if(typeof customParams != 'undefined') {
			var params = jQuery.extend(params,customParams);
		}
		Vtiger_Helper_Js.showPnotify(params);
	}
},{

	registerDeleteShortCutEvent : function(shortCutBlock) {
		var thisInstance = this;
		if(typeof shortCutBlock == 'undefined') {
			var shortCutBlock = jQuery('div#settingsShortCutsContainer')
		}
		shortCutBlock.on('click','.unpin',function(e) {
			var actionEle = jQuery(e.currentTarget);
			var closestBlock = actionEle.closest('.moduleBlock');
			var fieldId = actionEle.data('id');
			var shortcutBlockActionUrl = closestBlock.data('actionurl');
			var actionUrl = shortcutBlockActionUrl+'&pin=false';
			var progressIndicatorElement = jQuery.progressIndicator({
				'blockInfo' : {
				'enabled' : true
				}
			});
			AppConnector.request(actionUrl).then(function(data) {
				if(data.result.SUCCESS == 'OK') {
					closestBlock.remove();
					thisInstance.registerSettingShortCutAlignmentEvent();
					var menuItemId = '#'+fieldId+'_menuItem';
					var shortCutActionEle = jQuery(menuItemId);
					var imagePath = shortCutActionEle.data('pinimageurl');
					shortCutActionEle.attr('src',imagePath).data('action','pin');
					progressIndicatorElement.progressIndicator({
						'mode' : 'hide'
					});
					var params = {
						title : app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('JS_SUCCESSFULLY_UNPINNED'),
						animation: 'show',
						type: 'info'
					};
                                        thisInstance.registerReAlign();
					Vtiger_Helper_Js.showPnotify(params);
				}
			});
			e.stopPropagation();
		});
	},

	registerPinUnpinShortCutEvent : function() {
		var thisInstance = this;
		var widgets = jQuery('div.widgetContainer');
		widgets.on('click','.pinUnpinShortCut',function(e){
			var shortCutActionEle = jQuery(e.currentTarget);
			var url = shortCutActionEle.closest('.menuItem').data('actionurl');
			var shortCutElementActionStatus = shortCutActionEle.data('action');
			if(shortCutElementActionStatus == 'pin'){
				var actionUrl = url+'&pin=true';
			} else {
				actionUrl = url+'&pin=false';
			}
			var progressIndicatorElement = jQuery.progressIndicator({
				'blockInfo' : {
				'enabled' : true
				}
			});
			AppConnector.request(actionUrl).then(function(data) {
				if(data.result.SUCCESS == 'OK') {
					if(shortCutElementActionStatus == 'pin'){
						var imagePath = shortCutActionEle.data('unpinimageurl');
						var unpinTitle = shortCutActionEle.data('unpintitle');
						shortCutActionEle.attr('src',imagePath).data('action','unpin').attr('title',unpinTitle);
						var params = {
							'fieldid' : shortCutActionEle.data('id'),
							'mode'  : 'getSettingsShortCutBlock',
							'module'  : 'Vtiger',
							'parent' : 'Settings',
							'view' : 'IndexAjax'
						}
						AppConnector.request(params).then(function(data){
//							var shortCutsMainContainer = jQuery('#settingsShortCutsContainer');
                                                        var shortCutsMainContainer = jQuery('#settingsShortCutsContainer');
                                                        var existingDivBlock=jQuery('#settingsShortCutsContainer div.row-fluid:last');
                                                        var count=jQuery('#settingsShortCutsContainer div.row-fluid:last').children("span").length;
                                                        if(count==3){
                                                           
                                                            var newBlock =jQuery('#settingsShortCutsContainer').append('<div class="row-fluid">'+data);
                                                        }
                                                        else{
                                                            var newBlock = jQuery(data).appendTo(existingDivBlock);
                                                        }

//							var newBlock = jQuery(data).appendTo(shortCutsMainContainer);
							thisInstance.registerSettingShortCutAlignmentEvent();
							progressIndicatorElement.progressIndicator({
								'mode' : 'hide'
							});
							var params = {
								text: app.vtranslate('JS_SUCCESSFULLY_PINNED')
							};
							Settings_Vtiger_Index_Js.showMessage(params);
						});
					} else {
						var imagePath = shortCutActionEle.data('pinimageurl');
						var pinTitle = shortCutActionEle.data('pintitle');
						shortCutActionEle.attr('src',imagePath).data('action','pin').attr('title',pinTitle);
						jQuery('#shortcut_'+shortCutActionEle.data('id')).remove();
						thisInstance.registerSettingShortCutAlignmentEvent();
						progressIndicatorElement.progressIndicator({
							'mode' : 'hide'
						});
						var params = {
							title : app.vtranslate('JS_MESSAGE'),
							text: app.vtranslate('JS_SUCCESSFULLY_UNPINNED'),
							animation: 'show',
							type: 'info'
						};
                                                thisInstance.registerReAlign();
						Vtiger_Helper_Js.showPnotify(params);
					}
				}
			});
		});
	},

	registerSettingsShortcutClickEvent : function() {
		jQuery('#settingsShortCutsContainer').on('click','.moduleBlock',function(e){
			var url = jQuery(e.currentTarget).data('url');
			window.location.href = url;
		});
	},

	registerSettingShortCutAlignmentEvent : function() {
		jQuery('#settingsShortCutsContainer').find('.moduleBlock').removeClass('marginLeftZero');
		jQuery('#settingsShortCutsContainer').find('.moduleBlock:nth-child(3n+1)').addClass('marginLeftZero');
	},

	registerWidgetsEvents : function() {
		var widgets = jQuery('div.widgetContainer');
		widgets.on({
			shown: function(e) {
				var widgetContainer = jQuery(e.currentTarget);
				var quickWidgetHeader = widgetContainer.closest('.quickWidget').find('.quickWidgetHeader');
				var imageEle = quickWidgetHeader.find('.imageElement')
				var imagePath = imageEle.data('downimage');
				imageEle.attr('src',imagePath);
			},
			hidden: function(e) {
				var widgetContainer = jQuery(e.currentTarget);
				var quickWidgetHeader = widgetContainer.closest('.quickWidget').find('.quickWidgetHeader');
				var imageEle = quickWidgetHeader.find('.imageElement')
				var imagePath = imageEle.data('rightimage');
				imageEle.attr('src',imagePath);
			}
		});
	},

	registerAddShortcutDragDropEvent : function() {
		var thisInstance = this;

		jQuery( ".menuItemLabel" ).draggable({
			appendTo: "body",
			helper: "clone"
		});
		jQuery( "#settingsShortCutsContainer" ).droppable({
			activeClass: "ui-state-default",
			hoverClass: "ui-state-hover",
			accept: ".menuItemLabel",
			drop: function( event, ui ) {
				var actionElement = ui.draggable.closest('.menuItem').find('.pinUnpinShortCut');
				var pinStatus = actionElement.data('action');
				if(pinStatus == 'unpin') {
					var params = {
						title : app.vtranslate('JS_MESSAGE'),
						text: app.vtranslate('JS_SHORTCUT_ALREADY_ADDED'),
						animation: 'show',
						type: 'info'
					};
					Vtiger_Helper_Js.showPnotify(params);
				} else {
					ui.draggable.closest('.menuItem').find('.pinUnpinShortCut').trigger('click');
					thisInstance.registerSettingShortCutAlignmentEvent();
				}
			}
		});
	},
        
        registerReAlign : function()
        {
          
            var params = {
							'mode'  : 'realignSettingsShortCutBlock',
							'module'  : 'Vtiger',
							'parent' : 'Settings',
							'view' : 'IndexAjax'
						}

						AppConnector.request(params).then(function(data){
                                                    jQuery('#settingsShortCutsContainer').html(data);
                                                
                                                });
        },

	registerEvents: function() {
		this.registerSettingsShortcutClickEvent();
		this.registerDeleteShortCutEvent();
		this.registerWidgetsEvents();
		this.registerPinUnpinShortCutEvent();
		this.registerAddShortcutDragDropEvent();
		this.registerSettingShortCutAlignmentEvent();
	}

});