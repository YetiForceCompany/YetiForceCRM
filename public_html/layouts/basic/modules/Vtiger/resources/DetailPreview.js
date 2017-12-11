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
	registerSizeEvent: function (container) {
		console.log('registerSizeEvent');
		var iframe = $(top.document).find('#listPreviewframe');
		var bodyContents = $('.mainContainer');
		var bodyCon = $('.bodyContents');
		console.log();
		var listIframe = iframe.contents().find('.panel-body #listPreviewframe');
		var inifr = iframe.contents().find('#listPreviewframe');
		if(listIframe.length) {
			console.log('oki');
			listIframe.height($('.detailViewContainer').height());
			listIframe.closest('#recordsListPreview').height($('.detailViewContainer').height());
			console.log(listIframe.closest('#recordsListPreview'));
		}

		$('#page').find('.widget_contents').on('Vtiger.Widget.FinishLoad', function (e, widgetName) {
				iframe.height(bodyContents.height());
		});
		$('#page').on('DetailView.Tab.FinishLoad', function (e, data) {
			console.log('DetailView.Tab.FinishLoad');	
			
			if (iframe.contents().find('#listPreviewframe').length) {		
				console.log('if');			
				//var inBodyConHeight = $('#listPreviewframe').contents().find('.bodyContents').height();	
				var ifrTop = $(top.top.document).find('#listPreviewframe');
				var ifrTopBodyCon = $(top.top.document).find('#listPreviewframe').contents().find('.mainContainer');
				console.log(ifrTopBodyCon);
				console.log(bodyContents);
				var inifr = iframe.contents().find('#listPreviewframe');
				inifr.height($('.detailViewContainer').height());
				iframe.height(inifr.height() + inifr.offset().top + 10);
	
				var inif = $('#listPreviewframe');	
				$('#listPreviewframe').load(function () {	
					inif.height(inif.contents().find('.detailViewContainer').height());
					console.log(inif.height());		
					iframe.height(inif.height() + inif.offset().top + 15);	
				});	
				$('#page').find('.widget_contents').on('Vtiger.Widget.FinishLoad', function (e, widgetName) {
					inif.height(bodyContents.height());
					console.log('widgetin');
				});
			} 
			else {
				iframe.height(bodyContents.height() - 10);
				console.log('else');
				$('#page').find('.widget_contents').on('Vtiger.Widget.FinishLoad', function (e, widgetName) {
					iframe.height(bodyContents.height() - 10);
					console.log('widget');
				});
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
