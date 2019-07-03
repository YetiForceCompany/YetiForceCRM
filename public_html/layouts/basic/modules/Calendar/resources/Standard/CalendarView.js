/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
"use strict";

/**
 *  Class representing a standard calendar.
 * @extends Calendar_Js
 */
window.Calendar_Calendar_Js = class extends Calendar_Js {
  constructor(container, readonly) {
    super(container, readonly);
    this.eventCreate = app.getMainParams("eventCreate");
  }
  setCalendarModuleOptions() {
    let self = this,
      options = {
        selectable: self.eventCreate,
        select: function(start, end) {
          self.selectDays(start, end);
          self.getCalendarView().fullCalendar("unselect");
        },
        eventClick: function(calEvent, jsEvent, view) {
          jsEvent.preventDefault();
          var link = new URL($(this)[0].href);
          var progressInstance = jQuery.progressIndicator({
            blockInfo: { enabled: true }
          });
          var url =
            "index.php?module=Calendar&view=ActivityStateModal&record=" +
            link.searchParams.get("record");
          var callbackFunction = function(data) {
            progressInstance.progressIndicator({ mode: "hide" });
          };
          var modalWindowParams = {
            url: url,
            cb: callbackFunction
          };
          app.showModalWindow(modalWindowParams);
        }
      };
    return options;
  }

  getValuesFromSelect2(element, data, text) {
    if (element.hasClass("select2-hidden-accessible")) {
      var types = element.select2("data");
      for (var i = 0; i < types.length; i++) {
        if (text) {
          data.push(types[i].text);
        } else {
          data.push(types[i].id);
        }
      }
    }
    return data;
  }

  getDefaultParams() {
    let parentParams = super.getDefaultParams(),
      users = app.moduleCacheGet("calendar-groups") || [],
      filters = [];
    $(".calendarFilters .filterField").each(function() {
      let element = $(this),
        name,
        value;
      if (element.attr("type") == "checkbox") {
        name = element.val();
        value = element.prop("checked") ? 1 : 0;
      } else {
        name = element.attr("name");
        value = element.val();
      }
      filters.push({ name: name, value: value });
    });
    let params = {
      time: app.getMainParams("showType"),
      filters: filters
    };
    if (users.length) {
      params.user = parentParams.user.concat(users);
      params.emptyFilters = false;
    }
    params = Object.assign(parentParams, params);
    return params;
  }

  selectDays(startDate, endDate) {
    var thisInstance = this;
    var start_hour = app.getMainParams("startHour");
    var end_hour = app.getMainParams("endHour");
    if (endDate.hasTime() == false) {
      endDate.add(-1, "days");
    }
    startDate = startDate.format();
    endDate = endDate.format();
    var view = thisInstance.getCalendarView().fullCalendar("getView");
    if (start_hour == "") {
      start_hour = "00";
    }
    if (end_hour == "") {
      end_hour = "00";
    }
    this.getCalendarCreateView().done(function(data) {
      if (data.length <= 0) {
        return;
      }
      if (view.name != "agendaDay" && view.name != "agendaWeek") {
        startDate = startDate + "T" + start_hour + ":00";
        endDate = endDate + "T" + start_hour + ":00";
        if (startDate == endDate) {
          let activityType = data.find('[name="activitytype"]').val();
          let activityDurations = JSON.parse(
            data.find('[name="defaultOtherEventDuration"]').val()
          );
          let minutes = 0;
          for (let i in activityDurations) {
            if (activityDurations[i].activitytype === activityType) {
              minutes = parseInt(activityDurations[i].duration);
              break;
            }
          }
          endDate = moment(endDate)
            .add(minutes, "minutes")
            .toISOString();
        }
      }
      var dateFormat = data
        .find('[name="date_start"]')
        .data("dateFormat")
        .toUpperCase();
      var timeFormat = data.find('[name="time_start"]').data("format");
      if (timeFormat == 24) {
        var defaultTimeFormat = "HH:mm";
      } else {
        defaultTimeFormat = "hh:mm A";
      }
      var startDateString = moment(startDate).format(dateFormat);
      var startTimeString = moment(startDate).format(defaultTimeFormat);
      var endDateString = moment(endDate).format(dateFormat);
      var endTimeString = moment(endDate).format(defaultTimeFormat);

      data.find('[name="date_start"]').val(startDateString);
      data.find('[name="due_date"]').val(endDateString);
      data.find('[name="time_start"]').val(startTimeString);
      data.find('[name="time_end"]').val(endTimeString);

      var headerInstance = new Vtiger_Header_Js();
      headerInstance.handleQuickCreateData(data, {
        callbackFunction: function(data) {
          thisInstance.addCalendarEvent(data.result);
        }
      });
    });
  }

  isNewEventToDisplay(eventObject) {
    if (super.isNewEventToDisplay(eventObject)) {
      let taskstatus = $.inArray(eventObject.activitystatus.value, [
        "PLL_POSTPONED",
        "PLL_CANCELLED",
        "PLL_COMPLETED"
      ]);
      var state = $(".fc-toolbar .js-switch--label-on")
        .last()
        .hasClass("active");
      if (
        (state === true && taskstatus >= 0) ||
        (state != true && taskstatus == -1)
      ) {
        return false;
      } else {
        return true;
      }
    } else {
      return false;
    }
  }

  getCalendarCreateView() {
    var thisInstance = this;
    var aDeferred = jQuery.Deferred();

    if (this.calendarCreateView !== false) {
      aDeferred.resolve(this.calendarCreateView.clone(true, true));
      return aDeferred.promise();
    }
    var progressInstance = jQuery.progressIndicator({
      blockInfo: { enabled: true }
    });
    this.loadCalendarCreateView()
      .done(function(data) {
        progressInstance.progressIndicator({ mode: "hide" });
        thisInstance.calendarCreateView = data;
        aDeferred.resolve(data.clone(true, true));
      })
      .fail(function(error) {
        progressInstance.progressIndicator({ mode: "hide" });
        console.error(error);
      });
    return aDeferred.promise();
  }

  loadCalendarCreateView() {
    var aDeferred = jQuery.Deferred();
    var moduleName = app.getModuleName();
    var url = "index.php?module=" + moduleName + "&view=QuickCreateAjax";
    var headerInstance = Vtiger_Header_Js.getInstance();
    headerInstance
      .getQuickCreateForm(url, moduleName)
      .done(function(data) {
        aDeferred.resolve(jQuery(data));
      })
      .fail(function() {
        aDeferred.reject();
      });
    return aDeferred.promise();
  }

  switchTpl(on, off, state) {
    return `<div class="btn-group btn-group-toggle js-switch c-calendar-switch" data-toggle="buttons">
					<label class="btn btn-outline-primary c-calendar-switch__button js-switch--label-on ${
            state ? "" : "active"
          }">
						<input type="radio" name="options" data-on-text="${on}" autocomplete="off" ${
      state ? "" : "checked"
    }>
						${on}
					</label>
					<label class="btn btn-outline-primary c-calendar-switch__button ${
            state ? "active" : ""
          }">
						<input type="radio" name="options" data-off-text="${off}" autocomplete="off" ${
      state ? "checked" : ""
    }>
						${off}
					</label>
				</div>`;
  }

  registerSwitchEvents() {
    const calendarview = this.getCalendarView();
    let switchHistory,
      switchAllDays,
      switchContainer = $(
        `<div class="js-calendar-switch-container"></div>`
      ).insertAfter(calendarview.find(".fc-center"));
    if (
      app.getMainParams("showType") == "current" &&
      app.moduleCacheGet("defaultShowType") != "history"
    ) {
      switchHistory = false;
    } else {
      switchHistory = true;
    }
    $(
      this.switchTpl(
        app.vtranslate("JS_TO_REALIZE"),
        app.vtranslate("JS_HISTORY"),
        switchHistory
      )
    )
      .prependTo(switchContainer)
      .on("change", "input", e => {
        const currentTarget = $(e.currentTarget);
        if (typeof currentTarget.data("on-text") !== "undefined") {
          app.setMainParams("showType", "current");
          app.moduleCacheSet("defaultShowType", "current");
        } else if (typeof currentTarget.data("off-text") !== "undefined") {
          app.setMainParams("showType", "history");
          app.moduleCacheSet("defaultShowType", "history");
        }
        this.loadCalendarData();
      });
    if (
      app.getMainParams("switchingDays") === "workDays" &&
      app.moduleCacheGet("defaultSwitchingDays") !== "all"
    ) {
      switchAllDays = false;
    } else {
      switchAllDays = true;
    }
    if (app.getMainParams("hiddenDays", true) !== false) {
      $(
        this.switchTpl(
          app.vtranslate("JS_WORK_DAYS"),
          app.vtranslate("JS_ALL"),
          switchAllDays
        )
      )
        .prependTo(switchContainer)
        .on("change", "input", e => {
          const currentTarget = $(e.currentTarget);
          let hiddenDays = [];
          if (typeof currentTarget.data("on-text") !== "undefined") {
            app.setMainParams("switchingDays", "workDays");
            app.moduleCacheSet("defaultSwitchingDays", "workDays");
            hiddenDays = app.getMainParams("hiddenDays", true);
          } else if (typeof currentTarget.data("off-text") !== "undefined") {
            app.setMainParams("switchingDays", "all");
            app.moduleCacheSet("defaultSwitchingDays", "all");
          }
          calendarview.fullCalendar("option", "hiddenDays", hiddenDays);
          this.registerSwitchEvents();
          this.loadCalendarData();
        });
    }
  }

  registerCacheSettings() {
    var thisInstance = this;
    var calendar = thisInstance.getCalendarView();
    if (app.moduleCacheGet("defaultSwitchingDays") == "all") {
      app.setMainParams("switchingDays", "all");
    } else {
      app.setMainParams("switchingDays", "workDays");
    }
    if (app.moduleCacheGet("defaultShowType") == "history") {
      app.setMainParams("showType", "history");
    } else {
      app.setMainParams("showType", "current");
    }
    $(".siteBarRight .filterField").each(function(index) {
      var name = $(this).attr("id");
      var value = app.moduleCacheGet(name);
      var element = $("#" + name);
      if (element.length > 0 && value != null) {
        if (element.attr("type") == "checkbox") {
          element.prop("checked", value);
        }
      }
    });
    calendar.find(".fc-toolbar .fc-button").on("click", function(e) {
      let view;
      let element = $(e.currentTarget);
      view = calendar.fullCalendar("getView");
      if (element.hasClass("fc-" + view.name + "-button")) {
        app.moduleCacheSet("defaultView", view.name);
      } else if (
        element.hasClass("fc-prev-button") ||
        element.hasClass("fc-next-button") ||
        element.hasClass("fc-today-button")
      ) {
        app.moduleCacheSet("start", view.start.format());
        app.moduleCacheSet("end", view.end.format());
      }
    });
    var keys = app.moduleCacheKeys();
    if (keys.length > 0) {
      var alert = $("#moduleCacheAlert");
      $(".bodyContents").on("Vtiger.Widget.Load.undefined", function(e, data) {
        alert.removeClass("d-none");
      });
      alert.find(".cacheClear").on("click", function(e) {
        app.moduleCacheClear();
        alert.addClass("d-none");
        location.reload();
      });
    }
  }

  registerEvents() {
    super.registerEvents();
    this.registerCacheSettings();
    this.registerSwitchEvents();
  }
};
