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
		var ifrheight = iframe.height();
		var bodyContents = $('.mainContainer');
		var bodyCon = $('.bodyContents');
		iframe.height(ifrheight);
//		$('#page').find('.widget_contents').on('Vtiger.Widget.FinishLoad', function (e, widgetName) {
//			console.log('Vtiger.Widget.FinishLoad');
//			if (iframe.contents().find('#listPreviewframe').length) {
//				var inif = iframe.contents().find('#listPreviewframe');
//				var inbodyCon = inif.contents().find('.contentsDiv').height();
//				ifrheight = inbodyCon + inif.offset().top + iframe.offset().top + 33;
//				iframe.height(ifrheight);
//			} else {
//				ifrheight = bodyContents.height() + iframe.offset().top + 33;
//				iframe.height(ifrheight);
//			}
//		});
		$('#page').on('DetailView.Tab.FinishLoad', function (e, data) {
			console.log('DetailView.Tab.FinishLoad');
//			console.log(openBlocks);
//			console.log($('body'));
			if (iframe.contents().find('#listPreviewframe').length) {
				var openBlocks = 0;
//				$('.panel').each(function() {
//					openBlocks = openBlocks + $(this).height();
//					console.log($(this).height());
//					console.log($(this));
//				});			
				var inif = iframe.contents().find('#listPreviewframe');
				var inbodyCon = inif.contents().find('.detailViewContainer').height();
				ifrheight = inbodyCon + inif.offset().top + iframe.offset().top + openBlocks;
				iframe.height(ifrheight);
				console.log('if');
			} else {
				if ($('#detailView').has('#mapid').length === 1 || $('#detailView').has('.summaryView').length === 1) {
					if (bodyCon.height() === bodyContents.height()) {
						ifrheight = bodyCon.height() + 75; 
						iframe.height(ifrheight);
					} else {
						ifrheight = bodyCon.height(); 
						iframe.height(ifrheight);
					}
				} else {
					ifrheight = bodyContents.height(); 
					iframe.height(ifrheight);
				}
			}
		});
//		$('body').on('LoadRelatedRecordList.PostLoad', function (e, data) {
//			console.log($(this));
//			$('#detailView').on('click', '.blockHeader', function(){
//				console.log($(this).next('.hide').length === 0);
//				if ($(this).next('.hide').length === 0) {
//					//ifrheight = ifrheight + ($(this).next().height());
//
//				} else {
//					iframe.height(ifrheight);
//					console.log('else');
//					console.log(iframe.height());
//				}
//			});
//		});
//			fieldUpdatedEvent: 'Vtiger.Field.Updated',
//	widgetPostLoad: 'Vtiger.Widget.PostLoad',
//	//Filels list on updation of which we need to upate the detailview header
//	updatedFields: ['company', 'designation', 'title'],
//	//Event that will triggered before saving the ajax edit of fields
//	fieldPreSave: 'Vtiger.Field.PreSave',
//		$('body').on('LoadRelatedRecordList.PostLoad', function (e, data) {
//			console.log($('.blockContent.hide'));
//			$('.blockContent').css('background', 'red');
////			$('.blockHeader').on('click', function () {
////				console.log($(this).height());
////			});
//		});
		iframe.height(bodyCon.height() + 33);
	},
	registerEvents: function () {
		this._super();
		this.registerLinkEvent();
		this.registerSizeEvent($('#listPreviewframe'));
//		app.showScrollBar($("#page"), {
//			alwaysVisible: false,
//			size: '10px',
//			position: 'right',
//			height: $('.bodyContents').height()
//		});
	}
});
