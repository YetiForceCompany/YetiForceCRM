/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

const MailIntegration_Detail = {
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
		return AppConnector.request(request).fail(error => {
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
	 * Register row events
	 */
	registerRowEvents() {
		this.container.on('click', '.js-row-click', this.onRowClick.bind(this));
		$(document).on('click', '.popover a', this.onLinkClick.bind(this));
		this.container.on('click', '.js-add-related-record', this.onQuickCreateBtnClick.bind(this));
		this.container.on('click', '.js-remove-record', this.onDeleteRelationClick.bind(this));
	},
	/**
	 * On row click actions
	 *
	 * @param   {[type]}  event  [event description]
	 *
	 * @return  {[type]}         [return description]
	 */
	onRowClick(event) {
		let currentTarget = $(event.currentTarget);
		this.toggleActiveListItems(currentTarget);
		this.onLinkClick(event, currentTarget.find('.js-record-link').attr('href'));
	},
	/**
	 * Toggle active list items
	 *
	 * @param   {object}  targetRow  jQuery
	 */
	toggleActiveListItems(targetRow) {
		targetRow.siblings().removeClass('active');
		targetRow.addClass('active');
	},
	/**
	 * On link click
	 *
	 * @param   {object}  event  click event object
	 * @param   {string}  url
	 */
	onLinkClick(event, url) {
		event.preventDefault();
		if (!url) {
			url = $(event.currentTarget).attr('href');
		}
		this.changeIframeSource(url);
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
	 * On delete relation click
	 *
	 * @param   {object}  event  click event
	 */
	onDeleteRelationClick(event) {
		event.stopPropagation();
		const currentTarget = $(event.currentTarget);
		const recordData = currentTarget.closest('.js-row-click').data();
		this.connector({
			module: 'MailIntegration',
			action: 'Mail',
			mode: 'deleteRelation',
			mailId: this.mailId,
			record: recordData.id,
			recordModule: recordData.module
		}).done(response => {
			this.showResponseMessage(response['success'], app.vtranslate('JS_REMOVED_RELATION_SUCCESSFULLY'));
			this.reloadView(response['success']);
		});
	},
	/**
	 * On quick create btn click
	 *
	 * @param   {object}  event  click event
	 */
	onQuickCreateBtnClick(event) {
		event.stopPropagation();
		const currentTarget = $(event.currentTarget);
		const recordData = currentTarget.closest('.js-row-click').data();
		this.showQuickCreateForm(event.currentTarget.dataset.module, {
			data: {
				sourceModule: recordData.module,
				sourceRecord: recordData.id
			}
		});
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
		}).done(response => {
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
		quickCreateParams.data.relationOperation = true;
		App.Components.QuickCreate.createRecord(moduleName, quickCreateParams);
	},
	registerIframeEvents() {
		const link = this.container.find('.js-row-click').first();
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
		this.container.on('click', '.js-import-mail', e => {
			this.showIframeLoader();
			this.getMailDetails().then(mails => {
				this.connector(
					Object.assign(
						{
							module: 'MailIntegration',
							action: 'Import'
						},
						mails,
						window.PanelParams
					)
				).done(response => {
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
		let mailItem = Office.context.mailbox.item;
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
		return new Promise((resolve, reject) => {
			mailItem.body.getAsync(Office.CoercionType.Html, body => {
				if (body.status === 'succeeded') {
					resolve({
						mailFrom: this.parseEmailAddressDetails(mailItem.from),
						mailSender: mailItem.sender.emailAddress,
						mailTo: this.parseEmailAddressDetails(mailItem.to),
						mailCc: this.parseEmailAddressDetails(mailItem.cc),
						mailMessageId: mailItem.internetMessageId,
						mailSubject: mailItem.subject,
						mailNormalizedSubject: mailItem.normalizedSubject,
						mailDateTimeCreated: mailItem.dateTimeCreated.toISOString(),
						mailBody: body.value
					});
				} else {
					reject(body);
				}
			});
		});
	},
	/**
	 * Parse email address details
	 *
	 * @param   {object}  data
	 *
	 * @return  {string}        e-mail address
	 */
	parseEmailAddressDetails(data) {
		let fn = function(row) {
			return row.emailAddress;
		};
		if ($.isArray(data)) {
			let rows = [];
			$.each(data, function(index, value) {
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
		this.container.find('.js-select-record').on('click', e => {
			const params = {
				module: this.moduleSelect[0].value,
				src_module: 'OSSMailView'
			};
			app.showRecordsList(params, (modal, instance) => {
				instance.setSelectEvent((responseData, e) => {
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
		this.addRecordBtn.on('click', e => {
			const moduleName = this.moduleSelect[0].value;
			const callbackFunction = ({ result }) => {
				this.addRelation(moduleName, result._recordId);
			};
			this.showQuickCreateForm(moduleName, { callbackFunction });
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
		this.mailId = this.container.find('.js-panel').data('mailId');
		if (this.iframe.length) {
			this.registerRowEvents();
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

(function($) {
	MailIntegration_Detail.registerEvents();
})($);
