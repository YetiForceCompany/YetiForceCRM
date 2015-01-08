/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/


jQuery.Class("Calendar_CalendarView_Js",{

	currentInstance : false,

	getInstanceByView : function(){
	    var view = jQuery('#currentView').val();
		var jsFileName = view+'View';
	    var moduleClassName = view+"_"+jsFileName+"_Js";
	    if(typeof window[moduleClassName] != 'undefined'){
			var instance = new window[moduleClassName]();
		} else {
			instance = new Calendar_CalendarView_Js();
		}
	    return instance;
	},

	initiateCalendarFeeds : function() {
		Calendar_CalendarView_Js.currentInstance.performCalendarFeedIntiate();
	}
},{

	calendarView : false,
	calendarCreateView : false,
	//Hold the conditions for a hour format
	hourFormatConditionMapping : false,

	//Hold the saved values of calendar settings
	calendarSavedSettings : false,

	CalendarSettingsContainer : false,

	weekDaysArray : {Sunday : 0,Monday : 1, Tuesday : 2, Wednesday : 3,Thursday : 4, Friday : 5, Saturday : 6},

	calendarfeedDS : {},

	getCalendarView : function() {
		if(this.calendarView == false) {
			this.calendarView = jQuery('#calendarview');
		}
		return this.calendarView;
	},

	getCalendarCreateView : function() {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();

		if(this.calendarCreateView !== false) {
			aDeferred.resolve(this.calendarCreateView.clone(true,true));
			return aDeferred.promise();
		}
		var progressInstance = jQuery.progressIndicator();
		this.loadCalendarCreateView().then(
			function(data){
				progressInstance.hide();
				thisInstance.calendarCreateView = data;
				aDeferred.resolve(data.clone(true,true));
			},
			function(){
				progressInstance.hide();
			}
		);
		return aDeferred.promise();
	},

	loadCalendarCreateView : function() {
		var aDeferred  = jQuery.Deferred();
		var quickCreateCalendarElement = jQuery('#quickCreateModules').find('[data-name="Calendar"]');
		var url = quickCreateCalendarElement.data('url');
		var name = quickCreateCalendarElement.data('name');

		var headerInstance = new Vtiger_Header_Js();
		headerInstance.getQuickCreateForm(url,name).then(
			function(data){
				aDeferred.resolve(jQuery(data));
			},
			function(){
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},

	fetchCalendarFeed : function(feedcheckbox) {
		var type = feedcheckbox.data('calendar-sourcekey');
		this.calendarfeedDS[type] = function(start, end, callback) {
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
				fieldname: feedcheckbox.data('calendar-fieldname'),
				userid : feedcheckbox.data('calendar-userid'),
				color : feedcheckbox.data('calendar-feed-color'),
				textColor : feedcheckbox.data('calendar-feed-textcolor')
			}
			var customData = feedcheckbox.data('customData');
			if( customData != undefined) {
				params = jQuery.extend(params, customData);
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

		this.getCalendarView().fullCalendar('addEventSource', this.calendarfeedDS[type]);
	},

	fetchAllCalendarFeeds : function(calendarfeedidx) {
		var thisInstance = this;
		var calendarfeeds = jQuery('[data-calendar-feed]');

		//TODO : see if you get all the feeds in one request
		calendarfeeds.each(function(index,element){
			var feedcheckbox = jQuery(element);
			var	disabledOnes = app.cacheGet('calendar.feeds.disabled',[]);
			if (disabledOnes.indexOf(feedcheckbox.data('calendar-sourcekey')) == -1) {
				feedcheckbox.attr('checked',true);
			}
			thisInstance.fetchCalendarFeed(feedcheckbox);
		});
	},
	
	allocateColorsForAllActivityTypes : function() {
		var calendarfeeds = jQuery('[data-calendar-feed]');
		calendarfeeds.each(function(index,element){
			var feedUserElement = jQuery(element);
			var feedUserLabel = feedUserElement.closest('.addedCalendars').find('.label')
			var color = feedUserElement.data('calendar-feed-color');
			var feedModule = feedUserElement.data('calendar-feed');
			var feedFieldName = feedUserElement.data('calendar-feed-fieldname');
			var sourcekey = feedModule+'_'+feedFieldName;
			if(color == '' || typeof color == 'undefined') {
				color = app.cacheGet(sourcekey);
				if(color != null) {
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
	
	performCalendarFeedIntiate : function() {
		this.allocateColorsForAllActivityTypes();
		this.registerCalendarFeedChange();
		this.fetchAllCalendarFeeds();
		//this.registerEventForEditUserCalendar();
		this.registerEventForDeleteUserCalendar();
	},

	registerCalendarFeedChange : function() {
		var thisInstance = this;
		jQuery('#calendarview-feeds').on('change', '[data-calendar-feed]', function(e){
			var currentTarget = $(e.currentTarget);
			var type = currentTarget.data('calendar-sourcekey');
			if(currentTarget.is(':checked')) {
				// NOTE: We are getting cache data fresh - as it shared between browser tabs
				var disabledOnes = app.cacheGet('calendar.feeds.disabled',[]);
				// http://stackoverflow.com/a/3596096
				disabledOnes = jQuery.grep(disabledOnes, function(value){return value != type;});
				app.cacheSet('calendar.feeds.disabled', disabledOnes);

				if(!thisInstance.calendarfeedDS[type]){
                	thisInstance.fetchAllCalendarFeeds();
				}
				thisInstance.getCalendarView().fullCalendar('addEventSource', thisInstance.calendarfeedDS[type]);
			} else {
				// NOTE: We are getting cache data fresh - as it shared between browser tabs
				var disabledOnes = app.cacheGet('calendar.feeds.disabled',[]);
				if (disabledOnes.indexOf(type) == -1) disabledOnes.push(type);
				app.cacheSet('calendar.feeds.disabled', disabledOnes);

				thisInstance.getCalendarView().fullCalendar('removeEventSource', thisInstance.calendarfeedDS[type]);
			}
		});
	},

	dayClick : function(date, allDay, jsEvent, view){
		var thisInstance = this;
		this.getCalendarCreateView().then(function(data){
			if(data.length <= 0) {
				return;
			}
			var dateFormat = data.find('[name="date_start"]').data('dateFormat');

			var startDateInstance = Date.parse(date);
			var startDateString = app.getDateInVtigerFormat(dateFormat,startDateInstance);
			var startTimeString = startDateInstance.toString('hh:mm tt');

			var endDateInstance = Date.parse(date);
			if(data.find('[name="activitytype"]').val() == 'Call'){
				var defaulCallDuration = data.find('[name="defaultCallDuration"]').val();
				endDateInstance.addMinutes(defaulCallDuration);
			} else {
				var defaultOtherEventDuration = data.find('[name="defaultOtherEventDuration"]').val();
				endDateInstance.addMinutes(defaultOtherEventDuration);
			}
			var endDateString = app.getDateInVtigerFormat(dateFormat,endDateInstance);
			var endTimeString = endDateInstance.toString('hh:mm tt');

			data.find('[name="date_start"]').val(startDateString);
			data.find('[name="due_date"]').val(endDateString);

			data.find('[name="time_start"]').val(startTimeString);
			data.find('[name="time_end"]').val(endTimeString);

			var headerInstance = new Vtiger_Header_Js();
			headerInstance.handleQuickCreateData(data, {callbackFunction:function(data){
					thisInstance.addCalendarEvent(data.result);
			}});
                    
                    thisInstance.getPlannedEvents();
                    jQuery('[name="date_start"]').on('change', function() {
                        thisInstance.getPlannedEventsClearTable();
                        thisInstance.getPlannedEvents();
                    });
		    jQuery('.modal-body').css({'max-height' : '500px', 'overflow-y': 'auto'});
		});
                


	},
    
     getPlannedEventsClearTable: function() {
        jQuery('#cur_events .table tr').next().remove();
        jQuery('#prev_events .table tr').next().remove();
        jQuery('#next_events  .table tr').next().remove();
    },
    getPlannedEvents: function() {
        this.getSingleEventType('0', 'cur_events', 'MultipleEvents');
        this.getSingleEventType('0', 'cur_events', 'Calendar');
        this.getSingleEventType('-1', 'prev_events', 'MultipleEvents');
        this.getSingleEventType('-1', 'prev_events', 'Calendar');
        this.getSingleEventType('+1', 'next_events', 'MultipleEvents');
        this.getSingleEventType('+1', 'next_events', 'Calendar');
    },
    
    getEndDate: function(startDate) {
        var dateTab = startDate.split('-');
        var date = new Date(dateTab[0], dateTab[1], dateTab[2]);
        var newDate = new Date();
        
        newDate.setDate(date.getDate() + 2);
        return app.getStringDate(newDate);
    },
    
    getSingleEventType: function(modDay, id, type) {
        var dateStartEl = jQuery('[name="date_start"]');
        var dateStartVal = jQuery(dateStartEl).val();
        var dateStartFormat = jQuery(dateStartEl).data('date-format');
        var validDateFromat = Vtiger_Helper_Js.convertToDateString(dateStartVal, dateStartFormat, modDay, type);
        var map = jQuery.extend({}, ['#b6a996,black'])
        var thisInstance = this;

        var params = {
            module: 'Calendar',
            action: 'Feed',
            start: validDateFromat,
            end: this.getEndDate(validDateFromat),
            type: type,
            mapping: map
        }

        AppConnector.request(params).then(function(events) {
            var testDate = Vtiger_Helper_Js.convertToDateString(dateStartVal, dateStartFormat, modDay);
            if (!jQuery.isEmptyObject(events)) {
                if (events[0]['activitytype'] === 'Task') {
                    for (var ev in events) {
                        if (events[ev]['start'].indexOf(testDate) > -1) {
                            jQuery('#' + id + ' .table').append('<tr><td style="padding: 2px;"><a target="_blank" href="' + events[ev]['url'] + '">' + thisInstance.getActivitIcon(events[ev]) + events[ev]['title'] + '</a></td></tr>');
                        }
                    }
                } else {
                    for (var i = 0; i < events[0].length; i++) {
                        if (events[0][i]['start'].indexOf(testDate) > -1) {
                            jQuery('#' + id + ' .table').append('<tr><td style="padding: 2px;"><a target="_blank" href="' + events[0][i]['url'] + '">' + thisInstance.getActivitIcon(events[0][i]) + events[0][i]['title'] + '</a></td></tr>');
                        }
                    }
                }
            }
        })
    },

    addCallMeetingIcons : function(event,element) {
        var activityType = event.activitytype;
        if(activityType == 'undefined') return;
        //imgContainer is event time div in week and day view and fc-event-inner in month view
        var imgContainer = element.find('.fc-event-head').length ? element.find('.fc-event-time') : element.find('div.fc-event-inner');
        if(activityType == 'Call')
            imgContainer.prepend('&nbsp<img width="13px" title="(call)" alt="(call)" src="layouts/vlayout/skins/images/small_Call.png">&nbsp');
        if(activityType == 'Meeting')
            imgContainer.prepend('&nbsp<img width="14px" title="(meeting)" alt="(meeting)" src="layouts/vlayout/skins/images/small_Meeting.png">&nbsp');
        if(activityType == 'Task')
            imgContainer.prepend('&nbsp<img width="14px" title="(task)" alt="(task)" src="layouts/vlayout/skins/images/small_Tasks.png">&nbsp');
    },
    getActivitIcon : function(event) {
        var activityType = event.activitytype;
        if(activityType == 'undefined') return;
		var img = '';
        if(activityType == 'Call')
            img = '&nbsp<img width="13px" title="(call)" alt="(call)" src="layouts/vlayout/skins/images/small_Call.png">&nbsp';
        if(activityType == 'Meeting')
            img = '&nbsp<img width="14px" title="(meeting)" alt="(meeting)" src="layouts/vlayout/skins/images/small_Meeting.png">&nbsp';
        if(activityType == 'Task')
            img = '&nbsp<img width="14px" title="(task)" alt="(task)" src="layouts/vlayout/skins/images/small_Tasks.png">&nbsp';
		return img;
    },

    /**
     *Function : strikes out events and tasks with status Held and Completed
     */
    strikeoutCompletedEventsTasks : function(event,element,view) {
        var activityType = event.activitytype;
        var title = '',titleStriked = '',target = '';
        var status = event.status;
        if(activityType === 'Task') {
            if(status === 'Completed') {
                title = event.title;
                titleStriked = title.strike();
                target = element.find('.fc-event-title');
                target.html(titleStriked);
            }
        }
        else {
            //Item redered is an event
            if(status === 'Held') {
                //Full calendar places title along with time for small duration events
                if(!element.find('.fc-event-title').length) {
                    target = element.find('.fc-event-time');
                    title = target.html();
                    titleStriked = title.strike();
                }
                else {
                    title = event.title;
                    titleStriked = title.strike();
                    target = element.find('.fc-event-title');
                }
                target.html(titleStriked);
            }
        }
    },

    registerEventDelete : function(targetElement,calEvent) {
        var thisInstance = this;
        var recordId = calEvent.id;
        targetElement.find('.delete').click(function(e){
            var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
            Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
			function(e) {
				//Confirmed to delete
                 var params = {
                        "module": "Calendar",
                        "action": "DeleteAjax",
                        "record": recordId
                    }
                AppConnector.request(params).then(function(data){
                   if(data.success) {
                        thisInstance.getCalendarView().fullCalendar('removeEvents', calEvent.id);
                        var param = {text:app.vtranslate('JS_RECORD_DELETED')};
                        Vtiger_Helper_Js.showMessage(param);
                    } else {
                        var  params = {
                            text : app.vtranslate('JS_NO_DELETE_PERMISSION')
                        }
                        Vtiger_Helper_Js.showPnotify(params);
                    }
                    
                });
			},
			function(error, err){
                e.preventDefault();
                return false;
			});  
        });
    },
    registerEventInfo : function(targetElement,calEvent) {
        var thisInstance = this;
        var recordId = calEvent.id;
		targetElement.find('.hip').hover(
			function () {
				$(this).popover('show');
			}, 
			function () {
				$(this).popover('hide');
			}
		);
    },
	registerCalendar : function(customConfig) {
		var thisInstance = this;
		var calendarview = this.getCalendarView();

		//User preferred default view
		var userDefaultActivityView = jQuery('#activity_view').val();
		if(userDefaultActivityView == 'Today'){
			userDefaultActivityView ='agendaDay';
		}else if(userDefaultActivityView == 'This Week'){
			userDefaultActivityView ='agendaWeek';
		}else{
			userDefaultActivityView ='month';
		}

		//Default time format
		var userDefaultTimeFormat = jQuery('#time_format').val();
		if(userDefaultTimeFormat == 24){
			userDefaultTimeFormat = 'H(:mm)';
		} else {
			userDefaultTimeFormat = 'h(:mm)tt';
		}

		//Default first day of the week
		var defaultFirstDay = jQuery('#start_day').val();
		var convertedFirstDay = thisInstance.weekDaysArray[defaultFirstDay];

		//Default first hour of the day
		var defaultFirstHour = jQuery('#start_hour').val();
		var explodedTime = defaultFirstHour.split(':');
		defaultFirstHour = explodedTime['0'];
        
        //Date format in agenda view must respect user preference
        var dateFormat = jQuery('#date_format').val();
        //Converting to fullcalendar accepting date format
        monthPos = dateFormat.search("mm");
        datePos = dateFormat.search("dd");
        if(monthPos < datePos)
            dateFormat = "M/d";
        else
            dateFormat = "d/M";
        
		var config = {
			header: {
				left: 'month,agendaWeek,agendaDay',
				center: 'title today',
				right: 'prev,next'
			},
            columnFormat: {
                month: 'ddd',
                week: 'ddd '+dateFormat,
                day: 'dddd '+dateFormat
            },
			height: 600,
            timeFormat:userDefaultTimeFormat+'{ - '+userDefaultTimeFormat+'}',
			axisFormat: userDefaultTimeFormat,
			firstHour : defaultFirstHour,
			firstDay : convertedFirstDay,
			defaultView: userDefaultActivityView,
            editable: true,
			slotMinutes : 15,
            defaultEventMinutes : 0,

			monthNames: [app.vtranslate('LBL_JANUARY'),app.vtranslate('LBL_FEBRUARY'),app.vtranslate('LBL_MARCH'),
				app.vtranslate('LBL_APRIL'),app.vtranslate('LBL_MAY'),app.vtranslate('LBL_JUNE'),app.vtranslate('LBL_JULY'),
				app.vtranslate('LBL_AUGUST'),app.vtranslate('LBL_SEPTEMBER'), app.vtranslate('LBL_OCTOBER'),
				app.vtranslate('LBL_NOVEMBER'), app.vtranslate('LBL_DECEMBER') ],

			monthNamesShort: [app.vtranslate('LBL_JAN'),app.vtranslate('LBL_FEB'),app.vtranslate('LBL_MAR'),
				app.vtranslate('LBL_APR'),app.vtranslate('LBL_MAY'),app.vtranslate('LBL_JUN'),app.vtranslate('LBL_JUL'),
				app.vtranslate('LBL_AUG'),app.vtranslate('LBL_SEP'),app.vtranslate('LBL_OCT'),app.vtranslate('LBL_NOV'),
				app.vtranslate('LBL_DEC')],

			dayNames: [ app.vtranslate('LBL_SUNDAY'), app.vtranslate('LBL_MONDAY'), app.vtranslate('LBL_TUESDAY'),
				app.vtranslate('LBL_WEDNESDAY'), app.vtranslate('LBL_THURSDAY'), app.vtranslate('LBL_FRIDAY'),
				app.vtranslate('LBL_SATURDAY')],

			dayNamesShort: [ app.vtranslate('LBL_SUN'), app.vtranslate('LBL_MON'), app.vtranslate('LBL_TUE'),
				app.vtranslate('LBL_WED'), app.vtranslate('LBL_THU'), app.vtranslate('LBL_FRI'),
				app.vtranslate('LBL_SAT')],

			buttonText: {
				today: app.vtranslate('LBL_TODAY'),
				month: app.vtranslate('LBL_MONTH'),
				week: app.vtranslate('LBL_WEEK'),
				day: app.vtranslate('LBL_DAY')
			},
			allDayText : app.vtranslate('LBL_ALL_DAY'),

			dayClick : function(date, allDay, jsEvent, view){thisInstance.dayClick(date, allDay, jsEvent, view);},

           eventAfterRender : function(event,element,view){
                                        thisInstance.addCallMeetingIcons(event,element);
                                        thisInstance.strikeoutCompletedEventsTasks(event,element,view);
                                        /*
                                         *Setting calendar view height to large value for week and day view to
                                         *avoid loss of display when more number of allday tasks are available
                                         **/
                                        if(view.name === 'agendaWeek' || view.name === 'agendaDay') {
                                            var allDayDiv = jQuery('.fc-view-'+view.name).find('.fc-agenda-divider').prev();
                                            if(allDayDiv.height() > 350) view.setHeight(600000);
                                        }
           },

           eventResize : function(event, dayDelta, minuteDelta, revertFunc, jsEvent, ui, view){
               if(event.module != 'Calendar' && event.module != 'Events'){
                   revertFunc();
                   return;
               }
                var params = {
                    module : 'Calendar',
                    action : 'DragDropAjax',
                    mode : 'updateDeltaOnResize',
                    id : event.id,
                    activitytype : event.activitytype,
                    dayDelta : dayDelta,
                    minuteDelta : minuteDelta,
                    view : view.name
                }
                AppConnector.request(params).then(function(data){
                    var response = JSON.parse(data);
                    if(!response['result'].ispermitted){
                        Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_EDIT_PERMISSION'));
                        revertFunc();
                    }
                    if(response['result'].error)
                        revertFunc();
                });
           },

           eventDrop : function( event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view ) {
                    if(event.module != 'Calendar' && event.module != 'Events'){
                        revertFunc();
                        return;
                    }
                    if((allDay && event.activitytype != 'Task') || (!allDay && event.activitytype === 'Task')){
                        revertFunc();
                        return;
                    }
                    var params = {
                        module : 'Calendar',
                        action : 'DragDropAjax',
                        mode : 'updateDeltaOnDrop',
                        id : event.id,
                        activitytype : event.activitytype,
                        dayDelta : dayDelta,
                        minuteDelta : minuteDelta,
                        view : view.name
                    }
                    AppConnector.request(params).then(function(data){
                        var response = JSON.parse(data);
                        if(!response['result'].ispermitted){
                            Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_EDIT_PERMISSION'));
                            revertFunc();
                        }
                    });
            },
			
			eventMouseover : function(calEvent, jsEvent, view) {
				jQuery(this).css('z-index', '10');
                var targetElement = jQuery(this).find('.fc-event-time');
                var trashElement = jQuery(this).find('a.delete');
                if(!trashElement.length) {
                    if(!targetElement.length) {
                        targetElement = jQuery(this).find('.fc-event-title');
                            targetElement.append('<a class="delete" style="position:absolute;right:1px;" href="javascript:void(0)"><i class="icon-trash"></i></a>'); 
                    }
                    else {
                        if(view.name == 'month') targetElement = jQuery(this).find('.fc-event-inner');
                        targetElement.append('<a class="delete" style="position:absolute;right:1px;" href="javascript:void(0)"><i class="icon-trash"></i></a>');                    
                    }
                    thisInstance.registerEventDelete(targetElement,calEvent);
                }
                else {
                    trashElement.removeClass('hide');
                }
				trashElement = jQuery(this).find('a.hip');
				var dateFormat = jQuery('#date_format').val();
				var userDefaultTimeFormat = jQuery('#time_format').val();
				if(userDefaultTimeFormat == 24){
					userDefaultTimeFormat = 'H:mm';
				} else {
					userDefaultTimeFormat = 'h:mm tt';
				}
				var content = '';
				if(calEvent.start != null){
					var DateString = app.getDateInVtigerFormat(dateFormat,calEvent.start);
					var TimeString = calEvent.start.toString(userDefaultTimeFormat);
					content = app.vtranslate('JS Start Date')+': '+DateString+' '+TimeString;
				}
				if(calEvent.end != null){
					var DateString = app.getDateInVtigerFormat(dateFormat,calEvent.end);
					var TimeString = calEvent.end.toString(userDefaultTimeFormat);
					content += '<br />'+app.vtranslate('JS End Date')+': '+DateString+' '+TimeString;
				}
				if(calEvent.activitytype != null){
					content += '<br />'+app.vtranslate('JS Activity Type')+': '+app.vtranslate('JS '+calEvent.activitytype);
				}
				if(calEvent.status != null){
					content += '<br />'+app.vtranslate('JS Satatus')+': '+app.vtranslate('JS '+calEvent.status);
				}				
                if(!trashElement.length) {
                    if(!targetElement.length) {
                        targetElement = jQuery(this).find('.fc-event-title');
                            targetElement.append('<a style="position:absolute;right:14px;" class="hip" data-placement="top" data-content="'+content+'" data-original-title="'+calEvent.title+'"><i class="icon-info-sign"></i></a>'); 
                    }
                    else {
                        if(view.name == 'month') targetElement = jQuery(this).find('.fc-event-inner');
                        targetElement.append('<a style="position:absolute;right:14px;" class="hip" data-placement="top" data-content="'+content+'" data-original-title="'+calEvent.title+'"><i class="icon-info-sign"></i></a>');                    
                    }
					thisInstance.registerEventInfo(targetElement,calEvent);
                }
                else {
                    trashElement.removeClass('hide');
                }
			},

			eventMouseout : function(calEvent, jsEvent, view) {
				jQuery(this).css('z-index', '8');
                jQuery(this).find('.delete').addClass('hide');
				jQuery(this).find('.hip').addClass('hide');
			}
		}
		if(typeof customConfig != 'undefined'){
			config = jQuery.extend(config,customConfig);
		}
		calendarview.fullCalendar(config);

		//To create custom button to create event or task
		jQuery('<span class="pull-left"><button class="btn addButton">'+ app.vtranslate('LBL_ADD_EVENT_TASK') +'</button></span>')
			.prependTo(calendarview.find('.fc-header .fc-header-right')).on('click', 'button', function(e){
				thisInstance.getCalendarCreateView().then(function(data){
					var headerInstance = new Vtiger_Header_Js();
					headerInstance.handleQuickCreateData(data,{callbackFunction:function(data){
							thisInstance.addCalendarEvent(data.result);
					}});
				});

			})
	},

	changeCalendarSharingType : function(data) {
        var selectedUsersContainer = app.getSelect2ElementFromSelect(jQuery('#selectedUsers',data));
        if(jQuery('#selectedUsers').is(':checked')){
            selectedUsersContainer.attr('style','display:block;width:90%;');
        }
		jQuery('[name="sharedtype"]').on('change',function(e) {
			var sharingType = jQuery(e.currentTarget).data('sharingtype');

			if(sharingType == 'selectedusers') {
				selectedUsersContainer.show();
                selectedUsersContainer.attr('style','display:block;width:90%;');
			} else {
				selectedUsersContainer.hide();
			}
		});
	},

	isAllowedToAddCalendarEvent : function (calendarDetails) {
		var activityType = calendarDetails.activitytype.value;
		if(activityType == 'Calendar'  && jQuery('[data-calendar-feed="Calendar"]').is(':checked')) {
			return true;
		} else if(jQuery('[data-calendar-feed="Events"]').is(':checked')){
			return true;
		} else {
			return false;
		}
	},
	addCalendarEvent : function(calendarDetails) {
		//If type is not shown then dont render the created event
		var isAllowed = this.isAllowedToAddCalendarEvent(calendarDetails);
		if(isAllowed == false) return;

		var eventObject = {};
		eventObject.id = calendarDetails._recordId;
		eventObject.title = calendarDetails.subject.display_value;
		var startDate = Date.parse(calendarDetails.date_start.calendar_display_value);
		eventObject.start = startDate.toString();
		var endDate = Date.parse(calendarDetails.due_date.calendar_display_value);
		var assignedUserId = calendarDetails.assigned_user_id.value;
		eventObject.end = endDate.toString();
		eventObject.url = 'index.php?module=Calendar&view=Detail&record='+calendarDetails._recordId;
		if(calendarDetails.activitytype.value == 'Task'){
			var color = jQuery('[data-calendar-feed="Calendar"]').data('calendar-feed-color');
			var textColor = jQuery('[data-calendar-feed="Calendar"]').data('calendar-feed-textcolor');
			eventObject.allDay = true;
            eventObject.activitytype = calendarDetails.activitytype.value;
            eventObject.status = calendarDetails.taskstatus.value;
		}else{
			var userElement = jQuery('[data-calendar-userid='+assignedUserId+']');
			if(userElement.length > 0) {
				var color = jQuery('[data-calendar-userid='+assignedUserId+']').data('calendar-feed-color');
				var textColor = jQuery('[data-calendar-userid='+assignedUserId+']').data('calendar-feed-textcolor');
			} else {
				var color = jQuery('[data-calendar-feed="Events"]').data('calendar-feed-color');
				var textColor = jQuery('[data-calendar-feed="Events"]').data('calendar-feed-textcolor');
			}
			
            eventObject.activitytype = calendarDetails.activitytype.value;
            eventObject.status = calendarDetails.eventstatus.value;
			eventObject.allDay = false;
		}
		eventObject.color = color;
		eventObject.textColor = textColor;
		this.getCalendarView().fullCalendar('renderEvent',eventObject);
	},

	restoreActivityTypesWidgetState : function() {
		var key = 'Calendar_sideBar_LBL_ACTIVITY_TYPES';
		var value = app.cacheGet(key);
		var widgetContainer = jQuery("#Calendar_sideBar_LBL_ACTIVITY_TYPES");
		if(value == 0){
			Vtiger_Index_Js.loadWidgets(widgetContainer,false);
		}
		else{
			Vtiger_Index_Js.loadWidgets(widgetContainer);
		}
	},
	
	/**
	 * Function to register event for add calendar view
	 */
	registerEventForAddCalendarView : function() {
		var thisInstance = this;
		jQuery('[data-label="LBL_ADDED_CALENDARS"],[data-label="LBL_ACTIVITY_TYPES"]').find('.addCalendarView').click(function(e) {
			//To stop the accordion default behaviour when click on add icon
			e.stopPropagation();
			var currentTarget = jQuery(e.currentTarget);
			if(jQuery('#calendarview-feeds').find('.invisibleCalendarViews').val() == 'true') {
				thisInstance.showAddUserCalendarModal(currentTarget);
			} else {
				Vtiger_Helper_Js.showPnotify({text: app.vtranslate('JS_NO_CALENDAR_VIEWS_TO_ADD')});
			}
		});
	},
        
        	/**
	 * Function to register event for Check calendar view
	 */
	registerEventForCheckCalendarView : function() {
		var thisInstance = this;
		var pre_stan = false;
		jQuery('[data-label="LBL_ADDED_CALENDARS"],[data-label="LBL_ACTIVITY_TYPES"]').find('.checkCalendarView').click(function(e) {
			e.stopPropagation();
			
			jQuery('#calendarview-feeds .checkbox.addedCalendars input[type="checkbox"]').each(function(){
				if( $(this).attr( "data-calendar-userid" ) != undefined || $(this).attr( "data-calendar-feed" ) != undefined){
					$(this).attr('checked', pre_stan).change();
				}
			})
			if(pre_stan){
				pre_stan = false;
			}else{
				pre_stan = true;
			}
		});
	},
	
	/**
	 * Function to register event for delete user calendar
	 */
	registerEventForDeleteUserCalendar : function() {
		var thisInstance = this;
		var calendarView = jQuery('#calendarview-feeds');
		calendarView.find('.deleteCalendarView').on('click', function(e) {
			e.preventDefault();
			var currentTarget = jQuery(e.currentTarget);
			var feedcheckbox = currentTarget.closest('.addedCalendars').find('[data-calendar-feed]');
			var message = app.vtranslate('JS_CALENDAR_VIEW_DELETE_CONFIRMATION');
			Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(function(data) {
				thisInstance.deleteCalendarView(feedcheckbox).then(function() {
						var params = {
							text: app.vtranslate('JS_CALENDAR_VIEW_DELETED_SUCCESSFULLY'),
							type: 'info'
						};
						Vtiger_Helper_Js.showPnotify(params);
					});
				},
				function(error, err){
				}
			);
		})
	},
	
	/**
	 * Function used to delete calendar view
	 */
	deleteCalendarView : function(feedcheckbox) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var params = {
			module: 'Calendar',
			action: 'CalendarUserActions',
			mode : 'deleteCalendarView',
			viewmodule : feedcheckbox.data('calendar-feed'),
			viewfieldname : feedcheckbox.data('calendar-fieldname'),
			viewfieldlabel : feedcheckbox.data('calendar-fieldlabel')
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
			userSelectElement.append('<option value="'+result['viewfieldname']+'" data-viewmodule="'+result['viewmodule']+'">'+result['viewfieldlabel']+'</option>');
			var editUserSelectElement = jQuery('#calendarview-feeds').find('[name="editingUsersList"]');
			editUserSelectElement.find('option[value="'+result['viewfieldname']+'"]').remove();
			jQuery('#calendarview-feeds').find('.invisibleCalendarViews').val('true');
			
			aDeferred.resolve();
		},
		function(error){
			aDeferred.reject();
		});
		
		return aDeferred.promise();
	},
	
	resetAccordionHeight : function() {
		var accordionContainer = jQuery('[name="calendarViewTypes"]').parent();
		if(accordionContainer.hasClass('in')) {
			accordionContainer.css('height', 'auto');
		}
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
			selectElement.find('option[value="'+feedUserEle.data('calendar-fieldname')+'"]').attr('selected', true);
			thisInstance.showAddUserCalendarModal(currentTarget);
		})
	},
	
	/**
	 * Function to show add calendar modal
	 */
	showAddUserCalendarModal : function(currentEle) {
		var thisInstance = this;
		var addCalendarModal = jQuery('#calendarview-feeds').find('.addViewsToCalendar');
		var clonedContainer = addCalendarModal.clone(true, true);

		var callBackFunction = function(data) {
			data.find('.addViewsToCalendar').removeClass('hide');
			var selectedUserColor = data.find('.selectedUserColor');
			var selectedUser = data.find('.selectedUser');
			var selectedViewModule = data.find('.selectedViewModule');
			var addCalendarViewsList = data.find('.addCalendarViewsList');
			var editCalendarViewsList = data.find('.editCalendarViewsList');
			
			//check its edit mode or add mode, show modal respective to that mode
			if(currentEle.hasClass('editCalendarColor')) {
				addCalendarViewsList.addClass('hide');
				editCalendarViewsList.removeClass('hide');
				data.find('.modal-header h3').text(app.vtranslate('JS_EDIT_CALENDAR'));
				data.find('.userCalendarMode').val('edit');
				//on change of calendar view, color picker should update with that calendar view color
				thisInstance.registerViewsListChangeEvent(data);
				
				var addedCalendarEle = currentEle.closest('.addedCalendars');
				var feedUserEle = addedCalendarEle.find('[data-calendar-feed]');
				selectedUserColor.val(feedUserEle.data('calendar-feed-color'));
				//color picker params for edit calendar view color
				var customParams = {
					color : feedUserEle.data('calendar-feed-color')
				};
			} else {
				addCalendarViewsList.removeClass('hide');
				editCalendarViewsList.addClass('hide');
				data.find('.userCalendarMode').val('add');
				//while adding new calendar view set the random color to the color picker
				var randomColor = '#'+(0x1000000+(Math.random())*0xffffff).toString(16).substr(1,6);
				selectedUserColor.val(randomColor);
				//color picker params for add calendar view
				var customParams = {
					color : randomColor
				};
			}
			
			//register color picker
			var params = {
				flat : true,
				onChange : function(hsb, hex, rgb) {
					var selectedColor = '#'+hex;
					selectedUserColor.val(selectedColor);
				}
			};
			if(typeof customParams != 'undefined'){
				params = jQuery.extend(params,customParams);
			}
			data.find('.calendarColorPicker').ColorPicker(params);
			
			//save the user calendar with color
			data.find('[name="saveButton"]').click(function(e) {
				if(currentEle.hasClass('addCalendarView')) {
					var selectElement = data.find('select[name="usersCalendarList"]');
				} else {
					var selectElement = data.find('select[name="editingUsersList"]');
				}
				selectedUser.val(selectElement.val()).attr('data-username', selectElement.find('option:selected').text());
				selectedViewModule.val(selectElement.find('option:selected').data('viewmodule'));
				thisInstance.saveUserCalendar(data, currentEle);
				
			});
		}
		
		app.showModalWindow(clonedContainer,function(data) {
			if(typeof callBackFunction == 'function') {
				callBackFunction(data);
			}
		}, {'width':'1000px'});
	},
	
	/**
	 * Function to register change event for users list select element in edit user calendar modal
	 */
	registerViewsListChangeEvent : function(data) {
		var parentElement = jQuery('#calendarview-feeds');
		var selectElement = data.find('[name="editingUsersList"]');
		var selectedUserColor = data.find('.selectedUserColor');
		var selectedViewModule = data.find('.selectedViewModule');
		
		//on change of edit user, update color picker with the selected user color
		selectElement.on('change', function() {
			var selectedOption = selectElement.find('option:selected');
			var fieldName = selectedOption.val();
			var userColor = jQuery('[data-calendar-fieldname="'+fieldName+'"]', parentElement).data('calendar-feed-color');
			selectedUserColor.val(userColor);
			selectedViewModule.val(selectedOption.data('viewmodule'));
			data.find('.calendarColorPicker').ColorPickerSetColor(userColor);
		});
	},
	
	/**
	 * Function to save added user calendar
	 */
	saveUserCalendar : function(data, currentEle) {
		var thisInstance = this;
		var userColor = data.find('.selectedUserColor').val();
		var fieldName = data.find('.selectedUser').val();
		var moduleName = data.find('.selectedViewModule').val();
		var userName = data.find('.selectedUser').data('username');
		var params = {
			module: 'Calendar',
			action: 'CalendarUserActions',
			mode : 'addCalendarView',
			viewmodule : moduleName,
			viewfieldname : fieldName,
			viewColor : userColor
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
				var feedUserEle = jQuery('[data-calendar-fieldname="'+fieldName+'"]', parentElement);
				
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
				feedUserEle.attr('data-calendar-sourcekey', fieldName).attr('data-calendar-feed', moduleName).attr('data-calendar-feed-color',userColor)
						.attr('data-calendar-feed-textcolor',textColor).attr('data-calendar-fieldname',fieldName).attr('data-calendar-fieldlabel', userName);
				feedUserEle.closest('.addedCalendars').find('.label').css({'background-color':userColor,'color':textColor}).text(userName);
				parentElement.append(labelView);
				
				//After add activityType reset accodion height to auto
				thisInstance.resetAccordionHeight();
				
				thisInstance.fetchCalendarFeed(feedUserEle);
				
				//Update the adding and editing users list in hidden modal
				var userSelectElement = jQuery('#calendarview-feeds').find('[name="usersCalendarList"]');
				userSelectElement.find('option[value="'+fieldName+'"]').remove();
				
				if(userSelectElement.find('option').length <= 0) {
					jQuery('#calendarview-feeds').find('.invisibleCalendarViews').val('false');
				}
				
				var editUserSelectElement = jQuery('#calendarview-feeds').find('[name="editingUsersList"]');
				editUserSelectElement.append('<option value="'+fieldName+'" data-viewmodule="'+moduleName+'">'+userName+'</option>');
				
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
	
	registerEvents : function() {
		this.registerCalendar();
        this.restoreActivityTypesWidgetState();
		//register event for add calendar view
		this.registerEventForAddCalendarView();
                this.registerEventForCheckCalendarView();
		return this;
	}
});

jQuery(document).ready(function() {
	var instance = Calendar_CalendarView_Js.getInstanceByView();
	instance.registerEvents()
	Calendar_CalendarView_Js.currentInstance = instance;
})