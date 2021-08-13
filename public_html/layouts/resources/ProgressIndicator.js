/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

(function ($) {
	var ProgressIndicatorHelper = function () {
		var thisInstance = this;

		this.defaults = {
			position: 'append',
			mode: 'show',
			blockInfo: {
				elementToBlock: 'body'
			},
			message: ''
		};

		this.imageContainerCss = {
			'text-align': 'center'
		};

		this.blockOverlayCSS = {
			opacity: 0.8,
			'background-color': '#fff'
		};

		this.blockCss = {
			border: '',
			'background-color': '',
			'background-clip': 'border-box',
			'border-radius': '2px'
		};

		this.showTopCSS = {
			width: '25%',
			left: '37.5%',
			position: 'fixed',
			top: '4.5%',
			'z-index': '100000'
		};

		this.showOnTop = false;

		this.init = function (element, options = {}) {
			thisInstance.options = $.extend(true, this.defaults, options);
			thisInstance.blockOverlayCSS = Object.assign(
				thisInstance.blockOverlayCSS,
				options.blockOverlayCSS ? options.blockOverlayCSS : {}
			);
			thisInstance.container = element;
			thisInstance.position = options.position;
			if (typeof options.imageContainerCss !== 'undefined') {
				thisInstance.imageContainerCss = $.extend(true, this.imageContainerCss, options.imageContainerCss);
			}
			if (this.isBlockMode()) {
				thisInstance.elementToBlock = $(thisInstance.options.blockInfo.elementToBlock);
			}
			return this;
		};

		this.initActions = function () {
			if (this.options.mode == 'show') {
				this.show();
			} else if (this.options.mode == 'hide') {
				this.hide();
			}
		};

		this.isPageBlockMode = function () {
			if (typeof this.elementToBlock !== 'undefined' && this.elementToBlock.is('body')) {
				return true;
			}
			return false;
		};

		this.isBlockMode = function () {
			if (typeof this.options.blockInfo !== 'undefined' && this.options.blockInfo.enabled == true) {
				return true;
			}
			return false;
		};

		this.show = function () {
			var className = 'bigLoading';
			if (this.options.smallLoadingImage == true) {
				className = 'smallLoading';
			}
			if (this.isBlockMode()) {
				className = className + ' blockProgressContainer';
			}
			var imageHtml =
				'<div class="imageHolder ' +
				className +
				'">' +
				'<div class="sk-cube-grid"><div class="sk-cube sk-cube1"></div>' +
				'<div class="sk-cube sk-cube2"></div>' +
				'<div class="sk-cube sk-cube3"></div><div class="sk-cube sk-cube4"></div><div class="sk-cube sk-cube5"></div>' +
				'<div class="sk-cube sk-cube6"></div><div class="sk-cube sk-cube7"></div><div class="sk-cube sk-cube8"></div>' +
				'<div class="sk-cube sk-cube9"></div></div></div>';
			var jQImageHtml = jQuery(imageHtml).css(this.imageContainerCss);

			var jQMessage = thisInstance.options.message;
			if (jQMessage !== false) {
				if (jQMessage.length == 0) {
					jQMessage = app.vtranslate('JS_LOADING_PLEASE_WAIT');
				}
				if (!(jQMessage instanceof jQuery)) {
					jQMessage = jQuery('<span></span>').html(jQMessage);
				}
				var messageContainer = jQuery('<div class="message"></div>').append(jQMessage);
			}
			jQImageHtml.append(messageContainer);
			if (this.isBlockMode()) {
				jQImageHtml.addClass('blockMessageContainer');
			}

			switch (thisInstance.position) {
				case 'prepend':
					thisInstance.container.prepend(jQImageHtml);
					break;
				case 'html':
					thisInstance.container.html(jQImageHtml);
					break;
				case 'replace':
					thisInstance.container.replaceWith(jQImageHtml);
					break;
				default:
					thisInstance.container.append(jQImageHtml);
			}
			if (this.isBlockMode()) {
				thisInstance.blockedElement = thisInstance.elementToBlock;
				if (thisInstance.isPageBlockMode()) {
					$.blockUI({
						message: thisInstance.container,
						overlayCSS: thisInstance.blockOverlayCSS,
						css: thisInstance.blockCss,
						onBlock: thisInstance.options.blockInfo.onBlock
					});
				} else {
					thisInstance.elementToBlock.block({
						message: thisInstance.container,
						overlayCSS: thisInstance.blockOverlayCSS,
						css: thisInstance.blockCss
					});
				}
			}

			if (thisInstance.showOnTop) {
				this.container.css(this.showTopCSS).appendTo('body');
			}
		};

		this.hide = function () {
			$('.imageHolder', this.container).remove();
			if (typeof this.blockedElement !== 'undefined') {
				if (this.isPageBlockMode()) {
					$.unblockUI();
				} else {
					this.blockedElement.unblock();
				}
			}
			this.container.removeData('progressIndicator');
		};
	};

	$.fn.progressIndicator = function (options) {
		let element = this;
		if (this.length <= 0) {
			element = jQuery('body');
		}
		return element.each(function (index, element) {
			let jQueryObject = $(element),
				progressIndicatorInstance;
			if (typeof jQueryObject.data('progressIndicator') !== 'undefined') {
				progressIndicatorInstance = jQueryObject.data('progressIndicator');
			} else {
				progressIndicatorInstance = new ProgressIndicatorHelper();
				jQueryObject.data('progressIndicator', progressIndicatorInstance);
			}
			progressIndicatorInstance.init(jQueryObject, options).initActions();
		});
	};

	$.progressIndicator = function (options) {
		var progressImageContainer = jQuery('<div></div>');
		var progressIndicatorInstance = new ProgressIndicatorHelper();
		progressIndicatorInstance.init(progressImageContainer, options);
		if (!progressIndicatorInstance.isBlockMode()) {
			progressIndicatorInstance.showOnTop = true;
		}
		progressIndicatorInstance.initActions();
		return progressImageContainer.data('progressIndicator', progressIndicatorInstance);
	};

	//Change the z-index of the block overlay value
	$.blockUI.defaults.baseZ = 10000;
})(jQuery);
