/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

window.MailIntegration_Iframe = {
	mailId: 0,
	container: {},
	iframe: {},
	iframeWindow: {},
	moduleSelect: {},
	addRecordBtn: {},
	loaderParams: {
		blockInfo: { enabled: true },
		message: false
	},
	/**
	 * AppConnector wrapper
	 *
	 * @param   {object}  request
	 *
	 * @return  {object}           AppConnector object with done method
	 */
	connector(request) {
		return AppConnector.request(request).fail((error) => {
			this.hideIframeLoader();
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
			this.mailItem.notificationMessages.replaceAsync('information', {
				type: 'informationalMessage',
				message: message,
				icon: 'iconid',
				persistent: false
			});
		} else {
			this.mailItem.notificationMessages.replaceAsync('error', {
				type: 'errorMessage',
				message: app.vtranslate('JS_ERROR') + ' ' + message
			});
		}
	},
	/**
	 * Register list item events
	 */
	registerListItemEvents() {
		this.container.on('click', '.js-list-item-click', this.onClickListItem.bind(this));
		$(document).on('click', '.popover a', this.onClickLink.bind(this));
		this.container.on('click', '.js-add-related-record', this.onClickQuickCreateBtn.bind(this));
		this.container.on('click', '.js-remove-record', this.onClickDeleteRelation.bind(this));
	},
	/**
	 * On ListItem click actions
	 *
	 * @param   {[type]}  event  [event description]
	 *
	 * @return  {[type]}         [return description]
	 */
	onClickListItem(event) {
		let currentTarget = $(event.currentTarget);
		this.toggleActiveListItems(currentTarget);
		this.onClickLink(event, currentTarget.find('.js-record-link').attr('href'));
	},
	/**
	 * On link click
	 *
	 * @param   {object}  event  click event object
	 * @param   {string}  url
	 */
	onClickLink(event, url) {
		event.preventDefault();
		if (!url) {
			url = $(event.currentTarget).attr('href');
		}
		this.changeIframeSource(url);
	},
	/**
	 * On delete relation click
	 *
	 * @param   {object}  event  click event
	 */
	onClickDeleteRelation(event) {
		event.stopPropagation();
		const currentTarget = $(event.currentTarget);
		const recordData = currentTarget.closest('.js-list-item-click').data();
		this.connector({
			module: 'MailIntegration',
			action: 'Mail',
			mode: 'deleteRelation',
			mailId: this.mailId,
			record: recordData.id,
			recordModule: recordData.module
		}).done((response) => {
			this.showResponseMessage(response['success'], app.vtranslate('JS_REMOVED_RELATION_SUCCESSFULLY'));
			this.reloadView(response['success']);
		});
	},
	/**
	 * On quick create btn click
	 *
	 * @param   {object}  event  click event
	 */
	onClickQuickCreateBtn(event) {
		event.stopPropagation();
		const currentTarget = $(event.currentTarget);
		const recordData = currentTarget.closest('.js-list-item-click').data();
		const callbackFunction = () => {
			this.iframeWindow.location.reload();
		};
		let newRecordData = {
			sourceModule: recordData.module,
			sourceRecord: recordData.id
		};
		this.showQuickCreateForm(event.currentTarget.dataset.module, {
			data: newRecordData,
			callbackFunction
		});
	},
	/**
	 * Fill new record data in quick create form
	 *
	 * @param   {string}  moduleName  [moduleName description]
	 *
	 * @return  {object}              call asyncGetMailBody which returns Promise
	 */
	fillNewRecordData(moduleName) {
		const data = {
			email: this.mailItem.from.emailAddress,
			email1: this.mailItem.from.emailAddress,
			relationOperation: true,
			relatedRecords: $.map(this.container.find('.js-list-item-click'), (record) => {
				return { module: record.dataset.module, id: record.dataset.id };
			})
		};
		const fillNameFields = (first) => {
			const nameData = this.mailItem.from.displayName.split(' ');
			const firstName = nameData.shift();
			const lastName = nameData.join(' ');
			return first ? firstName : lastName;
		};
		let autoCompleteMap = JSON.parse(this.container.find('.js-mailAutoCompleteFields').val());
		if (autoCompleteMap && autoCompleteMap[moduleName]) {
			let map = autoCompleteMap[moduleName];
			for (let name in map) {
				if (map.hasOwnProperty(name) && map[name]) {
					switch (map[name]) {
						case 'fromNameFirstPart':
							data[name] = fillNameFields(true);
							break;
						case 'fromNameSecondPart':
							data[name] = fillNameFields(false);
							break;
						case 'fromName':
							data[name] = this.mailItem.from.displayName;
							break;
						case 'subject':
							data[name] = this.mailItem.subject;
							break;
						case 'email':
							data[name] = this.mailItem.from.emailAddress;
							break;
					}
				}
			}
		}
		this.mailDetails = {};
		return new Promise((resolve) => {
			this.asyncGetMailBody().then(() => {
				data.description = this.mailDetails.mailBody;
				resolve(data);
			});
		});
	},
	/**
	 * Toggle active list items
	 *
	 * @param   {object}  targetListItem  jQuery
	 */
	toggleActiveListItems(targetListItem) {
		targetListItem.siblings().removeClass('active');
		targetListItem.addClass('active');
	},
	/**
	 * Change iframe source
	 *
	 * @param   {string}  url
	 */
	changeIframeSource(url) {
		this.iframe.attr('src', url);
		this.showIframeLoader();
	},
	/**
	 * Add relation
	 *
	 * @param   {number}  recordId
	 * @param   {string}  moduleName
	 */
	addRelation(recordId, moduleName) {
		this.connector({
			module: 'MailIntegration',
			action: 'Mail',
			mode: 'addRelation',
			mailId: this.mailId,
			record: recordId,
			recordModule: moduleName
		}).done((response) => {
			this.showResponseMessage(response['success'], app.vtranslate('JS_ADDED_RELATION_SUCCESSFULLY'));
			this.reloadView(response['success']);
		});
	},
	/**
	 * Show quick create form
	 *
	 * @param   {string}  moduleName
	 * @param   {object}  quickCreateParams
	 */
	showQuickCreateForm(moduleName, quickCreateParams = {}) {
		quickCreateParams = Object.assign({ noCache: true, data: {} }, quickCreateParams);
		this.fillNewRecordData(moduleName).then((data) => {
			quickCreateParams.data = Object.assign(data, quickCreateParams.data);
			App.Components.QuickCreate.createRecord(moduleName, quickCreateParams);
		});
	},
	/**
	 * Register iframe events
	 */
	registerIframeEvents() {
		const link = this.container.find('.js-list-item-click').first();
		this.initIframeLoader();
		if (link.length) {
			link.addClass('active');
			this.iframe.attr('src', link.find('.js-record-link').attr('href'));
			this.iframe.on('load', () => {
				this.hideIframeLoader();
			});
		} else {
			this.hideIframeLoader();
		}
	},
	/**
	 * Register import click
	 */
	registerImportClick() {
		this.container.on('click', '.js-import-mail', (e) => {
			this.showIframeLoader();
			this.getMailDetails().then(() => {
				this.connector(
					Object.assign(
						{
							module: 'MailIntegration',
							action: 'Import'
						},
						this.mailDetails,
						window.PanelParams
					)
				).done((response) => {
					this.hideIframeLoader();
					this.showResponseMessage(response['success'], app.vtranslate('JS_IMPORT'));
					this.reloadView(response['success']);
				});
			});
		});
	},
	/**
	 * Get mail details
	 *
	 * @return  {object}  Promise
	 */
	getMailDetails() {
		let mailItem = this.mailItem;
		if (mailItem.attachments.length > 0) {
			let outputString = '';
			for (let i = 0; i < mailItem.attachments.length; i++) {
				let attachment = mailItem.attachments[i];
				outputString += '<BR>' + i + '. Name: ';
				outputString += attachment.name;
				outputString += '<BR>ID: ' + attachment.id;
				outputString += '<BR>contentType: ' + attachment.contentType;
				outputString += '<BR>size: ' + attachment.size;
				outputString += '<BR>attachmentType: ' + attachment.attachmentType;
				outputString += '<BR>isInline: ' + attachment.isInline;
			}
		}
		this.mailDetails = {
			mailFrom: this.parseEmailAddressDetails(mailItem.from),
			mailSender: mailItem.sender.emailAddress,
			mailTo: this.parseEmailAddressDetails(mailItem.to),
			mailCc: this.parseEmailAddressDetails(mailItem.cc),
			mailMessageId: mailItem.internetMessageId,
			mailSubject: mailItem.subject,
			mailNormalizedSubject: mailItem.normalizedSubject,
			mailDateTimeCreated: mailItem.dateTimeCreated.toISOString()
		};
		return this.asyncGetMailBody();
	},
	/**
	 * Get mail body async function
	 *
	 * @param   {function}  callback
	 *
	 * @return  {object}            Promise
	 */
	asyncGetMailBody() {
		return Promise.all([
			new Promise((resolve, reject) => {
				this.mailItem.body.getAsync(Office.CoercionType.Html, (body) => {
					if (body.status === 'succeeded') {
						this.mailDetails.mailBody = body.value;
						resolve(body);
					} else {
						reject(body);
					}
				});
			}),
			new Promise((resolve, reject) => {
				this.mailItem.getAllInternetHeadersAsync((body) => {
					if (body.status === 'succeeded') {
						this.mailDetails.mailHeaders = body.value;
						resolve(body);
					} else {
						reject(body);
					}
				});
			})
		]);
	},
	/**
	 * Parse email address details
	 *
	 * @param   {object}  data
	 *
	 * @return  {string}        e-mail address
	 */
	parseEmailAddressDetails(data) {
		let fn = function (row) {
			return row.emailAddress;
		};
		if ($.isArray(data)) {
			let rows = [];
			$.each(data, function (index, value) {
				rows[index] = fn(value);
			});
			return rows;
		} else {
			return fn(data);
		}
	},
	/**
	 * Set iframe height
	 */
	setIframeHeight() {
		this.iframe.height($(window).height() - this.iframe.offset().top);
	},
	/**
	 * Show iframe loader
	 */
	showIframeLoader() {
		this.iframeLoader.progressIndicator(this.loaderParams);
	},
	/**
	 * Hide iframe loader
	 */
	hideIframeLoader() {
		this.iframeLoader.progressIndicator({ mode: 'hide' });
	},
	/**
	 * Init iframe loader
	 */
	initIframeLoader() {
		this.iframeLoader = $.progressIndicator(this.loaderParams);
	},
	/**
	 * Register modules select
	 */
	registerModulesSelect() {
		this.moduleSelect = App.Fields.Picklist.showSelect2ElementView(this.container.find('.js-modules'));
		this.moduleSelect.on('change', this.registerModulesSelectChange.bind(this));
		this.container.find('.js-select-record').on('click', (e) => {
			let params = {
				module: this.moduleSelect[0].value,
				src_module: 'OSSMailView'
			};
			this.container.find('.js-list-item-click').each(function (index) {
				let data = $(this).data();
				if (data.field == 'link' || data.field == 'process') {
					params[data.field] = data.id;
				}
			});
			app.showRecordsList(params, (_modal, instance) => {
				instance.setSelectEvent((responseData) => {
					this.addRelation(responseData.id, params.module);
				});
			});
		});
	},
	/**
	 * Register modules select change
	 */
	registerModulesSelectChange() {
		if (this.moduleSelect.select2('data')[0].element.dataset.addRecord) {
			this.addRecordBtn.removeClass('d-none');
		} else {
			this.addRecordBtn.addClass('d-none');
		}
	},
	/**
	 * Register add record
	 */
	registerAddRecord() {
		this.addRecordBtn.on('click', (e) => {
			let moduleName = this.moduleSelect[0].value;
			let callbackFunction = ({ result }) => {
				this.addRelation(result._recordId, moduleName);
			};
			let quickCreateParams = { callbackFunction };
			this.showQuickCreateForm(moduleName, quickCreateParams);
		});
	},
	/**
	 * Reload view
	 */
	reloadView(condition) {
		if (condition) {
			window.location.reload();
		}
	},
	/**
	 * Register events
	 */
	registerEvents() {
		this.container = $('#page');
		this.iframe = $('#js-iframe');
		this.iframeWindow = this.iframe[0].contentWindow;
		this.addRecordBtn = this.container.find('.js-add-record');
		this.mailId = this.container.find('.js-iframe-container').data('mailId');
		this.mailItem = Office.context.mailbox.item;
		if (this.iframe.length) {
			this.registerListItemEvents();
			this.registerIframeEvents();
			this.setIframeHeight();
			if (this.mailId) {
				this.registerModulesSelect();
				this.registerAddRecord();
			} else {
				this.registerImportClick();
			}
		}
	}
};
window.App.Components.Scrollbar.active = false;
(function ($) {
	window.MailIntegration_Iframe.registerEvents();
})($);
