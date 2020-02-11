/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

const MailIntegration_Compose = {
	/**
	 * AppConnector wrapper
	 *
	 * @param   {object}  request
	 *
	 * @return  {object}           AppConnector object with done method
	 */
	connector(request) {
		return AppConnector.request(request).fail(error => {
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
			_renderItem: function(ul, item) {
				const listItemTemplate = user => {
					return `<li class="c-search-item js-search-item">
					<div class="">
								<div class="row">
									<div class="col-9 pr-0">
										<div class="u-font-size-14px">${user.name}</div>
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
			source: this.findEmail.bind(this),
			select: this.onSelectRecipient.bind(this)
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
		}).done(responseData => {
			const data = responseData.result.map(user => {
				let userData = user.split(' <');
				const name = userData[0];
				const mail = userData[1].slice(0, -1);
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
	onSelectRecipient({ toElement }, { item }) {
		const newRecipient = [
			{
				displayName: item.name,
				emailAddress: item.mail
			}
		];
		const recipientsField = toElement.dataset.copyTarget ? toElement.dataset.copyTarget : 'to';
		this.copyRecipient(recipientsField, newRecipient);
	},
	/**
	 * Copy recipient to outlook field
	 *
	 * @param   {string}  recipientsField  to, cc, bcc
	 * @param   {object}  newRecipient
	 */
	copyRecipient(recipientsField, newRecipient) {
		Office.context.mailbox.item[recipientsField].addAsync(newRecipient, function(result) {
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
(function($) {
	Office.onReady(info => {
		if (info.host === Office.HostType.Outlook) {
			MailIntegration_Compose.registerEvents();
		}
	});
})($);
