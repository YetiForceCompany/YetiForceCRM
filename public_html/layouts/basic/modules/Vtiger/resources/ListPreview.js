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
		var fixedList = container.find('.fixedListInitial');
		var listPreview = container.find('#listPreview');
		var mainBody = $('.mainBody');
		var listWidth = container.find('.fixedListInitial');
		$(window).resize(function () {
			thisInstance.updateListPreviewSize(fixedList);
			if (mainBody.scrollTop() >= (fixedList.offset().top + commactHeight)) {
				container.find('.gutter').css('left', listPreview.offset().left - 8);
			}
		});
		var commactHeight = $('.commonActionsContainer').height();
		var paddingTop = 6;
		var offset = 46 - paddingTop + commactHeight;
		mainBody.scroll(function () {
			window.console.log(fixedList.offset().top);
			if ($(this).scrollTop() >= (fixedList.offset().top + commactHeight - paddingTop)) {
				fixedList.css('top', $(this).scrollTop() - offset);
				if ($(window).width() > 993) {
					var gutter = container.find('.gutter');
					gutter.addClass('gutterOnScroll');
					gutter.css('left', listPreview.offset().left - 8);
					gutter.on('mousedown', function () {
						$(this).on('mousemove', function (e) {
							$(this).css('left', listPreview.offset().left - 8);
						})
					})
				}
			} else {
				fixedList.css('top', 'initial');
				if ($(window).width() > 993) {
					var gutter = container.find('.gutter');
					gutter.removeClass('gutterOnScroll');
					gutter.css('left', 0);
					gutter.off();
				}
			}
			thisInstance.updateListPreviewSize(fixedList);
		});
		thisInstance.updateListPreviewSize(fixedList);
	},
	registerSplit: function (container, leftCon, rightCon) {
		var fixedList = container.find('.fixedListInitial');
		var commactHeight = $('.commonActionsContainer').height();
		var listPreview = container.find('#listPreview');
		var splitsArray = [];
		var mainBody = $('.mainBody');
		if ($(window).width() > 993) {
			var split = Split([leftCon, rightCon], {
				sizes: [25, 75],
				minSize: 10,
				gutterSize: 8
			});
		}
		splitsArray.push(split);
		$(window).resize(function () {
			if ($(window).width() < 993) {
				if (container.find('.gutter').length) {
					splitsArray[splitsArray.length - 1].destroy();
				}
			} else {
				if (container.find('.gutter').length !== 1) {
					var split = Split([leftCon, rightCon], {
						sizes: [25, 75],
						minSize: 10,
						gutterSize: 8
					});
					if (mainBody.scrollTop() >= (fixedList.offset().top + commactHeight)) {
						var gutter = container.find('.gutter');
						gutter.addClass('gutterOnScroll');
						gutter.css('left', listPreview.offset().left - 8);
						gutter.on('mousedown', function () {
							$(this).on('mousemove', function (e) {
								$(this).css('left', listPreview.offset().left - 8);
							})
						})
					}
					splitsArray.push(split);
				}
			}
		});
	},
	registerEvents: function () {
		var listViewContainer = this.getListViewContentContainer();
		this._super();
		this.registerPreviewEvent();
		this.registerSplit(listViewContainer, '.fixedListInitial', '#listPreview');
		this.registerListPreviewScroll(listViewContainer);
	},
});
