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
	/**
	 * Function redirects to current iframe's parent
	 * 
	 */
	updateParentFrame: function () {
		parent.app.getPageController().updateWindowHeight($(".mainContainer").height(), $(window.frameElement));
	},
	/**
	 * Function sets correct size of iframes
	 * 
	 */
	updateWindowHeight: function (currentHeight, frame) {
		var thisInstance = this;
		var relatedContents = frame.closest('.relatedContents');
		frame.height(currentHeight);
		var fixedListHeight = relatedContents.find(".fixedListContent").height();
		if (fixedListHeight > currentHeight) {
			currentHeight = fixedListHeight;
		}
		relatedContents.find(".gutter,.wrappedPanel,.fixedListInitial,.listPreview,.recordsListPreview").height(currentHeight);
		if (window.frameElement) {
			thisInstance.updateParentFrame();
		}
	},
	/**
	 * Register events, which impact on iframes' size
	 * 
	 */
	registerSizeEvent: function () {
		var thisInstance = this;
		if (window.frameElement) {
			thisInstance.updateParentFrame();
		}
		app.event.on('RelatedList.AfterLoad', function () {
			thisInstance.updateParentFrame();
		});
		app.event.on("DetailView.BlockToggle.PostLoad", function () {
			thisInstance.updateParentFrame();
		});
		app.event.on('DetailView.Tab.AfterLoad', function () {
			thisInstance.updateParentFrame();
		});
		app.event.on("DetailView.SaveComment.AfterLoad DetailView.SaveComment.AfterUpdate", function () {
			thisInstance.updateParentFrame();
		});
		app.event.on("DetailView.Widget.AfterLoad DetailView.UpdatesWidget.AddMore", function () {
			thisInstance.updateParentFrame();
		});
	},
	registerEvents: function () {
		this._super();
		this.registerLinkEvent();
		this.registerSizeEvent();
	}
});
