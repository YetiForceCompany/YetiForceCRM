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
	showResponseMessage(success, message) {
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
				message: app.vtranslate('JS_ERROR')
			});
		}
	},
	registerRowEvents() {
		this.container.on('click', '.js-row-click', this.rowClick.bind(this));
		$(document).on('click', '.popover a', this.linkClick.bind(this));
		this.container.on('click', '.js-add-related-record', this.showQuickCreateClick.bind(this));
		this.container.on('click', '.js-remove-record', this.deleteRelationshipClick.bind(this));
	},
	rowClick(event) {
		let currentTarget = $(event.currentTarget);
		this.toggleActiveListItems(currentTarget);
		this.linkClick(event, currentTarget.find('.js-record-link').attr('href'));
	},
	toggleActiveListItems(targetRow) {
		targetRow.siblings().removeClass('active');
		targetRow.addClass('active');
	},
	linkClick(event, href) {
		if (!href) {
			href = $(event.currentTarget).attr('href');
		}
		this.changeIframeSource(href);
		event.preventDefault();
		return false;
	},
	changeIframeSource(href) {
		this.iframe.attr('src', href);
		this.showIframeLoader();
	},
	deleteRelationshipClick(event) {
		const currentTarget = $(event.currentTarget);
		const recordData = currentTarget.closest('.js-row-click').data();
		AppConnector.request({
			module: 'MailIntegration',
			action: 'Mail',
			mode: 'deleteRelation',
			mailId: this.mailId,
			record: recordData.id,
			recordModule: recordData.module
		})
			.done(response => {
				this.showResponseMessage(response['success'], app.vtranslate('JS_REMOVED_RELATION_SUCCESSFULLY'));
				this.reloadView(response['success']);
			})
			.fail(function(error) {
				console.error(error);
			});
		return false;
	},
	showQuickCreateClick(event) {
		const currentTarget = $(event.currentTarget);
		const recordData = currentTarget.closest('.js-row-click').data();
		this.showQuickCreateForm(event.currentTarget.dataset.module, {
			data: {
				sourceModule: recordData.module,
				sourceRecord: recordData.id
			}
		});
		return false;
	},
	addRelation(recordId, moduleName) {
		AppConnector.request({
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
	registerImportClick() {
		this.container.on('click', '.js-import-mail', e => {
			this.showIframeLoader();
			this.getMailDetails().then(mails => {
				AppConnector.request(
					Object.assign(
						{
							module: 'MailIntegration',
							action: 'Import'
						},
						mails,
						window.PanelParams
					)
				)
					.done(response => {
						this.hideIframeLoader();
						this.showResponseMessage(response['success'], app.vtranslate('JS_IMPORT'));
						this.reloadView(response['success']);
					})
					.fail(error => {
						console.error(error);
						this.hideIframeLoader();
					});
			});
		});
	},
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
	setIframeHeight() {
		this.iframe.height($(window).height() - this.iframe.offset().top);
	},
	showIframeLoader() {
		this.iframeLoader.progressIndicator(this.loaderParams);
	},
	hideIframeLoader() {
		this.iframeLoader.progressIndicator({ mode: 'hide' });
	},
	initIframeLoader() {
		this.iframeLoader = $.progressIndicator(this.loaderParams);
	},
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
	registerModulesSelectChange() {
		if (this.moduleSelect.select2('data')[0].element.dataset.addRecord) {
			this.addRecordBtn.removeClass('d-none');
		} else {
			this.addRecordBtn.addClass('d-none');
		}
	},
	registerAddRecord() {
		this.addRecordBtn.on('click', e => {
			const moduleName = this.moduleSelect[0].value;
			const callbackFunction = ({ result }) => {
				this.addRelation(moduleName, result._recordId);
			};
			this.showQuickCreateForm(moduleName, { callbackFunction });
		});
	},
	reloadView(condition) {
		if (condition) {
			window.location.reload();
		}
	},
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
