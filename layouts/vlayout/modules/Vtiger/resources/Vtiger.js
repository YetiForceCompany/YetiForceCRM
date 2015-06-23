/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/

var Vtiger_Index_Js = {

	/**
	 * Function to show email preview in popup
	 */
	showEmailPreview : function(recordId, parentId) {
		var popupInstance = Vtiger_Popup_Js.getInstance();
		var params = {};
		params['module'] = "Emails";
		params['view'] = "ComposeEmail";
		params['mode'] = "emailPreview";
		params['record'] = recordId;
		params['parentId'] = parentId;
		params['relatedLoad'] = true;
		popupInstance.show(params);
	},

	registerWidgetsEvents : function() {
		var widgets = jQuery('div.widgetContainer');
		widgets.on('shown.bs.collapse',function(e){
			var widgetContainer = jQuery(e.currentTarget);
			Vtiger_Index_Js.loadWidgets(widgetContainer);
			var key = widgetContainer.attr('id');
			app.cacheSet(key, 1);
		});
		widgets.on('hidden.bs.collapse',function(e){
			var widgetContainer = jQuery(e.currentTarget);
			var imageEle = widgetContainer.parent().find('.imageElement');
			var imagePath = imageEle.data('rightimage');
			imageEle.attr('src',imagePath);
			var key = widgetContainer.attr('id');
			app.cacheSet(key, 0);
		});
	},

	/**
	 * Function is used to load the sidebar widgets
	 * @param widgetContainer - widget container
	 * @param open - widget should be open or closed
	 */
	loadWidgets : function(widgetContainer, open) {
		var message = jQuery('.loadingWidgetMsg').html();
		if(widgetContainer.find('.panel-body').html() != '') {
			var imageEle = widgetContainer.parent().find('.imageElement');
			var imagePath = imageEle.data('downimage');
			imageEle.attr('src',imagePath);
			widgetContainer.css('height', 'auto');
			return;
		}

		widgetContainer.progressIndicator({'message' : message});
		var url = widgetContainer.data('url');
		var listViewWidgetParams = {
			"type":"GET", "url":"index.php",
			"dataType":"html", "data":url
		}
		AppConnector.request(listViewWidgetParams).then(
			function(data){
			if(typeof open == 'undefined') open = true;
            	if(open){
					widgetContainer.progressIndicator({'mode':'hide'});
					var imageEle = widgetContainer.parent().find('.imageElement');
					var imagePath = imageEle.data('downimage');
					imageEle.attr('src',imagePath);
					widgetContainer.css('height', 'auto');
				}
				widgetContainer.html(data);
				var label = widgetContainer.closest('.quickWidget').find('.quickWidgetHeader').data('label');
				jQuery('.bodyContents').trigger('Vtiger.Widget.Load.'+label,jQuery(widgetContainer));
			}
		);
	},

	loadWidgetsOnLoad : function(){
		var widgets = jQuery('div.widgetContainer');
		widgets.each(function(index,element){
			var widgetContainer = jQuery(element);
			var key = widgetContainer.attr('id');
			var value = app.cacheGet(key);
			if(value != null){
				if(value == 1) {
					Vtiger_Index_Js.loadWidgets(widgetContainer);
					widgetContainer.addClass('in');
				} else {
					var imageEle = widgetContainer.parent().find('.imageElement');
					var imagePath = imageEle.data('rightimage');
					imageEle.attr('src',imagePath);
				}
			}
		});
	},

	/**
	 * Function to change user theme(colour)
	 * @params : colour name
	 */
	changeSkin : function() {
		jQuery('.themeElement').on('click', function(e) {
			e.stopPropagation();
			var currentElement = jQuery(e.currentTarget);
			currentElement.closest('#themeContainer').hide();

			var progressElement = jQuery('#progressDiv');
			progressElement.progressIndicator();

			var params = {
				'module' : 'Users',
				'action' : 'SaveAjax',
				'record' : jQuery('#current_user_id').val(),
				'field'	 : 'theme',
				'value'	 : currentElement.data('skinName')
			}
			AppConnector.request(params).then(function(data) {
				if(data.success && data.result) {
					progressElement.progressIndicator({'mode':'hide'});
					jQuery('.settingIcons').removeClass('open');
					window.location.reload();
				}
			},
			function(error,err){
			});
		})
	},

	/**
	 * Function to show compose email popup based on number of
	 * email fields in given module,if email fields are more than
	 * one given option for user to select email for whom mail should
	 * be sent,or else straight away open compose email popup
	 * @params : accepts params object
	 *
	 * @cb: callback function to recieve the child window reference.
	 */

	showComposeEmailPopup : function(params, cb){
		var currentModule = "Emails";
		Vtiger_Helper_Js.checkServerConfig(currentModule).then(function(data){
			if(data == true){
				var css = jQuery.extend({'text-align' : 'left'},css);
				AppConnector.request(params).then(
					function(data) {
						var cbargs = [];
						if(data) {
							data = jQuery(data);
							var form = data.find('#SendEmailFormStep1');
							var emailFields = form.find('.emailField');
							var length = emailFields.length;
							var emailEditInstance = new Emails_MassEdit_Js();
							if(length > 1) {
								app.showModalWindow(data,function(data){
									emailEditInstance.registerEmailFieldSelectionEvent();
									if( jQuery('#multiEmailContainer').height() > 300 ){
										jQuery('#multiEmailContainer').slimScroll({
											height: '300px',
											railVisible: true,
											alwaysVisible: true,
											size: '6px'
										});
									}
								},css);
							} else {
								emailFields.attr('checked','checked');
								var params = form.serializeFormData();
								// http://stackoverflow.com/questions/13953321/how-can-i-call-a-window-child-function-in-javascript
								// This could be useful for the caller to invoke child window methods post load.
								var win = emailEditInstance.showComposeEmailForm(params);
								cbargs.push(win);
							}
						}
						if (typeof cb == 'function') cb.apply(null, cbargs);
					},
					function(error,err){

					}
				);
			} else {
				Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
			}
		})

	},

	/**
	 * Function registers event for Calendar Reminder popups
	 */
	registerActivityReminder : function() {
		var activityReminder = jQuery('#activityReminder').val();
		activityReminder = activityReminder * 1000;
		if(activityReminder != '') {
			var currentTime = new Date().getTime()/1000;
			var nextActivityReminderCheck = app.cacheGet('nextActivityReminderCheckTime', 0);
			if((currentTime + activityReminder) > nextActivityReminderCheck) {
				Vtiger_Index_Js.requestReminder(true);
				setTimeout('Vtiger_Index_Js.requestReminder()', activityReminder);
				app.cacheSet('nextActivityReminderCheckTime', currentTime + parseInt(activityReminder));
			}
		}
	},

	/**
	 * Function request for reminder popups
	 */
	requestReminder : function(typeRemainder) {
		var url = 'index.php?module=Calendar&action=ActivityReminder&mode=getReminders';
		if (typeRemainder) {
			url += '&type_remainder=true';
		} 
		document.notify = [];
		AppConnector.request(url).then(function(data){
			if(data.success && data.result) {
				for(i=0; i< data.result.length; i++) {
					var record  = data.result[i];
					Vtiger_Index_Js.showReminderPopup(record);
				}
			}
		});
	},

	/**
	 * Function display the Reminder popup
	 */
	showReminderPopup : function(record) {
		var data_info = '';
		if(record.contact_id != '' && record.contact_id != undefined){
			data_info += '<span class="col-md-12">'+app.vtranslate('JS_CONTACT_NAME')+' : <strong>'+record.contact_id+'</strong></span>';
		}
		if(record.link != '0' && record.link != undefined){
			data_info += '<span class="col-md-12">'+app.vtranslate('JS_RELATION')+' : <strong>'+record.link+'</strong></span>';
		}
		if(record.process != '0' && record.process != undefined){
			data_info += '<span class="col-md-12">'+app.vtranslate('JS_PROCESS')+' : <strong>'+record.process+'</strong></span>';
		}
		if(record.location != '' && record.location != undefined){
			data_info += '<span class="col-md-12" style="margin-top: 5px;"><a class="btn btn-default" target="_blank" href="https://www.google.com/maps/search/'+record.location+'"><i class="icon-map-marker"></i>&nbsp;'+record.location+'</a></span>';
		}
		if(record.mailUrl != '' && record.mailUrl != undefined){
			data_info += '<span class="col-md-12" style="margin-top: 5px;">'+record.mailUrl+'</span>';
		}
		
		var params = {
			title: '&nbsp;&nbsp;<span style="position: relative; top: 8px;">'+record.activitytype+' - '+
					'<a target="_blank" href="index.php?module=Calendar&view=Detail&record='+record.id+'">'+record.subject+'</a></span>',
			text: '<div data-record="'+record.id+'" class="row calendar_info" style="color:black">\n\
				<span class="col-md-12">'+app.vtranslate('JS_START_DATE_TIME')+' : '+record.date_start+'</span>\n\
				<span class="col-md-12">'+app.vtranslate('JS_END_DATE_TIME')+' : '+record.due_date+'</span>'
				+data_info+
				'<span class="col-md-12" style="margin-top: 5px;"><button title="' + app.vtranslate('NOTIFICATION_ACCEPTED') + '" class="btn btn-success btn-sm reminder_accept"><span class="glyphicon glyphicon-ok icon-white"></span></button>&nbsp;&nbsp;'+
				'<button class="btn btn-sm btn-primary reminder_postpone" data-time="15m">15'+app.vtranslate('JS_M')+'</button>&nbsp;&nbsp;'+
				'<button class="btn btn-sm btn-primary reminder_postpone" data-time="30m">30'+app.vtranslate('JS_M')+'</button>&nbsp;&nbsp;'+
				'<button class="btn btn-sm btn-primary reminder_postpone" data-time="1h">1'+app.vtranslate('JS_H')+'</button>&nbsp;&nbsp;'+
				'<button class="btn btn-sm btn-primary reminder_postpone" data-time="2h">2'+app.vtranslate('JS_H')+'</button>&nbsp;&nbsp;'+
				'<button class="btn btn-sm btn-primary reminder_postpone" data-time="1d">1'+app.vtranslate('JS_D')+'</button>&nbsp;&nbsp;'+
				'</div>',
			addclass:'vtReminder',
			icon: 'vtReminder-icon',
			hide:false,
			closer:false,
			type:'info',
			after_open:function(p) {
				jQuery(p).data('info', record);
			}
		};
		document.notify[record.id] = Vtiger_Helper_Js.showPnotify(params);
		jQuery('.reminder_accept').on('click', function(e) {
			var currentElement = jQuery(e.currentTarget);
			var recordID = currentElement.closest('.calendar_info').data('record');
			var url = 'index.php?module=Calendar&action=ActivityReminder&mode=cancelReminder&record='+recordID;
			document.notify[recordID].remove();
			AppConnector.request(url);
			
		});
		jQuery('.reminder_postpone').on('click', function(e) {
			var currentElement = jQuery(e.currentTarget);
			var recordID = currentElement.closest('.calendar_info').data('record');
			var url = 'index.php?module=Calendar&action=ActivityReminder&mode=postpone&record='+recordID+'&time='+currentElement.data('time');
			document.notify[recordID].remove();
			AppConnector.request(url);
		});	
	},
	registerResizeEvent: function(){
		$(window).resize(function() {
			if(this.resizeTO) clearTimeout(this.resizeTO);
			this.resizeTO = setTimeout(function() {
				$(this).trigger('resizeEnd');
			}, 600);
		});
		$(window).bind('resizeEnd', function() {
			Vtiger_Index_Js.adjustTopMenuBarItems();
		});
	},
	/**
	 * Function to make top-bar menu responsive.
	 */
	adjustTopMenuBarItems: function() {
		// Dedicated space for all dropdown text
		var TOLERANT_MAX_GAP = 125; // px
		var menuBarWrapper = ($(window).outerWidth() < 1161) ? jQuery('#mediumNav') : jQuery('#largeNav');
		var topMenuBarWidth = menuBarWrapper.parent().outerWidth();
		var optionalBarItems = jQuery('.opttabs', menuBarWrapper), optionalBarItemsCount = optionalBarItems.length;
		var optionalBarItemIndex = optionalBarItemsCount;
		function enableOptionalTopMenuItem() {
			var opttab  = (optionalBarItemIndex > 0) ? optionalBarItems[optionalBarItemIndex-1] : null;
			if (opttab) { opttab = jQuery(opttab); opttab.hide(); optionalBarItemIndex--; }
			return opttab;
		}
		// Loop and enable hidden menu item until the tolerant width is reached.
		var stopLoop = false;
		do {
			if((topMenuBarWidth - menuBarWrapper.outerWidth()) < TOLERANT_MAX_GAP){
				var lastOptTab = enableOptionalTopMenuItem();
				if (lastOptTab == null || (topMenuBarWidth - menuBarWrapper.outerWidth()) > TOLERANT_MAX_GAP) {
					if(lastOptTab) lastOptTab.hide();
					stopLoop = true; break;
				}
			}else{
				stopLoop = true; break;
			}
		} while (!stopLoop);
		// Required to get the functionality of All drop-down working.
		jQuery(window).load(function(){
			jQuery("#topMenus").css({'overflow':'visible'});
		});
	},

	/**
	 * Function to trigger tooltip feature.
	 */
	registerTooltipEvents: function() {
		var references = jQuery.merge(jQuery('[data-field-type="reference"] > a'), jQuery('[data-field-type="multireference"] > a'));
		var lastPopovers = [];

		// Fetching reference fields often is not a good idea on a given page.
		// The caching is done based on the URL so we can reuse.
		var CACHE_ENABLED = true; // TODO - add cache timeout support.

		function prepareAndShowTooltipView() {
			hideAllTooltipViews();

			var el = jQuery(this);
			var url = el.attr('href')? el.attr('href') : '';
			if (url == '') {
				return;
			}

			// Rewrite URL to retrieve Tooltip view.
			url = url.replace('view=', 'xview=') + '&view=TooltipAjax';

			var cachedView = CACHE_ENABLED ? jQuery('[data-url-cached="'+url+'"]') : null;
			if (cachedView && cachedView.length) {
				showTooltip(el, cachedView.html());
			} else {
				AppConnector.request(url).then(function(data){
					cachedView = jQuery('<div>').css({display:'none'}).attr('data-url-cached', url);
					cachedView.html(data);
					jQuery('body').append(cachedView);
					showTooltip(el, data);
				});
			}
		}

		function get_popover_placement(el) {
		  var width = window.innerWidth;
		  var left_pos = jQuery(el).offset().left;
		  if (width - left_pos > 400) return 'right';
		  return 'left';
		}

		function showTooltip(el, data) {
			var the_placement = get_popover_placement(el);
			el.popover({
				//title: '', - Is derived from the Anchor Element (el).
				trigger: 'manual',
				content: data,
				animation: false,
				html: true,
				placement:  the_placement,
				template: '<div class="popover popover-tooltip"><div class="arrow"></div><div class="popover-inner"><button name="vtTooltipClose" class="close" style="color:white;opacity:1;font-weight:lighter;position:relative;top:3px;right:3px;">x</button><h3 class="popover-title"></h3><div class="popover-content"><div></div></div></div></div>'
			});
			lastPopovers.push(el.popover('show'));
			registerToolTipDestroy();
		}

		function hideAllTooltipViews() {
			// Hide all previous popover
			var lastPopover = null;
			while (lastPopover = lastPopovers.pop()) {
				lastPopover.popover('hide');
			}
		}

		references.each(function(index, el){
			jQuery(el).hoverIntent({
				interval: 100,
				sensitivity: 7,
				timeout: 10,
				over: prepareAndShowTooltipView,
				out: hideAllTooltipViews
			});
		});

		function registerToolTipDestroy() {
			jQuery('button[name="vtTooltipClose"]').on('click', function(e){
				var lastPopover = lastPopovers.pop();
				lastPopover.popover('hide');
			});
		}
	},

	registerShowHideLeftPanelEvent : function() {
		jQuery('#toggleButton').click(function(e){
			e.preventDefault();
			var leftPanel = jQuery('#leftPanel');
			var centerContents = jQuery('#centerPanel');
			var rightPanel = document.getElementById('rightPanel');
			var tButtonImage = jQuery('#tButtonImage');
			if (leftPanel.attr('class').indexOf(' hide') == -1) {
                var leftPanelshow = 1;
				leftPanel.addClass('hide');
				if(rightPanel && jQuery(rightPanel).attr('class').indexOf('hide') == -1){
					centerContents.removeClass('col-md-8').addClass('col-md-10');
				}else{
					centerContents.removeClass('col-md-10').addClass('col-md-12');
				}
				tButtonImage.removeClass('glyphicon-chevron-left').addClass("glyphicon-chevron-right");
			} else {
                var leftPanelshow = 0;
				leftPanel.removeClass('hide');
				if(rightPanel && jQuery(rightPanel).attr('class').indexOf('hide') == -1){
					centerContents.removeClass('col-md-10').addClass('col-md-8');
				}else{
					centerContents.removeClass('col-md-12').addClass('col-md-10');
				}
				tButtonImage.removeClass('glyphicon-chevron-right').addClass("glyphicon-chevron-left");
			}
            var params = {
                'module' : 'Users',
                'action' : 'IndexAjax',
                'mode' : 'toggleLeftPanel',
                'showPanel' : leftPanelshow
            }
            AppConnector.request(params);
		});
	},
	registerShowHideRightPanelEvent : function() {
		jQuery('#toggleRightPanelButton').click(function(e){
			e.preventDefault();
			var leftPanel = jQuery('#leftPanel');
			var centerContents = jQuery('#centerPanel');
			var rightPanel = jQuery('#rightPanel');
			var tButtonImage = jQuery('#tRightPanelButtonImage');
			var leftPanelStatus = leftPanel.attr('class').indexOf(' hide');
			if (rightPanel.attr('class').indexOf('hide') == -1 ) {
				rightPanel.addClass('hide');
				if(leftPanelStatus == -1){
					centerContents.removeClass('col-md-8').addClass('col-md-10');
				}else{
					centerContents.removeClass('col-md-10').addClass('col-md-12');
				}
				tButtonImage.removeClass('glyphicon-chevron-right').addClass("glyphicon-chevron-left");
			} else {
				rightPanel.removeClass('hide');
				if(leftPanelStatus == -1){
					centerContents.removeClass('col-md-10').addClass('col-md-8');
				}else{
					centerContents.removeClass('col-md-12').addClass('col-md-10');
				}
				tButtonImage.removeClass('glyphicon-chevron-left').addClass("glyphicon-chevron-right");
			}
		});
	},

	loadPreSaveRecord : function(form) {
		SaveResult = new SaveResult()
		return SaveResult.checkData(form); 
	},
	registerEvents : function(){
		Vtiger_Index_Js.registerWidgetsEvents();
		Vtiger_Index_Js.loadWidgetsOnLoad();
		Vtiger_Index_Js.registerActivityReminder();
		Vtiger_Index_Js.adjustTopMenuBarItems();
		Vtiger_Index_Js.registerPostAjaxEvents();
		Vtiger_Index_Js.changeSkin();
		Vtiger_Index_Js.registerShowHideLeftPanelEvent();
		Vtiger_Index_Js.registerShowHideRightPanelEvent();
		Vtiger_Index_Js.registerResizeEvent();
	},

	registerPostAjaxEvents: function() {
		Vtiger_Index_Js.registerTooltipEvents();
	}
}


//On Page Load
jQuery(document).ready(function() {
	Vtiger_Index_Js.registerEvents();
	app.listenPostAjaxReady(function() {
		Vtiger_Index_Js.registerPostAjaxEvents();
	});
});
