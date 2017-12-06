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
		$('#page').find('.widget_contents').on('Vtiger.Widget.FinishLoad', function (e, widgetName) {
			console.log(101);
			if (iframe.contents().find('#listPreviewframe').length) {
				var inif = iframe.contents().find('#listPreviewframe');
				var inbodyCon = inif.contents().find('.contentsDiv').height();
				ifrheight = inbodyCon + inif.offset().top + iframe.offset().top + 33
				iframe.height(ifrheight);
			} else {
				ifrheight = bodyContents.height() + iframe.offset().top + 33;
				iframe.height(ifrheight);
			}
		});
		$('#page').on('DetailView.Tab.FinishLoad', function (e, data) {
			console.log(222);
			if (iframe.contents().find('#listPreviewframe').length) {
				var inif = iframe.contents().find('#listPreviewframe');
				var inbodyCon = inif.contents().find('.contentsDiv').height();
				ifrheight = inbodyCon + inif.offset().top + iframe.offset().top + 33;
				iframe.height(ifrheight);
			} else {
				ifrheight = bodyContents.height() + iframe.offset().top + 33;
				iframe.height(ifrheight);
			}
		});
		$('body').on('LoadRelatedRecordList.PostLoad', function (e, data) {
			$('.blockHeader').on('click', function(){
				console.log($(this).next('.hide').length === 0);
				if ($(this).next('.hide').length === 0) {
					//ifrheight = ifrheight + ($(this).next().height());
					iframe.height(ifrheight + ($(this).next().height()));
					console.log($(this).next().height());
					console.log(iframe.height());
				} else {
					iframe.height(ifrheight);
					console.log('else');
					console.log(iframe.height());
				}
			});
		});
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
		iframe.height(bodyCon.height() + iframe.offset().top + 33);
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
