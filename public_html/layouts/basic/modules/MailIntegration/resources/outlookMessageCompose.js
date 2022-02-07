/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.MailIntegration_Compose = {
	/**
	 * AppConnector wrapper
	 *
	 * @param   {object}  request
	 *
	 * @return  {object}           AppConnector object with done method
	 */
	connector(request) {
		return AppConnector.request(request).fail((error) => {
			this.showResponseMessage(false);
		});
	},
	/**
	 * Show response message
	 *
	 * @param   {boolean}  success
	 * @param   {string}  message
	 */
	showResponseMessage(success, message = '') {
		if (success) {
			Office.context.mailbox.item.notificationMessages.replaceAsync('information', {
				type: 'informationalMessage',
				message: message,
				icon: 'iconid',
				persistent: false
			});
		} else {
			Office.context.mailbox.item.notificationMessages.replaceAsync('error', {
				type: 'errorMessage',
				message: app.vtranslate('JS_ERROR') + ' ' + message
			});
		}
	},
	/**
	 * Registered autocomplete template
	 *
	 * @return  {object}  overwrite ui-autocomplete list item template
	 */
	registerAutocompleteTemplate() {
		$.widget('ui.autocomplete', $.ui.autocomplete, {
			_renderItem: function (ul, item) {
				let listItemTemplate = (user) => {
					return `<li class="c-search-item js-search-item">
					<div class="">
								<div class="row">
									<div class="col-9 pr-0">
										<div class="u-fs-14px">${user.name}</div>
										<div class="c-search-item__mail small">${user.mail}</div>
									</div>
									<div class="col-3 pr-0 text-right">
										<button class="c-search-item__btn btn btn-xs btn-outline-primary" data-copy-target="cc">${app.vtranslate(
											'JS_CC'
										)}</button>
										<button class="c-search-item__btn btn btn-xs btn-outline-primary" data-copy-target="bcc">${app.vtranslate(
											'JS_BCC'
										)}</button>
									</div>
								</div></div>
							</li>`;
				};
				return $(listItemTemplate(item)).appendTo(ul);
			}
		});
	},
	/**
	 * Register autocomplete
	 *
	 * @return  {object}  autocomplete instance
	 */
	registerAutocomplete() {
		return this.container.find('.js-search-input').autocomplete({
			delay: '600',
			minLength: '3',
			classes: {
				'ui-autocomplete': 'mobile'
			},
			source: function (request, response) {
				window.MailIntegration_Compose.findEmail(request, response);
			},
			select: function (e, ui) {
				window.MailIntegration_Compose.onSelectRecipient(e.toElement, ui.item);
			}
		});
	},
	/**
	 * Find mail action for autocomplete source
	 *
	 * @param   {object}  request   autocomplete param
	 * @param   {fuction}  callBack  autocomplete callBack
	 */
	findEmail(request, callBack) {
		this.connector({
			module: 'MailIntegration',
			action: 'Mail',
			mode: 'findEmail',
			search: request.term
		}).done((responseData) => {
			let data = responseData.result.map((user) => {
				let userData = user.split(' <');
				let name = userData[0];
				let mail = userData[1].slice(0, -1);
				return { name, mail };
			});
			callBack(data);
		});
	},
	/**
	 * [onRecipientSelect description]
	 *
	 * @param   {object}  toElement  html node object
	 * @param   {object}  item       selected item object
	 */
	onSelectRecipient(toElement, item) {
		this.copyRecipient(toElement.dataset.copyTarget ? toElement.dataset.copyTarget : 'to', [
			{
				displayName: item.name,
				emailAddress: item.mail
			}
		]);
	},
	/**
	 * Copy recipient to outlook field
	 *
	 * @param   {string}  recipientsField  to, cc, bcc
	 * @param   {object}  newRecipient
	 */
	copyRecipient(recipientsField, newRecipient) {
		Office.context.mailbox.item[recipientsField].addAsync(newRecipient, function (result) {
			if (result.error) {
				Office.context.mailbox.item.notificationMessages.replaceAsync('error', {
					type: 'errorMessage',
					message: app.vtranslate('JS_ERROR') + ' ' + result.error
				});
			}
		});
	},
	registerEvents() {
		if (!$('.js-exception-error').length) {
			this.container = $('#page');
			this.registerAutocompleteTemplate();
			this.registerAutocomplete();
		}
	}
};
if (typeof Office === 'undefined') {
	app.showNotify({
		title: app.vtranslate('JS_ERROR'),
		type: 'error'
	});
} else {
	(function ($) {
		Office.onReady((info) => {
			if (info.host === Office.HostType.Outlook) {
				window.MailIntegration_Compose.registerEvents();
			}
		});
	})($);
}
