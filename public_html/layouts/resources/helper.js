/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 *************************************************************************************/
'use strict';

$.Class(
	'Vtiger_Helper_Js',
	{
		checkServerConfigResponseCache: '',
		langCode: '',
		/*
		 * Function to set lang code
		 */
		setLangCode: function () {
			var htmlTag = document.getElementsByTagName('html')[0];
			this.langCode = htmlTag.getAttribute('lang') ? htmlTag.getAttribute('lang') : 'en';
		},
		/*
		 * Function to get lang code
		 */
		getLangCode: function () {
			if (!this.langCode) {
				this.setLangCode();
			}
			return this.langCode;
		},
		/*
		 * Function to get the instance of Mass edit of Email
		 */
		getEmailMassEditInstance: function () {
			var className = 'Emails_MassEdit_Js';
			var emailMassEditInstance = new window[className]();
			return emailMassEditInstance;
		},
		showMessage: function (params) {
			if (typeof params.type === 'undefined') {
				params.type = 'info';
			}
			if (typeof params.title === 'undefined') {
				params.title = app.vtranslate('JS_MESSAGE');
			}
			app.showNotify(params);
		},

		/*
		 * Function to add clickoutside event on the element - By using outside events plugin
		 * @params element---On which element you want to apply the click outside event
		 * @params callbackFunction---This function will contain the actions triggered after clickoutside event
		 */
		addClickOutSideEvent: function (element, callbackFunction) {
			element.one('clickoutside', callbackFunction);
		},
		/*
		 * Function to show horizontal top scroll bar
		 */
		showHorizontalTopScrollBar: function () {
			var container = $('.contentsDiv');
			var topScroll = $('.contents-topscroll', container);
			var bottomScroll = $('.contents-bottomscroll', container);
			$('.bottomscroll-div', container).attr('style', '');
			$('.topscroll-div', container).css('width', $('.bottomscroll-div', container).outerWidth());
			$('.bottomscroll-div', container).css('width', $('.topscroll-div', container).outerWidth());
			topScroll.on('scroll', function () {
				bottomScroll.scrollLeft(topScroll.scrollLeft());
			});
			bottomScroll.on('scroll', function () {
				topScroll.scrollLeft(bottomScroll.scrollLeft());
			});
		},

		hideOptions: function (element, attr, value) {
			var opval = '';
			element.find('option').each(function (index, option) {
				option = $(option);
				if (value != option.data(attr)) {
					option.addClass('d-none');
					option.attr('disabled', 'disabled');
				} else {
					if (opval == '') {
						opval = option.val();
					}
					option.removeClass('d-none');
					option.removeAttr('disabled');
				}
			});
			element.val(opval).trigger('change');
		},
		unique: function (array) {
			return array.filter(function (el, index, arr) {
				return index === arr.indexOf(el);
			});
		}
	},
	{}
);
