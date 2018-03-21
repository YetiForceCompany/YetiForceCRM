/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Reports_Edit_Js("Reports_Edit1_Js", {}, {

	relatedModulesMapping: false,
	step1Container: false,
	secondaryModulesContainer: false,

	init: function () {
		this.initialize();
	},
	/**
	 * Function to get the container which holds all the reports step1 elements
	 * @return jQuery object
	 */
	getContainer: function () {
		return this.step1Container;
	},

	/**
	 * Function to set the reports step1 container
	 * @params : element - which represents the reports step1 container
	 * @return : current instance
	 */
	setContainer: function (element) {
		this.step1Container = element;
		return this;
	},
	/*
	 * Function to get the secondary module container 
	 */
	getSecondaryModuleContainer: function () {
		if (this.secondaryModulesContainer == false) {
			this.secondaryModulesContainer = jQuery('#secondary_module');
		}
		return this.secondaryModulesContainer;
	},

	/**
	 * Function  to intialize the reports step1
	 */
	initialize: function (container) {
		if (typeof container == 'undefined') {
			container = jQuery('#report_step1');
		}
		if (container.is('#report_step1')) {
			this.setContainer(container);
		} else {
			this.setContainer(jQuery('#report_step1'));
		}
		this.intializeOperationMappingDetails();
	},
	/**
	 * Function which will save the related modules mapping
	 */
	intializeOperationMappingDetails: function () {
		this.relatedModulesMapping = jQuery('#relatedModules').data('value');
	},

	/**
	 * Function which will return set of condition for the given field type
	 * @return array of conditions
	 */
	getRelatedModulesFromPrimaryModule: function (primaryModule) {
		return this.relatedModulesMapping[primaryModule];
	},

	loadRelatedModules: function (primaryModule) {
		var relatedModulesMapping = this.getRelatedModulesFromPrimaryModule(primaryModule);
		var options = '';
		for (var key in relatedModulesMapping) {
			//IE Browser consider the prototype properties also, it should consider has own properties only.
			if (relatedModulesMapping.hasOwnProperty(key)) {
				options += '<option value="' + key + '">' + relatedModulesMapping[key] + '</option>';
			}
		}
		var secondaryModulesContainer = this.getSecondaryModuleContainer();
		secondaryModulesContainer.html(options).trigger("change");

	},
	registerPrimaryModuleChangeEvent: function () {
		var thisInstance = this;
		jQuery('#primary_module').on('change', function (e) {
			var primaryModule = jQuery(e.currentTarget).val();
			thisInstance.loadRelatedModules(primaryModule);
		});
	},

	/*
	 * Function to check Duplication of report Name
	 * returns boolean true or false
	 */
	checkDuplicateName: function (details) {
		var aDeferred = jQuery.Deferred();
		var moduleName = app.getModuleName();
		var params = {
			'module': moduleName,
			'action': "CheckDuplicate",
			'reportname': details.reportName,
			'record': details.reportId,
			'isDuplicate': details.isDuplicate
		};

		AppConnector.request(params).then(
			function (data) {
				var response = data['result'];
				var result = response['success'];
				if (result == true) {
					aDeferred.reject(response);
				} else {
					aDeferred.resolve(response);
				}
			},
			function (error, err) {
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},

	submit: function () {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var form = this.getContainer();
		var formData = form.serializeFormData();

		var params = {};
		var reportName = jQuery.trim(formData.reportname);
		var reportId = formData.record;

		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});

		thisInstance.checkDuplicateName({
			'reportName': reportName,
			'reportId': reportId,
			'isDuplicate': formData.isDuplicate
		}).then(
			function (data) {
				AppConnector.request(formData).then(
					function (data) {
						form.hide();
						progressIndicatorElement.progressIndicator({
							'mode': 'hide'
						});
						aDeferred.resolve(data);
					},
					function (error, err) {

					}
				);
			},
			function (data, err) {
				progressIndicatorElement.progressIndicator({
					'mode': 'hide'
				});
				params = {
					title: app.vtranslate('JS_DUPLICATE_RECORD'),
					text: data['message']
				};
				Vtiger_Helper_Js.showPnotify(params);
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},

	/**
	 * Function which will register the select2 elements for secondary modules selection
	 */
	registerSelect2ElementForSecondaryModulesSelection: function () {
		var secondaryModulesContainer = this.getSecondaryModuleContainer();
		app.changeSelectElementView(secondaryModulesContainer, 'select2', {maximumSelectionLength: 2});
	},

	/**
	 * Function to register event for scheduled reports UI
	 */
	registerEventForScheduledReprots: function () {
		var thisInstance = this;
		jQuery('input[name="enable_schedule"]').on('click', function (e) {
			var element = jQuery(e.currentTarget);
			var scheduleBoxContainer = jQuery('#scheduleBox');
			if (element.is(':checked')) {
				element.val(element.is(':checked'));
				scheduleBoxContainer.removeClass('d-none');
			} else {
				element.val(element.is(':checked'));
				scheduleBoxContainer.addClass('d-none');
			}
		});
		app.registerEventForClockPicker();
		App.Fields.Date.registerDatePickerFields('#scheduleByDate', true);

		jQuery('#annualDates').chosen();
		jQuery('#schdayoftheweek').chosen();
		jQuery('#schdayofthemonth').chosen();
		jQuery('#recipients').chosen();

		var currentYear = new Date().getFullYear();
		var weekStartId = jQuery('#weekStartDay').data('value');

		$('#annualDatePicker').datepicker({
			weekStart: weekStartId,
			startDate: '01/01/' + currentYear,
			endDate: '12/31/' + currentYear,
			format: "mm/dd/yyyy",
			maxViewMode: 1,
			todayBtn: "linked",
			language: jQuery('body').data('language'),
			multidate: true,
			todayHighlight: true
		}).on('changeDate', function (e) {
			var datesInfo = [];
			var values = [];
			var html = '';
			// reset the annual dates
			var annualDatesEle = jQuery('#annualDates');
			thisInstance.updateAnnualDates(annualDatesEle);
			for (var index in e.dates) {
				var date = e.dates[index];
				var formated = moment(date).format('YYYY-MM-DD')
				datesInfo.push({
					id: formated,
					text: formated
				});
				values.push(formated);
				html += '<option selected value=' + formated + '>' + formated + '</option>';
			}
			annualDatesEle.append(html);
			annualDatesEle.trigger("chosen:updated");
		});
		var annualDatesEle = jQuery('#annualDates');
		thisInstance.updateAnnualDates(annualDatesEle);
		annualDatesEle.trigger("chosen:updated");
	},
	updateAnnualDates: function (annualDatesEle) {
		annualDatesEle.html('');
		var annualDatesJSON = jQuery('#hiddenAnnualDates').val();
		if (annualDatesJSON) {
			var hiddenDates = '';
			var annualDates = JSON.parse(annualDatesJSON);
			for (var i in annualDates) {
				hiddenDates += '<option selected value=' + annualDates[i] + '>' + annualDates[i] + '</option>';
			}
			annualDatesEle.html(hiddenDates);
		}
	},
	registerEventForChangeInScheduledType: function () {
		var thisInstance = this;
		jQuery('#schtypeid').on('change', function (e) {
			var element = jQuery(e.currentTarget);
			var value = element.val();

			thisInstance.showScheduledTime();
			thisInstance.hideScheduledWeekList();
			thisInstance.hideScheduledMonthByDateList();
			thisInstance.hideScheduledAnually();
			thisInstance.hideScheduledSpecificDate();

			if (value == '2') { //weekly
				thisInstance.showScheduledWeekList();
			} else if (value == '3') { //monthly by day
				thisInstance.showScheduledMonthByDateList();
			} else if (value == '4') { //Anually
				thisInstance.showScheduledAnually();
			} else if (value == '5') { //specific date
				thisInstance.showScheduledSpecificDate();
			}
		});
	},

	//Remove annual dates element 
	registerEventForRemoveAnnualDates: function () {
		var thisInstance = this;
		jQuery("#annualDates").chosen().change(function (e) {
			jQuery('#hiddenAnnualDates').val(JSON.stringify(jQuery(e.target).val()));
			var element = jQuery(e.currentTarget);
			thisInstance.updateAnnualDates(element);
			element.trigger("chosen:updated");
		});
	},

	hideScheduledTime: function () {
		jQuery('#scheduledTime').addClass('d-none');
	},

	showScheduledTime: function () {
		jQuery('#scheduledTime').removeClass('d-none');
	},

	hideScheduledWeekList: function () {
		jQuery('#scheduledWeekDay').addClass('d-none');
	},

	showScheduledWeekList: function () {
		jQuery('#scheduledWeekDay').removeClass('d-none');
	},

	hideScheduledMonthByDateList: function () {
		jQuery('#scheduleMonthByDates').addClass('d-none');
	},

	showScheduledMonthByDateList: function () {
		jQuery('#scheduleMonthByDates').removeClass('d-none');
	},

	hideScheduledSpecificDate: function () {
		jQuery('#scheduleByDate').addClass('d-none');
	},

	showScheduledSpecificDate: function () {
		jQuery('#scheduleByDate').removeClass('d-none');
	},

	hideScheduledAnually: function () {
		jQuery('#scheduleAnually').addClass('d-none');
	},

	showScheduledAnually: function () {
		jQuery('#scheduleAnually').removeClass('d-none');
	},

	registerEvents: function () {
		this.registerPrimaryModuleChangeEvent();
		this.registerSelect2ElementForSecondaryModulesSelection();
		var container = this.getContainer();

		var opts = app.validationEngineOptions;
		// to prevent the page reload after the validation has completed 
		opts['onValidationComplete'] = function (form, valid) {
			//returns the valid status
			return valid;
		};
		opts['promptPosition'] = "bottomRight";
		container.validationEngine(opts);
		//schedule reports
		this.registerEventForScheduledReprots();
		this.registerEventForChangeInScheduledType();
		this.registerEventForRemoveAnnualDates();
	}
});
