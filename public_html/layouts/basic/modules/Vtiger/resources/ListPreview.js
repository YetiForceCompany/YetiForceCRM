/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
Vtiger_List_Js("Vtiger_ListPreview_Js", {}, {
	frameProgress: false,
	updatePreview: function (url) {
		var frame = $('#listPreviewframe');
		this.frameProgress = $.progressIndicator({
			position: 'html',
			message: app.vtranslate('JS_FRAME_IN_PROGRESS'),
			blockInfo: {
				enabled: true
			}
		});
		var defaultView = '';
		if (app.getMainParams('defaultDetailViewName')) {
			defaultView = defaultView + '&mode=showDetailViewByMode&requestMode=' + app.getMainParams('defaultDetailViewName'); // full, summary
		}
		frame.attr('src', url.replace("view=Detail", "view=DetailPreview") + defaultView);
	},
	registerPreviewEvent: function () {
		var thisInstance = this;
		var contentHeight = $('#listPreview,#recordsListPreview');
		contentHeight.height($('.mainBody').height() - 16);
		$('#listPreviewframe').load(function () {
			thisInstance.frameProgress.progressIndicator({mode: 'hide'});
			contentHeight.height($(this).contents().find('.bodyContents').height()+2);
		});
		$('.listViewEntriesTable .listViewEntries').first().trigger('click');
	},
	postLoadListViewRecordsEvents: function (container) {
		this._super(container);
		this.registerPreviewEvent();
	},
	registerRowClickEvent: function () {
		var thisInstance = this;
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click', '.listViewEntries', function (e) {
			if ($(e.target).closest('div').hasClass('actions'))
				return;
			if ($(e.target).is('button') || $(e.target).parent().is('button'))
				return;
			if ($(e.target).closest('a').hasClass('noLinkBtn'))
				return;
			if ($(e.target, $(e.currentTarget)).is('td:first-child'))
				return;
			if ($(e.target).is('input[type="checkbox"]'))
				return;
			if ($.contains($(e.currentTarget).find('td:last-child').get(0), e.target))
				return;
			if ($.contains($(e.currentTarget).find('td:first-child').get(0), e.target))
				return;
			var elem = $(e.currentTarget);
			var recordUrl = elem.data('recordurl');
			if (typeof recordUrl == 'undefined') {
				return;
			}
			e.preventDefault();
			$('.listViewEntriesTable .listViewEntries').removeClass('active');
			$(this).addClass('active');
			thisInstance.updatePreview(recordUrl);
		});
	},
	registerEvents: function () {
		this._super();
		this.registerPreviewEvent();
	},
});
