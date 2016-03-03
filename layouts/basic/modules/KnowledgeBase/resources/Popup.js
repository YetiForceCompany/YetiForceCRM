/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Vtiger_Popup_Js("KnowledgeBase_Popup_Js", {}, {
	showPresentationContent : function(recordId) {
		var url = 'index.php?module=KnowledgeBase&view=Popup&record=' + recordId;
		var popupWinRef =  window.open(url, '' ,'width=900,height=650,resizable=0,scrollbars=1');
		if (typeof this.destroy == 'function') {
			// To remove form elements that have created earlier
			this.destroy();
		}
		jQuery.initWindowMsg();
		return popupWinRef;
	},
})
