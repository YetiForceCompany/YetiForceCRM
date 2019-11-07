Office.onReady(info => {
	if (info.host === Office.HostType.Outlook) {
		const listItemTemplate = user => {
			return `
			<li class="c-search-item js-search-item">
				<div class="d-flex flex-nowrap">
					<div class="d-flex flex-wrap">
						<div>
							${user.name}
						</div>
						<div>
							${user.mail}
						</div>
					</div>
					<div class="btn-group flex-nowrap align-items-center">
						<button class="c-search-item__btn btn btn-xs btn-outline-primary" data-copy-target="cc">
							${app.vtranslate('JS_CC')}
						</button>
						<button class="c-search-item__btn btn btn-xs btn-outline-primary" data-copy-target="bcc">
							${app.vtranslate('JS_BCC')}
						</button>
					</div>
				</div>
			</li>`;
		};
		$.widget('ui.autocomplete', $.ui.autocomplete, {
			_renderItem: function(ul, item) {
				return $(listItemTemplate(item)).appendTo(ul);
			}
		});
		$('.js-search-input').autocomplete({
			delay: '600',
			minLength: '3',
			classes: {
				'ui-autocomplete': 'mobile'
			},
			source: function(request, response) {
				AppConnector.request({
					module: 'MailIntegration',
					action: 'Mail',
					mode: 'findEmail',
					search: request.term
				})
					.done(resp => {
						const data = resp.result.map(user => {
							let userData = user.split(' <');
							const name = userData[0];
							const mail = userData[1].slice(0, -1);
							return { name, mail };
						});
						response(data);
					})
					.fail(function(error) {
						console.error(error);
					});
			},
			select: function({ toElement }, { item }) {
				const newRecipient = [
					{
						displayName: item.name,
						emailAddress: item.mail
					}
				];
				const recipientsField = toElement.dataset.copyTarget ? toElement.dataset.copyTarget : 'to';
				Office.context.mailbox.item[recipientsField].addAsync(newRecipient, function(result) {
					if (result.error) {
						Office.context.mailbox.item.notificationMessages.replaceAsync('error', {
							type: 'errorMessage',
							message: app.vtranslate('JS_ERROR') + ' ' + result.error
						});
					}
				});
			}
		});
	}
});
