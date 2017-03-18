/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Vtiger_Detail_Js("KnowledgeBase_Detail_Js", {}, {
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
	registerBasicEvents : function(){
		this._super();
		var tab = this.getSelectedTab();
		if (tab.data('reference') === 'Summary') {
			this.setSlideHeight('#carouselPresentation');
		}
	}
});
