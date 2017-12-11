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
 * Function changes iframes' size
 */
	registerSizeEvent: function (container) {
		var iframe = $(top.document).find('#listPreviewframe');
		var bodyContents = $('.mainContainer');
		var bodyCon = $('.bodyContents');
		var listIframe = iframe.contents().find('.panel-body #listPreviewframe');
		var inifr = iframe.contents().find('#listPreviewframe');
		//iframe from documents in records list - doesn't fit correct size and inner list doesn't enlarge outer iframe
		if(listIframe.length) {
			listIframe.height($('.detailViewContainer').height());
			iframe.height(iframe.contents().find('.detailViewContainer').height());
		}
		//widgets loader
		$('#page').find('.widget_contents').on('Vtiger.Widget.FinishLoad', function (e, widgetName) {
			iframe.height(bodyContents.height() - 10);
		});
		//tabs loader
		$('#page').on('DetailView.Tab.FinishLoad', function (e, data) {
			//check if tab has listpreview
			if (iframe.contents().find('#listPreviewframe').length) {		
				var inifr = iframe.contents().find('#listPreviewframe');
				inifr.height($('.detailViewContainer').height());
				iframe.height(inifr.height() + inifr.offset().top + 10);
				var currentIf = $('#listPreviewframe');	
				$('#listPreviewframe').load(function () {	
					currentIf.height(currentIf.contents().find('.detailViewContainer').height());
					iframe.height(currentIf.height() + currentIf.offset().top + 15);	
				});	
				$('#page').find('.widget_contents').on('Vtiger.Widget.FinishLoad', function (e, widgetName) {
					currentIf.height(bodyContents.height());
				});
			} 
			else {
				iframe.height(bodyContents.height() - 10);
				$('#page').find('.widget_contents').on('Vtiger.Widget.FinishLoad', function (e, widgetName) {
					iframe.height(bodyContents.height() - 10);
				});
//				code responsible for map size fitting, needs finishing
//				if ($('#detailView').has('#mapid').length === 1) {
//					iframe.height($(window).height());
//				}
			}
		});
	},
	registerEvents: function () {
		this._super();
		this.registerLinkEvent();
		this.registerSizeEvent();
	},
});
