Office.onReady(info => {
	if (info.host === Office.HostType.Outlook) {
		const listItemTemplate = user => {
			let itemData = user.split(' <');
			const userName = itemData[0];
			const userMail = itemData[1].slice(0, -1);
			return `
			<li class="c-search-item">
				<div class="d-flex flex-nowrap">
					<div class="d-flex flex-wrap">
						<div>
							${userName}
						</div>
						<div>
							${userMail}
						</div>
					</div>
					<div class="btn-group flex-nowrap align-items-center">
						<button class="c-search-item__btn btn btn-xs btn-outline-primary">DW</button>
						<button class="c-search-item__btn btn btn-xs btn-outline-primary">UDW</button>
					</div>
				</div>
			</li>`;
		};
		$.widget('ui.autocomplete', $.ui.autocomplete, {
			_renderItem: function(ul, item) {
				console.log(item);
				return $(listItemTemplate(item.label)).appendTo(ul);
			}
		});
		$('.form-control').autocomplete({
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
						response(resp.result);
					})
					.fail(function(error) {
						console.error(error);
					});
			},
			select: function(event, ui) {
				var selectedItemData = ui.item;
				if (typeof selectedItemData.type !== 'undefined' && selectedItemData.type == 'no results') {
					return false;
				}
				selectedItemData.name = selectedItemData.value;
			}
		});
	}
});
