/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
Vtiger_Detail_Js("Vtiger_DetailPreview_Js", {}, {
	registerLinkEvent: function (container) {
		$('#page').on('click', 'a', function (e) {
			e.preventDefault();
			var target = $(this);
			if (!target.closest('div').hasClass('fieldValue') || target.hasClass('showReferenceTooltip')) {
				if (target.attr('href') && target.attr('href') != '#') {
					top.location.href = target.attr('href');
				}
			}
		});
	},
	registerSizeEvent: function (container) {
		var inframe = $(top.document).find('#listPreviewframe');
		var bodyContents = $('.mainContainer');
		console.log(inframe);
		$('#page').find('.widget_contents').on('Vtiger.Widget.FinishLoad', function (e, widgetName) {
			inframe.height(bodyContents.height() + 33);
			console.log('ok');
		});
		$('#page').on('DetailView.Tab.FinishLoad', function (e, data) {
			inframe.height(bodyContents.height() + 33);
			console.log('ok');
		});
		$('#page').on('DetailView.Tab.PostLoad', function (e, data) {
			inframe.height(bodyContents.height() + 33);
			console.log('ok');
		});
//		var inframe = $('#listPreviewframe');
//		inframe.height($('.bodyContents').height() - 16);
//		$('#listPreviewframe').load(function () {
//			inframe.height($(this).contents().find('.bodyContents').height() + 2);
//		});
		//$('#page').trigger('DetailView.Tab.PostLoad', data);
	},
	registerEvents: function () {
		this._super();
		this.registerLinkEvent();
		this.registerSizeEvent($('#listPreviewframe'));
//		app.showScrollBar($("#page"), {
//			alwaysVisible: false,
//			size: '10px',
//			position: 'right',
//			height: $('.bodyContents').height()
//		});
	},
});
