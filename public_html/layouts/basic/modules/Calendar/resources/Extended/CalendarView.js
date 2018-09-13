/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Calendar_CalendarView_Js('Calendar_CalendarExtendedView_Js', {
	getInstanceByView: function () {
		let view = jQuery('#currentView').val();
		let jsFileName = view + 'View';
		let moduleClassName = view + "_" + jsFileName + "_Js";
		let instance;
		if (typeof window[moduleClassName] !== "undefined") {
			instance = new window[moduleClassName]();
		} else {
			instance = new Calendar_CalendarExtendedView_Js();
		}
		return instance;
	}
}, {
	registerEvents() {
		this._super();
	}
});
jQuery(document).ready(function () {
	let instance = Calendar_CalendarExtendedView_Js.getInstanceByView();
	instance.registerEvents();
	Calendar_CalendarExtendedView_Js.currentInstance = instance;
});