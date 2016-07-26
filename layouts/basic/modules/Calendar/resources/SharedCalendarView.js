/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
var multipleEventsToLoad = 0;

Calendar_CalendarView_Js("SharedCalendar_SharedCalendarView_Js",{
		
	currentInstance : false,
	
	initiateCalendarFeeds : function() {
		Calendar_CalendarView_Js.currentInstance.performCalendarFeedIntiate();
	}
},{
	
	multipleEvents : {},
	multipleEventsToCalnedar : [],
	multipleEventsOnLoad : 0,
	
	getAllUserColors : function() {
		var result = {};
		var calendarfeeds = jQuery('[data-calendar-feed]');
		
		calendarfeeds.each(function(index,element){
			var feedcheckbox = jQuery(element);
			var disabledOnes = app.cacheGet('calendar.feeds.disabled',[]); 
			if (disabledOnes.indexOf(feedcheckbox.data('calendar-sourcekey')) == -1) { 
				feedcheckbox.attr('checked',true); 
				var id = feedcheckbox.data('calendar-userid'); 
				result[id] = feedcheckbox.data('calendar-feed-color')+','+feedcheckbox.data('calendar-feed-textcolor'); 
			}
		});
		
		return result;
	},
	
	fetchAllCalendarFeeds : function() {
		var thisInstance = this;
		var calendarfeeds = jQuery('[data-calendar-feed]');
		var calendarfeedschecked = jQuery('[data-calendar-feed].checked');
		multipleEventsToLoad = calendarfeedschecked.length+1;
		thisInstance.multipleEventsOnLoad = 1;
		calendarfeeds.each(function(index,element){
			var feedcheckbox = jQuery(element);
			thisInstance.getCalendarView().fullCalendar('removeEventSource', thisInstance.calendarfeedDS[feedcheckbox.data('calendar-sourcekey')]);
			if(feedcheckbox.attr('checked') == 'checked'){
				multipleEventsToLoad = --multipleEventsToLoad;
				thisInstance.fetchCalendarFeed(feedcheckbox);
			}
		});
		this.multipleEvents = false;
		thisInstance.multipleEventsOnLoad = 0;
	},
        
	fetchCalendarFeed : function(feedcheckbox) {
		var thisInstance = this;

		//var type = feedcheckbox.data('calendar-sourcekey');
		this.calendarfeedDS[feedcheckbox.data('calendar-sourcekey')] = function(start, end, callback) {
			if(typeof thisInstance.multipleEvents != 'undefined' && thisInstance.multipleEvents != false){
				var events = thisInstance.multipleEvents[feedcheckbox.data('calendar-userid')];
				
				if(events !== false && multipleEventsToLoad > 0){
					$.merge( thisInstance.multipleEventsToCalnedar , events );
					return;
				}

				if(thisInstance.multipleEventsToCalnedar.length != 0) {
					callback(thisInstance.multipleEventsToCalnedar);
					return;
				}else if(events !== false) {
					callback(events);
					return;
				}
			}
			
			if(feedcheckbox.not(':checked').length > 0) {
				callback([]);
				return;
			}
			feedcheckbox.attr('disabled', true);
			
			var params = {
				module: 'Calendar',
				action: 'Feed',
				start: app.getStringDate(start),
				end: app.getStringDate(end),
				type: feedcheckbox.data('calendar-feed'),
				userid : feedcheckbox.data('calendar-userid'),
				color : feedcheckbox.data('calendar-feed-color'),
				textColor : feedcheckbox.data('calendar-feed-textcolor')
			}

			AppConnector.request(params).then(function(events){
				callback(events);
				feedcheckbox.attr('disabled', false).attr('checked', true);
			},
            function(error){
                //To send empty events if error occurs
                callback([]);
            });
		}
		if(thisInstance.multipleEventsOnLoad == 1){
			this.getCalendarView().fullCalendar('addEventSource', this.calendarfeedDS[feedcheckbox.data('calendar-sourcekey')]);
		}
	},
	
	allocateColorsForAllUsers : function() {
		var calendarfeeds = jQuery('[data-calendar-feed]');
		calendarfeeds.each(function(index,element){
			var feedUserElement = jQuery(element);
			var feedUserLabel = feedUserElement.closest('.addedCalendars').find('.label');
			var sourcekey = feedUserElement.data('calendar-sourcekey');
			var color = feedUserElement.data('calendar-feed-color');
			if(color == '' || typeof color == 'undefined') {
				color = app.cacheGet(sourcekey);
				if(color != null){
				} else {
					color = '#'+(0x1000000+(Math.random())*0xffffff).toString(16).substr(1,6);
					app.cacheSet(sourcekey, color);
				}
				feedUserElement.data('calendar-feed-color',color);
				feedUserLabel.css({'background-color':color});
			}
			var colorContrast = app.getColorContrast(color.slice(1));
			if(colorContrast == 'light') {
				var textColor = 'black'
			} else {
				textColor = 'white'
			}
			feedUserElement.data('calendar-feed-textcolor',textColor);
			feedUserLabel.css({'color':textColor});
		});

	},
	
	fetchAllEvents : function() {
		var progress = jQuery.progressIndicator({
			'position' : '#calendarview',
			'blockInfo' : {
				'enabled' : true
			}
		});
		var thisInstance = this;
		var result = this.getAllUserColors();
		var params = {
			module: 'Calendar',
			action: 'Feed',
			start: app.getStringDate(thisInstance.getCalendarView().fullCalendar('getView').visStart),
			end: app.getStringDate(thisInstance.getCalendarView().fullCalendar('getView').visEnd),
			type: 'MultipleEvents',
			mapping : result
		}

		AppConnector.request(params).then(function(multipleEvents){
				thisInstance.multipleEvents = multipleEvents;
				thisInstance.fetchAllCalendarFeeds();
				progress.progressIndicator({'mode': 'hide'});
		},
		function(error){
			
		});
	},
	isAllowedToAddCalendarEvent : function(calendarDetails){
		var assignedUserId = calendarDetails.assigned_user_id.value;
		if(jQuery('[data-calendar-userid='+assignedUserId+']').is(':checked')) {
			return true;
		} else {
			return false;
		}
		
	},
	addCalendarEvent : function(calendarDetails) {
		if(calendarDetails.activitytype.value == 'Task'){
			var msg = app.vtranslate('JS_TASK_IS_SUCCESSFULLY_ADDED_TO_YOUR_CALENDAR');
			var customParams = {
				text : msg,
				 type: 'info'
			}
			Vtiger_Helper_Js.showPnotify(customParams);
			return;
		} else {
			this._super(calendarDetails);
		}
	},
	
	/**
	 * Function used to delete user calendar
	 */
	deleteCalendarView : function(feedcheckbox) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var params = {
			module: 'Calendar',
			action: 'CalendarUserActions',
			mode : 'deleteUserCalendar',
			userid : feedcheckbox.data('calendar-userid')
		}
		
		AppConnector.request(params).then(function(response) {
			var result = response['result'];
			
			feedcheckbox.closest('.addedCalendars').remove();
			//After delete user reset accodion height to auto
			thisInstance.resetAccordionHeight();
			//Remove the events of deleted user in shared calendar feed
			thisInstance.getCalendarView().fullCalendar('removeEventSource', thisInstance.calendarfeedDS[feedcheckbox.data('calendar-sourcekey')]);
			
			//Update the adding and editing users list in hidden modal
			var userSelectElement = jQuery('#calendarview-feeds').find('[name="usersCalendarList"]');
			userSelectElement.append('<option value="'+result['sharedid']+'">'+result['username']+'</option>');
			var editUserSelectElement = jQuery('#calendarview-feeds').find('[name="editingUsersList"]');
			editUserSelectElement.find('option[value="'+result['sharedid']+'"]').remove();
			jQuery('#calendarview-feeds').find('.invisibleCalendarViews').val('true');
			
			aDeferred.resolve();
		},
		function(error){
			aDeferred.reject();
		});
		
		return aDeferred.promise();
	},
	
	/**
	 * Function to register event for edit user calendar color
	 */
	registerEventForEditUserCalendar : function() {
		var thisInstance = this;
		var parentElement = jQuery('#calendarview-feeds');
		parentElement.on('click', '.editCalendarColor', function(e) {
			e.preventDefault();
			var currentTarget = jQuery(e.currentTarget);
			var addedCalendarEle = currentTarget.closest('.addedCalendars');
			var feedUserEle = addedCalendarEle.find('[data-calendar-feed]');
			var editCalendarViewsList = jQuery('#calendarview-feeds').find('.editCalendarViewsList');
			var selectElement = editCalendarViewsList.find('[name="editingUsersList"]');
			selectElement.find('option:selected').removeAttr('selected');
			selectElement.find('option[value="'+feedUserEle.data('calendar-userid')+'"]').attr('selected', true);
			thisInstance.showAddUserCalendarModal(currentTarget);
		})
	},
	
	/**
	 * Function to register change event for users list select element in edit user calendar modal
	 */
	registerViewsListChangeEvent : function(data) {
		var parentElement = jQuery('#calendarview-feeds');
		var selectElement = data.find('[name="editingUsersList"]');
		var selectedUserColor = data.find('.selectedUserColor');
		//on change of edit user, update color picker with the selected user color
		selectElement.on('change', function() {
			var userid = selectElement.find('option:selected').val();
			var userColor = jQuery('[data-calendar-userid="'+userid+'"]', parentElement).data('calendar-feed-color');
			selectedUserColor.val(userColor);
			data.find('.calendarColorPicker').ColorPickerSetColor(userColor)
		});
	},
	
	/**
	 * Function to save added user calendar
	 */
	saveUserCalendar : function(data, currentEle) {
		var thisInstance = this;
		var userColor = data.find('.selectedUserColor').val();
		var userId = data.find('.selectedUser').val();
		var userName = data.find('.selectedUser').data('username');
		var params = {
			module: 'Calendar',
			action: 'CalendarUserActions',
			mode : 'addUserCalendar',
			selectedUser : userId,
			selectedColor : userColor
		};
		
		AppConnector.request(params).then(function() {
			app.hideModalWindow();
			
			var parentElement = jQuery('#calendarview-feeds');
			var colorContrast = app.getColorContrast(userColor.slice(1));
			if(colorContrast == 'light') {
				var textColor = 'black'
			} else {
				textColor = 'white'
			}
			if(data.find('.userCalendarMode').val() == 'edit') {
				var feedUserEle = jQuery('[data-calendar-userid="'+userId+'"]', parentElement);
				feedUserEle.data('calendar-feed-color',userColor).data('calendar-feed-textcolor',textColor);
				feedUserEle.closest('.addedCalendars').find('.label').css({'background-color':userColor,'color':textColor});
				
				thisInstance.getCalendarView().fullCalendar('removeEventSource', thisInstance.calendarfeedDS[feedUserEle.data('calendar-sourcekey')]);
				thisInstance.fetchCalendarFeed(feedUserEle);
				
				//notification message
				var message = app.vtranslate('JS_CALENDAR_VIEW_COLOR_UPDATED_SUCCESSFULLY');
			} else {
				var labelModal = jQuery('.labelModal', parentElement);
				var clonedContainer = labelModal.clone(true, true);
				var labelView = clonedContainer.find('label');
				feedUserEle = labelView.find('[type="checkbox"]');
				feedUserEle.attr('checked', 'checked');
				feedUserEle.attr('data-calendar-feed-color',userColor).attr('data-calendar-feed', 'Events').attr('data-calendar-userid', userId)
						.attr('data-calendar-sourcekey', 'Events33_'+userId).attr('data-calendar-feed-textcolor',textColor);
				feedUserEle.closest('.addedCalendars').find('.label').css({'background-color':userColor,'color':textColor}).text(userName);
				parentElement.append(labelView);
				
				//After add user reset accodion height to auto
				thisInstance.resetAccordionHeight();
				
				thisInstance.fetchCalendarFeed(feedUserEle);
				
				//Update the adding and editing users list in hidden modal
				var userSelectElement = jQuery('#calendarview-feeds').find('[name="usersCalendarList"]');
				userSelectElement.find('option[value="'+userId+'"]').remove();
				
				if(userSelectElement.find('option').length <= 0) {
					jQuery('#calendarview-feeds').find('.invisibleCalendarViews').val('false');
				}
				
				var editUserSelectElement = jQuery('#calendarview-feeds').find('[name="editingUsersList"]');
				editUserSelectElement.append('<option value="'+userId+'">'+userName+'</option>');
				
				//notification message
				var message = app.vtranslate('JS_CALENDAR_VIEW_ADDED_SUCCESSFULLY');
			}
			
			//show notification after add or edit user
			var params = {
				text: message,
				type: 'info'
			};
			Vtiger_Helper_Js.showPnotify(params);
		},
		function(error){
			
		});
		
	},
	
	performCalendarFeedIntiate : function() {
		this.allocateColorsForAllUsers();
		this.fetchAllEvents();
		this.registerCalendarFeedChange();
		this.registerEventForDeleteUserCalendar();
		this.registerEventForEditUserCalendar();
		this.resetAccordionHeight();
	},
	registerCalendarFeedChange : function() {
		var thisInstance = this;
		//thisInstance.multipleEventsToLoad = 0;
		jQuery('#calendarview-feeds').on('change', '[data-calendar-feed]', function(e){
			var progress = jQuery.progressIndicator({
				'position' : '#calendarview',
				'blockInfo' : {
					'enabled' : true
				}
			});
			var currentTarget = $(e.currentTarget);
			var type = currentTarget.data('calendar-sourcekey');
			if(currentTarget.is(':checked')) {
				// NOTE: We are getting cache data fresh - as it shared between browser tabs
				var disabledOnes = app.cacheGet('calendar.feeds.disabled',[]);
				// http://stackoverflow.com/a/3596096
				disabledOnes = jQuery.grep(disabledOnes, function(value){return value != type;});
				app.cacheSet('calendar.feeds.disabled', disabledOnes);

				if(!thisInstance.calendarfeedDS[type]){
                	//thisInstance.fetchAllCalendarFeeds();
					thisInstance.fetchCalendarFeed(currentTarget);
				}
				thisInstance.getCalendarView().fullCalendar('addEventSource', thisInstance.calendarfeedDS[type]);
			} else {
				// NOTE: We are getting cache data fresh - as it shared between browser tabs
				var disabledOnes = app.cacheGet('calendar.feeds.disabled',[]);
				if (disabledOnes.indexOf(type) == -1) disabledOnes.push(type);
				app.cacheSet('calendar.feeds.disabled', disabledOnes);

				thisInstance.getCalendarView().fullCalendar('removeEventSource', thisInstance.calendarfeedDS[type]);
			}
			progress.progressIndicator({'mode': 'hide'});
		});
	},
	restoreAddCalendarWidgetState : function() {
		var key = 'Calendar_sideBar_LBL_ADDED_CALENDARS'; 
		var value = app.cacheGet(key);
		var widgetContainer = jQuery("#Calendar_sideBar_LBL_ADDED_CALENDARS");
		if(value == 0){ 
			Vtiger_Index_Js.loadWidgets(widgetContainer,false);
		} 
		else{ 
			Vtiger_Index_Js.loadWidgets(widgetContainer);
		}
	},
	
	registerEvents : function() {
		this._super();
		this.restoreAddCalendarWidgetState();
		return this;
	}
});
