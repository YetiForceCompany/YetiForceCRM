/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
Vtiger_List_Js("Vtiger_ListPreview_Js", {}, {
	frameProgress: false,
	/**
	 * Sets correct page url.
	 * @param {string} url - current url.
	 */
	updatePreview: function (url) {
		var frame = $('.listPreviewframe');
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
	/**
	 * Registers click events.
	 */
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
			$('.listViewEntriesTable .listViewEntries').removeClass('active');
			$(this).addClass('active');
			thisInstance.updatePreview(recordUrl);
		});
	},
	/**
	 * Registers list events.
	 * @param {jQuery} container - current container for reference.
	 */
	registerListEvents: function (container) {
		var listPreview = container.find('.listPreview');
		var mainBody = container.closest('.mainBody');
		var commActHeight = $('.commonActionsContainer').height();
		var paddingTop = 6;
		var offset = this.list.offset().top - commActHeight - paddingTop;
		app.showNewBottomTopScrollbar(container.find('.fixedListContent'));
		app.showNewLeftScrollbar(this.list);
		$(window).on('resize', () = > {
			if(mainBody.scrollTop() >= (this.list.offset().top + commActHeight)
	)
		{
			container.find('.gutter').css('left', listPreview.offset().left - 8);
		}
	})
		;
		mainBody.on('scroll', () = > {
			var gutter = container.find('.gutter');
		var mainWindowHeightCss = {height: $(window).height() - ((gutter.offset().top + 33) + $('.infoUser').height())};
		gutter.css(mainWindowHeightCss);
		this.list.css(mainWindowHeightCss);
		this.sidePanels.css(mainWindowHeightCss);
		if (mainBody.scrollTop() >= (this.list.offset().top + commActHeight - paddingTop)) {
			if (listPreview.height() + listPreview.offset().top + 33 > $(window).height()) {
				this.list.css('top', mainBody.scrollTop() - offset);
				if ($(window).width() > 993) {
					this.sidePanels.addClass('wrappedPanelOnScroll');
					gutter.addClass('gutterOnScroll');
					gutter.css('left', listPreview.offset().left - 8);
					gutter.on('mousedown', function () {
						$(this).on('mousemove', function (e) {
							$(this).css('left', listPreview.offset().left - 8);
						});
					});
				}
			}
		} else {
			this.list.css('top', 'initial');
			if ($(window).width() > 993) {
				var gutter = container.find('.gutter');
				this.sidePanels.removeClass('wrappedPanelOnScroll');
				gutter.removeClass('gutterOnScroll');
				gutter.css('left', 0);
				gutter.off('mousedown');
				gutter.off('mousemove');
			}
		}
	})
		;
		this.list.on('click', '.listViewEntries', () = > {
			if(this.split.getSizes()[1] < 10
	)
		{
			const defaultGutterPosition = this.getDefaultSplitSizes();
			this.split.setSizes(defaultGutterPosition);
			listPreview.show();
			this.sidePanelRight.removeClass('wrappedPanelRight');
			app.moduleCacheSet('userSplitSet', defaultGutterPosition);
		}
	})
		;
	},
	getSecondColMinWidth(container) {
		let maxWidth, thisWidth;
		container.find('.listViewEntries').each(function (i) {
			thisWidth = $(this).find('.listViewEntryValue a').first().width();
			if (i === 0) {
				maxWidth = thisWidth;
			} else {
				thisWidth > maxWidth ? maxWidth = thisWidth : maxWidth;
			}
		});
		return maxWidth;
	},
	getDomParams(container) {
		this.listColumntFirstWidth = container.find('.listViewEntriesDiv .listViewHeaders th').first().width();
		this.listColumntSecondWidth = this.getSecondColMinWidth(container);
		this.windowMinWidth = (15 / $(window).width()) * 100;
		this.windowMaxWidth = 100 - this.minWidth;
		this.sidePanels = container.find('.wrappedPanel');
		this.sidePanelLeft = container.find(this.sidePanels[0]);
		this.sidePanelRight = container.find(this.sidePanels[1]);
		this.list = container.find('.fixedListInitial');
	},
	getDefaultSplitSizes() {
		let thWidth = ((this.listColumntFirstWidth + this.listColumntSecondWidth + 62) / $(window).width()) * 100;
		return [thWidth, 100 - thWidth];
	},
	/**
	 * Sets default windows size or from cache
	 * @param {jQuery} container - current container for reference.
	 * @return Array
	 */
	getSplitSizes() {
		const cachedParams = app.moduleCacheGet('userSplitSet');
		if (cachedParams !== null) {
			return cachedParams;
		} else {
			this.getDefaultSplitSizes();
		}
	},
	/**
	 * Registers split's events.
	 * @param {jQuery} container - current container for reference.
	 * @param {Split} split - a split object.
	 */
	registerSplitEvents: function (container, split) {
		var rightSplitMaxWidth = (400 / $(window).width()) * 100;
		var gutter = container.find('.gutter');
		var minWindowWidth = (15 / $(window).width()) * 100;
		var maxWindowWidth = 100 - minWindowWidth;
		var listPreview = container.find('.listPreview');
		gutter.on("dblclick", () = > {
			let gutterMidPosition = app.moduleCacheGet('gutterMidPosition');
		if (split.getSizes()[0] < 10) {
			this.sidePanelLeft.removeClass('wrappedPanelLeft');
			if (gutterMidPosition[0] > 11) {
				split.setSizes(gutterMidPosition);
			} else {
				split.setSizes(this.getDefaultSplitSizes());
			}
		} else if (split.getSizes()[1] < 20) {
			if (gutterMidPosition[1] > rightSplitMaxWidth + 1) {
				split.setSizes(gutterMidPosition);
			} else {
				split.setSizes(this.getDefaultSplitSizes());
			}
			this.sidePanelRight.removeClass('wrappedPanelRight');
			listPreview.show();
			gutter.css('right', 'initial');
			this.list.css('padding-right', '10px');
		} else if (split.getSizes()[0] > 10 && split.getSizes()[0] < 50) {
			split.setSizes([minWindowWidth, maxWindowWidth]);
			this.sidePanelLeft.addClass('wrappedPanelLeft');
		} else if (split.getSizes()[1] > 10 && split.getSizes()[1] < 50) {
			split.collapse(1);
			this.sidePanelRight.addClass('wrappedPanelRight');
			listPreview.hide();
			this.list.width(this.list.width() - 10);
		}
		app.moduleCacheSet('userSplitSet', split.getSizes());
	})
		;
		this.sidePanelLeft.on("dblclick", () = > {
			let gutterMidPosition = app.moduleCacheGet('gutterMidPosition');
		if (gutterMidPosition[0] > 11) {
			split.setSizes(gutterMidPosition);
		} else {
			split.setSizes(this.getDefaultSplitSizes());
		}
		this.sidePanelLeft.removeClass('wrappedPanelLeft');
		app.moduleCacheSet('userSplitSet', split.getSizes());
	})
		;
		this.sidePanelRight.on("dblclick", () = > {
			let gutterMidPosition = app.moduleCacheGet('gutterMidPosition');
		if (gutterMidPosition[1] > rightSplitMaxWidth + 1) {
			split.setSizes(gutterMidPosition);
		} else {
			split.setSizes(this.getDefaultSplitSizes());
		}
		this.sidePanelRight.removeClass('wrappedPanelRight');
		listPreview.show();
		gutter.css('right', 'initial');
		this.list.css('padding-right', '10px');
		app.moduleCacheSet('userSplitSet', split.getSizes());
	})
		;
	},
	/**
	 * Registers split object and executes its events listeners.
	 * @param {jQuery} container - current container for reference.
	 * @returns {Split} A split object.
	 */
	registerSplit: function (container) {
		var rightSplitMaxWidth = (400 / $(window).width()) * 100;
		var thWidth = container.find('.listViewEntriesDiv .listViewHeaders th').first();
		thWidth = ((thWidth.width() + thWidth.next().width() + 62) / $(window).width()) * 100;
		var listPreview = container.find('.listPreview');
		const splitSizes = this.getSplitSizes();
		var split = Split([this.list[0], listPreview[0]], {
			sizes: splitSizes,
			minSize: 10,
			gutterSize: 8,
			snapOffset: 100,
			onDrag: () = > {
			if(split.getSizes()[1] < rightSplitMaxWidth)
		{
			split.collapse(1);
		}
		if (split.getSizes()[0] < 5) {
			this.sidePanelLeft.addClass('wrappedPanelLeft');
		} else {
			this.sidePanelLeft.removeClass('wrappedPanelLeft');
		}
		if (split.getSizes()[1] < 10) {
			this.sidePanelRight.addClass('wrappedPanelRight');
			listPreview.hide();
			this.list.width(this.list.width() - 10);
		} else {
			this.sidePanelRight.removeClass('wrappedPanelRight');
			listPreview.show();
		}
		if (split.getSizes()[0] > 10 && split.getSizes()[1] > rightSplitMaxWidth) {
			app.moduleCacheSet('gutterMidPosition', split.getSizes());
		}
		app.moduleCacheSet('userSplitSet', split.getSizes());
	}
	})
		;
		if (splitSizes[0] < 10) {
			this.sidePanelLeft.addClass('wrappedPanelLeft');
			split.setSizes([this.windowMinWidth, this.windowMaxWidth]);
		} else if (splitSizes[1] < rightSplitMaxWidth) {
			this.sidePanelRight.addClass('wrappedPanelRight');
			listPreview.hide();
			split.setSizes([this.windowMaxWidth, this.windowMinWidth]);
		}
		var gutter = container.find('.gutter');
		var mainWindowHeightCss = {height: $(window).height() - (gutter.offset().top + 33)};
		var rotatedText = container.find('.rotatedText');
		gutter.css(mainWindowHeightCss);
		this.list.css(mainWindowHeightCss)
		this.sidePanels.css(mainWindowHeightCss);
		this.registerSplitEvents(container, split);
		rotatedText.first().find('.textCenter').append($('.breadcrumbsContainer .separator').nextAll().text());
		rotatedText.css({
			width: this.sidePanelLeft.height(),
			height: this.sidePanelLeft.height()
		});
		return split;
	},
	/**
	 * Adds the split and deletes it on resize.
	 * @param {jQuery} container - current container for reference.
	 */
	toggleSplit: function (container) {
		var thisInstance = this;
		var listPreview = container.find('.listPreview');
		var splitsArray = [];
		var mainBody = container.closest('.mainBody');
		if ($(window).width() > 993 && !container.find('.gutter').length) {
			this.split = thisInstance.registerSplit(container);
			splitsArray.push(this.split);
		}
		$(window).on('resize', () = > {
			if($(window).width() < 993
	)
		{
			if (container.find('.gutter').length) {
				splitsArray[splitsArray.length - 1].destroy();
				this.sidePanelRight.removeClass('wrappedPanelRight');
				this.sidePanelLeft.removeClass('wrappedPanelLeft');
			}
		}
	else
		{
			if (container.find('.gutter').length !== 1) {
				this.split = thisInstance.registerSplit(container);
				var gutter = container.find('.gutter');
				if (mainBody.scrollTop() >= (this.list.offset().top)) {
					gutter.addClass('gutterOnScroll');
					gutter.css('left', listPreview.offset().left - 8);
					gutter.on('mousedown', function () {
						$(this).on('mousemove', function (e) {
							$(this).css('left', listPreview.offset().left - 8);
						});
					});
				}
				splitsArray.push(this.split);
			}
			var currentSplit = splitsArray[splitsArray.length - 1];
			if (typeof currentSplit === 'undefined')
				return;
			if (currentSplit.getSizes()[0] < this.windowMinWidth + 5) {
				currentSplit.setSizes([this.windowMinWidth, this.windowMaxWidth]);
			} else if (currentSplit.getSizes()[1] < this.windowMinWidth + 5) {
				currentSplit.setSizes([this.windowMaxWidth, this.windowMinWidth]);
			}
		}
	})
		;
	},
	/**
	 * Sets initial iframe's height and fills the preview with first record's content.
	 */
	registerPreviewEvent: function () {
		const thisInstance = this;
		const iframe = $(".listPreviewframe");
		iframe.on('load', () = > {
			const container = this.getListViewContentContainer();
		this.frameProgress.progressIndicator({mode: "hide"});
		iframe.height(iframe.contents().find(".bodyContents").height() - 20);
		this.getDomParams(container);
		this.toggleSplit(container);
		if ($(window).width() > 993) {
			this.registerListEvents(container);
		}
	})
		;
		$(".listViewEntriesTable .listViewEntries").first().trigger("click");
	},
	/**
	 * Sets the correct parent iframe's size.
	 * @param {jQuery} currentHeight - ifrmae's body height to be set.
	 * @param {jQuery} frame - ifrmae's height to be changed.
	 */
	updateWindowHeight: function (height, frame) {
		frame.height(height);
	},
	/**
	 * Executes event listener.
	 * @param {jQuery} container - current container for reference.
	 */
	postLoadListViewRecordsEvents: function (container) {
		this._super(container);
		this.registerPreviewEvent();
	},
	/**
	 * Registers ListPreview's events.
	 */
	registerEvents: function () {
		this._super();
		this.registerPreviewEvent();
	}
});
