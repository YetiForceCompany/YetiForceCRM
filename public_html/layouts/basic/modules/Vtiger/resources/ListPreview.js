/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_List_Js(
	'Vtiger_ListPreview_Js',
	{},
	{
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
				defaultView =
					defaultView + '&mode=showDetailViewByMode&requestMode=' + app.getMainParams('defaultDetailViewName'); // full, summary
			}
			frame.attr('src', url.replace('view=Detail', 'view=DetailPreview') + defaultView);
		},
		/**
		 * Registers click events.
		 */
		registerRowClickEvent: function () {
			var thisInstance = this;
			var listViewContentDiv = this.getListViewContentContainer();
			listViewContentDiv.on('click', '.listViewEntries', function (e) {
				if ($(e.target).hasClass('js-no-link')) return;
				if ($(e.target).closest('div').hasClass('actions')) return;
				if ($(e.target).is('button') || $(e.target).parent().is('button')) return;
				if ($(e.target).closest('a').hasClass('noLinkBtn')) return;
				if ($(e.target).is('a')) return;
				if ($(e.target, $(e.currentTarget)).is('td:first-child')) return;
				if ($(e.target).is('input[type="checkbox"]')) return;
				if ($.contains($(e.currentTarget).find('td:last-child').get(0), e.target)) return;
				if ($.contains($(e.currentTarget).find('td:first-child').get(0), e.target)) return;
				var elem = $(e.currentTarget);
				var recordUrl = elem.data('recordurl');
				if (typeof recordUrl === 'undefined') {
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
		registerListEvents: function () {
			var mainBody = this.container.closest('.mainBody');
			app.showNewScrollbarTopBottomRight(this.list, { wheelPropagation: false });
			this.registerFixedThead();
			this.registerScrollEvent(mainBody);
			this.registerResizeEvent();
			this.list.on('click', '.listViewEntries', () => {
				if (this.split.getSizes()[1] < 10) {
					const defaultGutterPosition = this.getDefaultSplitSizes();
					this.split.setSizes(defaultGutterPosition);
					this.preview.show();
					this.sideBlockRight.removeClass('d-block');
					app.moduleCacheSet('userSplitSet', defaultGutterPosition);
				}
			});
		},
		registerScrollEvent(mainBody) {
			let scrollContainer = App.Components.Scrollbar.page.element;
			scrollContainer.scrollTop(0); // reset scroll to set correct start position
			let listOffsetTop = this.list.offset().top - this.headerH;
			let initialH = this.sideBlocks.height();
			let mainViewPortHeightCss = { height: mainBody.height() };
			let mainViewPortWidthCss = { width: mainBody.height() };
			this.gutter.addClass('js-fixed-scroll');
			let fixedElements = this.container.find('.js-fixed-scroll');
			let fixedThead = this.list.siblings('.floatThead-container');
			const onScroll = () => {
				if (scrollContainer.scrollTop() >= listOffsetTop) {
					fixedThead.add(fixedElements).css({ top: scrollContainer.scrollTop() - listOffsetTop });
					fixedElements.css(mainViewPortHeightCss);
					this.rotatedText.css(mainViewPortHeightCss);
					this.rotatedText.css(mainViewPortWidthCss);
				} else {
					fixedThead.add(fixedElements).css({ top: 'initial' });
					fixedElements.css({ height: initialH + scrollContainer.scrollTop() });
					this.rotatedText.css({
						width: initialH + scrollContainer.scrollTop(),
						height: initialH + scrollContainer.scrollTop()
					});
				}
			};
			scrollContainer.on('scroll', onScroll);
		},
		registerResizeEvent() {
			$(window).on('resize', () => {
				if (
					App.Components.Scrollbar.page.element.scrollTop() >=
					this.list.offset().top + $('.commonActionsContainer').height()
				) {
					this.container.find('.gutter').css('left', this.preview.offset().left - 8);
				}
			});
		},
		registerFixedThead() {
			let list = this.list;
			this.listFloatThead = list.find('.js-fixed-thead');
			this.listFloatThead.floatThead('destroy');
			this.listFloatThead.floatThead({
				scrollContainer: function () {
					return list;
				}
			});
			this.listFloatThead.floatThead('reflow');
		},
		getListColumnWidth: function () {
			let width = 300;
			let column = this.container.find('.listViewEntriesDiv .listViewHeaders th:eq(1)');
			if (column.length) {
				width = column.offset().left + column.width();
			}
			return width;
		},
		setDomParams: function (container) {
			this.container = container;
			this.listColumnWidth = this.getListColumnWidth();
			this.windowW = $(window).width();
			this.windowMinWidth = (15 / this.windowW) * 100;
			this.windowMaxWidth = 100 - this.minWidth;
			this.sideBlocks = container.find('.js-side-block');
			this.sideBlockLeft = this.sideBlocks.first();
			this.sideBlockRight = this.sideBlocks.last();
			this.list = container.find('.js-list-preview');
			this.preview = container.find('.js-detail-preview');
			this.rotatedText = container.find('.u-rotate-90');
			this.footerH = $('.js-footer').outerHeight();
			this.headerH = $('.js-header').outerHeight();
		},
		getDefaultSplitSizes: function () {
			let thWidth = (this.listColumnWidth / this.windowW) * 100;
			return [thWidth, 100 - thWidth];
		},
		/**
		 * Sets default windows size or from cache
		 * @param {jQuery} container - current container for reference.
		 * @return Array
		 */
		getSplitSizes: function () {
			const cachedParams = app.moduleCacheGet('userSplitSet');
			if (cachedParams !== undefined) {
				return cachedParams;
			} else {
				return this.getDefaultSplitSizes();
			}
		},
		/**
		 * Registers split's events.
		 * @param {jQuery} container - current container for reference.
		 * @param {Split} split - a split object.
		 */
		registerSplitEvents: function (container, split) {
			var rightSplitMaxWidth = (400 / this.windowW) * 100;
			var minWindowWidth = (25 / this.windowW) * 100;
			var maxWindowWidth = 100 - minWindowWidth;
			var listPreview = container.find('.js-detail-preview');
			this.gutter.on('dblclick', () => {
				let gutterMidPosition = app.moduleCacheGet('gutterMidPosition');
				if (isNaN(this.split.getSizes()[0])) {
					this.split.setSizes(gutterMidPosition);
				}
				if (split.getSizes()[0] < 10) {
					this.sideBlockLeft.removeClass('d-block');
					this.list.removeClass('u-hide-underneath');
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
					this.sideBlockRight.removeClass('d-block');
					listPreview.show();
					this.gutter.css('right', 'initial');
				} else if (split.getSizes()[0] > 10 && split.getSizes()[0] < 50) {
					split.setSizes([minWindowWidth, maxWindowWidth]);
					this.list.addClass('u-hide-underneath');
					this.sideBlockLeft.addClass('d-block');
				} else if (split.getSizes()[1] > 10 && split.getSizes()[1] < 50) {
					split.collapse(1);
					this.sideBlockRight.addClass('d-block');
					listPreview.hide();
					this.list.width(this.list.width() - 10);
				}
				this.listFloatThead.floatThead('reflow');
				app.moduleCacheSet('userSplitSet', split.getSizes());
			});
			this.sideBlockLeft.on('click', () => {
				let gutterMidPosition = app.moduleCacheGet('gutterMidPosition');
				if (gutterMidPosition[0] > 11) {
					split.setSizes(gutterMidPosition);
				} else {
					split.setSizes(this.getDefaultSplitSizes());
				}
				this.sideBlockLeft.removeClass('d-block');
				this.list.removeClass('u-hide-underneath');
				this.listFloatThead.floatThead('reflow');
				app.moduleCacheSet('userSplitSet', split.getSizes());
			});
			this.sideBlockRight.on('click', () => {
				let gutterMidPosition = app.moduleCacheGet('gutterMidPosition');
				if (gutterMidPosition[1] > rightSplitMaxWidth + 1) {
					split.setSizes(gutterMidPosition);
				} else {
					split.setSizes(this.getDefaultSplitSizes());
				}
				this.sideBlockRight.removeClass('d-block');
				listPreview.show();
				this.gutter.css('right', 'initial');
				this.listFloatThead.floatThead('reflow');
				app.moduleCacheSet('userSplitSet', split.getSizes());
			});
		},
		/**
		 * Registers split object and executes its events listeners.
		 * @param {jQuery} container - current container for reference.
		 * @returns {Split} A split object.
		 */
		registerSplit: function (container) {
			var rightSplitMaxWidth = (400 / this.windowW) * 100;
			var splitMinWidth = (25 / this.windowW) * 100;
			var splitMaxWidth = 100 - splitMinWidth;
			var listPreview = container.find('.js-detail-preview');
			const splitSizes = this.getSplitSizes();
			var split = Split([this.list[0], listPreview[0]], {
				sizes: splitSizes,
				minSize: 10,
				gutterSize: 24,
				snapOffset: 100,
				onDrag: () => {
					if (split.getSizes()[1] < rightSplitMaxWidth) {
						split.collapse(1);
					}
					if (split.getSizes()[0] < 5) {
						this.sideBlockLeft.addClass('d-block');
						this.list.addClass('u-hide-underneath');
					} else {
						this.sideBlockLeft.removeClass('d-block');
						this.list.removeClass('u-hide-underneath');
					}
					if (split.getSizes()[1] < 10) {
						this.sideBlockRight.addClass('d-block');
						listPreview.hide();
						this.list.width(this.list.width() - 10);
					} else {
						this.sideBlockRight.removeClass('d-block');
						listPreview.show();
					}
					if (split.getSizes()[0] > 10 && split.getSizes()[1] > rightSplitMaxWidth) {
						this.listFloatThead.floatThead('reflow');
						app.moduleCacheSet('gutterMidPosition', split.getSizes());
					}
					app.moduleCacheSet('userSplitSet', split.getSizes());
				},
				onDragStart: () => {
					listPreview.css('z-index', '1001');
					this.gutter.css('z-index', '1001');
				},
				onDragEnd: () => {
					listPreview.css('z-index', '0');
					this.gutter.css('z-index', '0');
					this.listFloatThead.floatThead('reflow');
				}
			});
			if (splitSizes[0] < 10) {
				listPreview.width(listPreview.width() - 150);
				this.sideBlockLeft.addClass('d-block');
				split.setSizes([splitMinWidth, splitMaxWidth]);
				this.list.addClass('u-hide-underneath');
			} else if (splitSizes[1] < rightSplitMaxWidth) {
				this.sideBlockRight.addClass('d-block');
				listPreview.hide();
				split.setSizes([splitMaxWidth, splitMinWidth]);
			}
			this.gutter = container.find('.gutter');
			var mainWindowHeightCss = {
				height: $(window).height() - (this.gutter.offset().top + this.footerH)
			};
			this.gutter.css(mainWindowHeightCss);
			this.list.css(mainWindowHeightCss);
			this.sideBlocks.css(mainWindowHeightCss);
			this.registerSplitEvents(container, split);
			this.rotatedText.first().find('.js-list-name').append($('.breadcrumbsContainer .js-text-content').text());
			this.rotatedText.css({
				width: this.sideBlockLeft.height(),
				height: this.sideBlockLeft.height()
			});
			return split;
		},
		/**
		 * Adds the split and deletes it on resize.
		 * @param {jQuery} container - current container for reference.
		 */
		toggleSplit: function (container) {
			var thisInstance = this;
			var listPreview = container.find('.js-detail-preview');
			var splitsArray = [];
			var mainBody = container.closest('.mainBody');
			if (this.windowW > 993 && !container.find('.gutter').length) {
				this.split = thisInstance.registerSplit(container);
				splitsArray.push(this.split);
			}
			$(window).on('resize', () => {
				if (this.windowW < 993) {
					if (container.find('.gutter').length) {
						splitsArray[splitsArray.length - 1].destroy();
						this.sideBlockRight.removeClass('d-block');
						this.sideBlockLeft.removeClass('d-block');
					}
				} else {
					if (container.find('.gutter').length !== 1) {
						this.split = thisInstance.registerSplit(container);

						this.gutter = container.find('.gutter');
						this.gutter.addClass('js-fixed-scroll');
						if (mainBody.scrollTop() >= this.list.offset().top) {
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
					if (typeof currentSplit === 'undefined') return;
					if (currentSplit.getSizes()[0] < this.windowMinWidth + 5) {
						currentSplit.setSizes([this.windowMinWidth, this.windowMaxWidth]);
					} else if (currentSplit.getSizes()[1] < this.windowMinWidth + 5) {
						currentSplit.setSizes([this.windowMaxWidth, this.windowMinWidth]);
					}
				}
			});
		},
		/**
		 * @inheritDoc
		 */
		registerDesktopEvents() {},
		/**
		 * Sets initial iframe's height and fills the preview with first record's content.
		 */
		registerPreviewEvent: function () {
			const iframe = $('.listPreviewframe');
			const container = this.getListViewContentContainer();
			this.setDomParams(container);
			this.toggleSplit(container);
			if (this.windowW > 993) {
				this.registerListEvents(container);
			}
			iframe.on('load', () => {
				this.frameProgress.progressIndicator({ mode: 'hide' });
				iframe.height(iframe.contents().find('.bodyContents').height() - 20);
			});
			$('.listViewEntriesTable .listViewEntries').first().trigger('click');
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
	}
);
