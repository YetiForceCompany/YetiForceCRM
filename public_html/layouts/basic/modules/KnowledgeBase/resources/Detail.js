/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Vtiger_Detail_Js("KnowledgeBase_Detail_Js", {
	showPresentation: function () {
		let url = 'index.php?module=KnowledgeBase&view=FullScreen&record=' + app.getRecordId();
		let features = "width=" + screen.width + ",height=" + screen.height + ",toolbar=0,location=0, directories=0, status=0,location=no,menubar=0";
		let popup = window.open(url, '', features);
		popup.moveTo(0, 0);
	}
}, {
	/**
	 * Sets all presentation slides height equal to the biggest one
	 * @param string id Selector of carousel
	 * @returns true
	 */
	setSlideHeight: function (id) {
		var slides = [];

		$(id + ' .item').each(function () {
			slides.push($(this).height());
		});

		var highestSlideHeight = Math.max.apply(null, slides);

		$(id + ' .knowledgePresentationContent').each(function () {
			$(this).css('height', highestSlideHeight + 'px');
		});

		return true;
	},
	registerBasicEvents: function () {
		this._super();
		var tab = this.getSelectedTab();
		if (tab.data('reference') === 'Summary') {
			this.setSlideHeight('#carouselPresentation');
		}
	}
});
