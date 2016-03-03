/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Vtiger_Popup_Js("KnowledgeBase_Popup_Js", {}, {
	test : true,
	showPresentationContent : function(recordId) {
		var url = 'index.php?module=KnowledgeBase&view=Popup&record=' + recordId;
		var sW = screen.width;
		var sH = screen.height;
		var popupWinRef =  window.open(url, '' ,'width=' + sW + ',height=' + sH);
		popupWinRef.moveTo(0,0);
		if (typeof this.destroy == 'function') {
			// To remove form elements that have created earlier
			this.destroy();
		}
		jQuery.initWindowMsg();
		return popupWinRef;
	}
});
$(function () {
	var slides = [];
	var highestSlideHeight = $('#page').height();
	$('#carouselPresentation .knowledgePresentationContent').each(function () {
		$(this).css('height', highestSlideHeight + 'px');
	});
});
