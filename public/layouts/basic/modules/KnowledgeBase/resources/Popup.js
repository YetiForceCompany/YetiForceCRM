/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Vtiger_Popup_Js("KnowledgeBase_Popup_Js", {}, {
	showPresentationContent : function(recordId) {
		var url = 'index.php?module=KnowledgeBase&view=FullScreen&record=' + recordId;
		var screenWidth = screen.width;
		var screenHeight = screen.height;
		var popupWinRef =  window.open(url, '' ,'width=' + screenWidth + ',height=' + screenHeight);
		popupWinRef.moveTo(0,0);
		if (typeof this.destroy == 'function') {
			// To remove form elements that have created earlier
			this.destroy();
		}
		jQuery.initWindowMsg();
		return popupWinRef;
	},
	setSlidesHeight : function () {
		$(function () {
			var highestSlideHeight = $('#page').height();
			$('#carouselPresentation .knowledgePresentationContent').each(function () {
				$(this).css('height', highestSlideHeight + 'px');
			});
		});
	},
	registerEvents : function () {
		if ($('#popupValue').val() == 1) {
			this.setSlidesHeight();
		}
	}
});

