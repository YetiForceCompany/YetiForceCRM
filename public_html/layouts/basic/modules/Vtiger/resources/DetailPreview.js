/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
Vtiger_Detail_Js("Vtiger_DetailPreview_Js", {}, {
	/**
	 * Redirects to the clicked link from the iframe.
	 */
	registerLinkEvent: function () {
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
	/**
	 * Redirects to the current iframe parent.
	 */
	updateParentFrame: function () {
		parent.app.getPageController().updateWindowHeight($(".mainContainer").height(), $(window.frameElement));
	},
	/**
	 * Function sets the correct iframe size.
	 * @param {jQuery} currentHeight - ifrmae body height to be set.
	 * @param {jQuery} frame - ifrmae height to be changed.
	 */
	updateWindowHeight: function (currentHeight, frame) {
		var thisInstance = this;
		var relatedContents = frame.closest('.relatedContents');
		var fixedListHeight = relatedContents.find(".js-list-preview--scroll").height();
		frame.height(currentHeight);
		if (fixedListHeight > currentHeight) {
			currentHeight = fixedListHeight;
		}
		if ($(window).width() < 993) {
			relatedContents.find(".gutter, .js-list-preview, .js-side-block, .js-list-detail, .recordsListPreview").height(currentHeight);

		} else {
			relatedContents.find(".gutter, .js-list-preview, .js-side-block, .js-list-detail, .recordsListPreview").height(currentHeight);
		}
		relatedContents.find(".gutter, .js-list-preview, .js-side-block, .js-list-detail, .recordsListPreview").height(currentHeight);

		if (window.frameElement) {
			thisInstance.updateParentFrame();
		}
	},
	/**
	 * Creates ResizeSensor, which detects size changes.
	 */
	registerSizeEvent: function () {
		var thisInstance = this;
		new ResizeSensor($('.mainContainer'), function () {
			thisInstance.updateParentFrame();
		});
	},
	/**
	 * Registers DetailPreview events.
	 */
	registerEvents: function () {
		this._super();
		this.registerLinkEvent();
		this.registerSizeEvent();
	}
});
