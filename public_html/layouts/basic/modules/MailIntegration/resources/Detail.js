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
	registerRowEvents() {
		this.container.on('click', '.js-row-click', this.rowClick.bind(this));
		this.container.on('click', '.js-add-related-record', this.showQuickCreateClick.bind(this));
		this.container.on('click', '.js-remove-record', this.deleteRelationshipClick.bind(this));
	},
	rowClick(event) {
		let currentTarget = $(event.currentTarget);
		this.changeIframeSource(currentTarget);
		event.preventDefault();
		return false;
	},
	changeIframeSource(targetRow) {
		targetRow.siblings().removeClass('active');
		targetRow.addClass('active');
		this.iframe.attr('src', targetRow.find('.js-record-link').attr('href'));
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
			.done(function(responseData) {
				console.info(responseData);
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
			sourceModule: recordData.module,
			sourceRecord: recordData.id
		});
		return false;
	},
	showQuickCreateForm(moduleName, relatedParams = {}) {
		relatedParams['relationOperation'] = true;
		const quickCreateParams = {
			data: relatedParams,
			noCache: true,
			showInIframe: true
		};
		const headerInstance = new this.iframeWindow.Vtiger_Header_Js();
		headerInstance.quickCreateModule(moduleName, quickCreateParams);
	},
	registerIframeEvents() {
		const link = this.container.find('.js-row-click').first();
		this.initIframeLoader();
		if (link.length) {
			link.addClass('active');
			this.iframe.attr('src', link.find('.js-record-link').attr('href'));
		}
		this.iframe.on('load', () => {
			console.log('loaded');
			this.iframeLoader.progressIndicator({ mode: 'hide' });
		});
	},
	registerImportClick() {
		this.container.on('click', '.js-import-mail', e => {
			this.getMailDetails().then(mails => {
				console.log(mails);
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
					.done(function(responseData) {
						console.info(responseData);
					})
					.fail(function(error) {
						console.error(error);
					});
			});
		});
	},
	getMailDetails() {
		let mailItem = Office.context.mailbox.item;
		// mailItem.internetHeaders.getAsync(['Date', 'date'], function(body) {
		// 	console.log(body);
		// });
		if (mailItem.attachments.length > 0) {
			let outputString = '';
			for (let i = 0; i < mailItem.attachments.length; i++) {
				let attachment = mailItem.attachments[i];
				console.log(attachment);
				outputString += '<BR>' + i + '. Name: ';
				outputString += attachment.name;
				outputString += '<BR>ID: ' + attachment.id;
				outputString += '<BR>contentType: ' + attachment.contentType;
				outputString += '<BR>size: ' + attachment.size;
				outputString += '<BR>attachmentType: ' + attachment.attachmentType;
				outputString += '<BR>isInline: ' + attachment.isInline;
			}
			console.log(outputString);
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
	initIframeLoader() {
		this.iframeLoader = $.progressIndicator(this.loaderParams);
	},
	registerModulesSelect() {
		this.moduleSelect = App.Fields.Picklist.showSelect2ElementView(this.container.find('.js-modules'));
		this.moduleSelect.on('change', this.registerModulesSelectChange.bind(this));
		this.container.find('.js-select-record').on('click', e => {
			const params = {
				module: this.moduleSelect[0].value,
				src_module: 'OSSMailView',
				modalParams: {
					showInIframe: true
				}
			};
			this.iframeWindow.app.showRecordsList(params, (modal, instance) => {
				instance.setSelectEvent((responseData, e) => {
					AppConnector.request({
						module: 'MailIntegration',
						action: 'Mail',
						mode: 'addRelation',
						mailId: this.mailId,
						record: responseData.id,
						recordModule: params.module
					}).done(data => {
						let response = data['result'];
						let notifyParams = {
							text: response['data'],
							animation: 'show'
						};
						if (response['success']) {
							notifyParams.type = 'info';
						}
						this.iframeWindow.Vtiger_Helper_Js.showPnotify(notifyParams);
						this.loadRelationsList();
					});
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
			this.showQuickCreateForm(this.moduleSelect[0].value);
		});
	},
	loadRelationsList() {
		//params to overwrite - action loading
		const params = {
			module: 'MailIntegration',
			view: 'ActionsMailExist'
		};
		AppConnector.request(params).done(response => {
			this.container.find('.js-relations-container').html(response);
		});
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
