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
			})
			.fail(_ => {
				Office.context.mailbox.item.notificationMessages.replaceAsync('error', {
					type: 'errorMessage',
					message: app.vtranslate('JS_ERROR')
				});
			});
	},
	registerEvents(mailbox) {
		this.showDetailView(mailbox.item);
	}
};
