/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

const MailIntegration_Start = {
	iframe: {},
	/**
	 * Set iframe element.
	 */
	setIframe() {
		this.iframe = $('#js-iframe');
	},
	/**
	 * Show detail view.
	 *
	 * @param   {object}  mailItem  Office mailbox.item
	 */
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
	/**
	 * Register logout events
	 */
	registerLogoutEvents() {
		this.setIframe();
		let reloadPanelAfterLogout = () => {
			if (!this.isUserLoggedIn()) {
				window.location.reload();
			}
		};
		this.iframe.on('load', reloadPanelAfterLogout);
	},
	/**
	 * Register login events.
	 */
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
	/**
	 * Is user logged in.
	 *
	 * @return  {boolean}
	 */
	isUserLoggedIn() {
		let iframeCONFIG = this.iframe[0].contentWindow.CONFIG;
		return iframeCONFIG && iframeCONFIG.userId;
	},
	/**
	 * Register events.
	 *
	 * @param   {object}  mailbox  Office mailbox
	 */
	registerEvents(mailbox) {
		if (!$('.js-exception-error').length) {
			this.setIframe();
			if (this.iframe.data('view') === 'login') {
				this.registerLoginEvents();
			} else {
				this.showDetailView(mailbox.item);
			}
		}
	}
};
Office.onReady(info => {
	window.PanelParams = {
		source: 'Outlook',
		device: Office.context.mailbox.diagnostics.hostName
	};
	if (info.host === Office.HostType.Outlook) {
		MailIntegration_Start.registerEvents(Office.context.mailbox);
	}
});
