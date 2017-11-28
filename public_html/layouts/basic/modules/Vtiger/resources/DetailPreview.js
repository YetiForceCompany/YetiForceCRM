/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
Vtiger_Detail_Js("Vtiger_DetailPreview_Js", {}, {
	registerLinkEvent: function (container) {
		$('#page').on('click', 'a', function (e) {
			e.preventDefault();
			var target = $(this);
			if (!target.closest('div').hasClass('fieldValue')) {
				if (target.attr('href') && target.attr('href') != '#') {
					parent.location.href = target.attr('href');
				}
			}
		});
	},
	registerEvents: function () {
		this._super();
		this.registerLinkEvent();
		app.showScrollBar($("#page"), {
			alwaysVisible: false,
			size: '10px',
			position: 'right',
		});
	},
});
