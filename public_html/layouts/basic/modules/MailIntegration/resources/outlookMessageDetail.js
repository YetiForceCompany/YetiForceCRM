/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
window.MailIntegration_Start = {
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
			.done((responseData) => {
				$('#page').html(responseData);
				this.registerLogoutEvents();
			})
			.fail((_) => {
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
		let src = this.iframe[0].getAttribute('src-a');
		if (src && this.iframe[0].getAttribute('src') == undefined) {
			this.iframe[0].removeAttribute('src-a');
			this.iframe[0].setAttribute('src', src);
		}
	},
	/**
	 * Is user logged in.
	 *
	 * @return  {boolean}
	 */
	isUserLoggedIn() {
		return !(
			this.iframe[0].contentWindow.document.body.dataset.module == 'Users' &&
			this.iframe[0].contentWindow.document.body.dataset.view == 'Login'
		);
	},
	showConsole() {
		let s = '';
		let x = '';
		for (var p in navigator) {
			s += p + ' : ' + navigator[p] + '<br>';
			x += p + ' : ' + navigator[p] + '\n';
		}
		console.log(x);
		document.body.innerHTML +=
			'<div style="position:absolute;width:100%;height:100%;z-index:100;background:#fff;left: 0px; top: 50%;overflow-y: auto;">' +
			s +
			'</div>';
	},
	/**
	 * Register events.
	 *
	 * @param   {object}  mailbox  Office mailbox
	 */
	registerEvents(mailbox) {
		//this.showConsole();
		if (!$('.js-exception-error').length) {
			this.setIframe();
			if (this.iframe.data('view') === 'login') {
				this.registerLoginEvents();
			} else {
				this.showDetailView(mailbox.item);
			}
		}
	},
	reloadView(data) {
		window.MailIntegration_Start.showDetailView(Office.context.mailbox.item);
	}
};
if (typeof Office === 'undefined') {
	app.showNotify({
		title: app.vtranslate('JS_ERROR'),
		type: 'error'
	});
} else {
	Office.onReady((info) => {
		window.PanelParams = {
			source: 'Outlook',
			device: Office.context.mailbox.diagnostics.hostName
		};
		if (info.host === Office.HostType.Outlook) {
			window.MailIntegration_Start.registerEvents(Office.context.mailbox);
			Office.context.mailbox.addHandlerAsync(Office.EventType.ItemChanged, window.MailIntegration_Start.reloadView);
		}
	});
}
