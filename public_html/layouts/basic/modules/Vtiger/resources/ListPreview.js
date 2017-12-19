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
		var iframe = $("#listPreviewframe");
		$("#listPreviewframe").load(function () {
			thisInstance.frameProgress.progressIndicator({mode: "hide"});
			iframe.height($(this).contents().find(".bodyContents").height() - 20);
		});
		$(".listViewEntriesTable .listViewEntries").first().trigger("click");
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
	updateListPreviewSize: function (currentElement) {
		var fixedList = $('.fixedListInitial, .fixedListContent');
		var vtFooter = $('.vtFooter').height();
		if ($(window).width() > 993) {
			var height = $(window).height() - (vtFooter + currentElement.offset().top + 2);
			fixedList.css('max-height', height);
		}
	},
	registerListPreviewScroll: function (container) {
		var thisInstance = this;
		var currentElement = $('.fixedListInitial');
		var listPreview = container.find('#listPreview');
		$(window).resize(function () {
			thisInstance.updateListPreviewSize(currentElement);
		});
		var commactHeight = $('.commonActionsContainer').height();
		$('.mainBody').scroll(function () {
			if ($(this).scrollTop() >= (currentElement.offset().top + commactHeight)) {
				currentElement.addClass('fixedListOnScroll');
				if ($(window).width() > 1092) {
					var gutter = container.find('.gutter');
					gutter.addClass('gutterOnScroll');
					gutter.css('left', listPreview.offset().left - 6);
					gutter.on('mousedown', function () {
						$(this).on('mousemove', function (e) {
							$(this).css('left', listPreview.offset().left - 6);
						})
					})
				}
			} else {
				currentElement.removeClass('fixedListOnScroll');
				if ($(window).width() > 1092) {
					var gutter = container.find('.gutter');
					gutter.removeClass('gutterOnScroll');
					gutter.css('left', 0);
					gutter.off();
				}
			}
			thisInstance.updateListPreviewSize(currentElement);
		});
		thisInstance.updateListPreviewSize(currentElement);
	},
	registerEvents: function () {
		var listViewContainer = this.getListViewContentContainer();
		this._super();
		this.registerPreviewEvent();
		this.registerSplit(listViewContainer, '.fixedListInitial', '#listPreview');
		this.registerListPreviewScroll(listViewContainer);
	},
});
