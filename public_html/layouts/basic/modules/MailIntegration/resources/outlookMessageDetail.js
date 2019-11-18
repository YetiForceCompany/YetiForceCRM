/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

Office.onReady(info => {
	window.PanelParams = {
		source: 'Outlook',
		device: Office.context.mailbox.diagnostics.hostName
	};
	if (info.host === Office.HostType.Outlook) {
		MailIntegration_Start.registerEvents(Office.context.mailbox);
	}
});

const MailIntegration_Start = {
	iframe: {},
	setIframe() {
		this.iframe = $('#js-iframe');
	},
	showDetailView(mailItem) {
		AppConnector.request(
			$.extend(
				{
					module: 'MailIntegration',
					view: 'Iframe',
					mailFrom: mailItem.from.emailAddress,
					mailSender: mailItem.sender.emailAddress,
					mailSubject: mailItem.subject,
					mailNormalizedSubject: mailItem.normalizedSubject,
					mailMessageId: mailItem.internetMessageId,
					mailDateTimeCreated: mailItem.dateTimeCreated.toISOString()
				},
				window.PanelParams
			)
		)
			.done(responseData => {
				$('#page').html(responseData);
				this.registerLogoutEvents();
			})
			.fail(_ => {
				Office.context.mailbox.item.notificationMessages.replaceAsync('error', {
					type: 'errorMessage',
					message: app.vtranslate('JS_ERROR')
				});
			});
	},
	registerLogoutEvents() {
		this.setIframe();
		let reloadPanelAfterLogout = () => {
			if (!this.isUserLoggedIn()) {
				window.location.reload();
			}
		};
		this.iframe.on('load', reloadPanelAfterLogout);
	},
	registerLoginEvents() {
		let loader;
		let reloadPanelAfterLogin = () => {
			if (this.isUserLoggedIn()) {
				window.location.reload();
			} else {
				loader.progressIndicator({ mode: 'hide' });
			}
		};
		let showLoader = () => {
			loader = $.progressIndicator({
				blockInfo: { enabled: true },
				message: false,
				blockOverlayCSS: {
					'background-color': 'white',
					opacity: 1
				}
			});
		};
		this.iframe.on('load', reloadPanelAfterLogin);
		$(this.iframe[0].contentWindow).on('unload', showLoader);
	},
	isUserLoggedIn() {
		let iframeCONFIG = this.iframe[0].contentWindow.CONFIG;
		return iframeCONFIG && iframeCONFIG.userId;
	},
	registerEvents(mailbox) {
		this.setIframe();
		if (this.iframe.data('view') === 'login') {
			this.registerLoginEvents();
		} else {
			this.showDetailView(mailbox.item);
		}
	}
};
