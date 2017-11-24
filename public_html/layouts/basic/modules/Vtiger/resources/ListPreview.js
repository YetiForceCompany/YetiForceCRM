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
		frame.attr('src', url.replace("view=Detail", "view=DetailPreview") + '&mode=showDetailViewByMode&requestMode=full');
	},
	registerPreviewEvent: function () {
		var thisInstance = this;
//		Split(['#recordsList', '#listPreview'], {
//			minSize: 400
//		});
//		$('#recordsListPreview .gutter').height($('.mainBody').height());
		var height = $('.mainBody').height()
		$('#listPreview,#recordsListPreview').height(height - 16);
		$('#listPreviewframe').load(function () {
			thisInstance.frameProgress.progressIndicator({mode: 'hide'});
		});
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
			thisInstance.updatePreview(recordUrl);
		});
	},
	registerEvents: function () {
		this._super();
		this.registerPreviewEvent();
	},
});
