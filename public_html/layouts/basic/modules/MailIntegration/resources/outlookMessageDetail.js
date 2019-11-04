/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

Office.onReady(info => {
	window.PanelParams = {
		source: 'outlook',
		device: Office.context.mailbox.diagnostics.hostName
	};
	console.log(Office.context.mailbox);
	if (info.host === Office.HostType.Outlook) {
		MailIntegration_Start.registerEvents(Office.context.mailbox);
	}
});

const MailIntegration_Start = {
	showDetailView(mailItem) {
		console.log(mailItem);
		AppConnector.request(
			$.extend(
				{
					module: 'MailIntegration',
					view: 'Detail',
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
			.done(function(responseData) {
				$('#page').html(responseData);
			})
			.fail(function(error) {
				console.error(error);
			});
	},
	registerEvents(mailbox) {
		this.showDetailView(mailbox.item);
	}
};
