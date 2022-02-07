/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Vtiger_DetailPreview_Js',
	{},
	{
		/**
		 * Redirects to the clicked link from the iframe.
		 */
		registerLinkEvent: function () {
			$('#page').on('click', 'a', function (e) {
				e.preventDefault();
				let target = $(this);
				if (!target.closest('div').hasClass('fieldValue') || target.hasClass('showReferenceTooltip')) {
					if (target.attr('href') && target.attr('href') != '#') {
						top.location.href = target.attr('href');
					}
				}
			});
		},
		/**
		 * Redirects to the current iframe parent.
		 */
		updateParentFrame: function () {
			parent.app.getPageController().updateWindowHeight($('.mainContainer').height(), $(window.frameElement));
		},
		/**
		 * Function sets the correct iframe size.
		 * @param {jQuery} currentHeight - ifrmae body height to be set.
		 * @param {jQuery} frame - ifrmae height to be changed.
		 */
		updateWindowHeight: function (currentHeight, frame) {
			let thisInstance = this;
			let relatedContents = frame.closest('.relatedContents');
			let fixedListHeight = relatedContents.find('.js-list-preview--scroll').height();
			frame.height(currentHeight);
			if (fixedListHeight > currentHeight) {
				currentHeight = fixedListHeight;
			}
			relatedContents
				.find('.gutter, .js-list-preview, .js-side-block, .js-list-detail, .recordsListPreview')
				.height(currentHeight);

			if (window.frameElement) {
				thisInstance.updateParentFrame();
			}
		},
		/**
		 * Creates ResizeSensor, which detects size changes.
		 */
		registerSizeEvent: function () {
			let thisInstance = this;
			new ResizeSensor($('.mainContainer'), function () {
				thisInstance.updateParentFrame();
			});
		},
		/**
		 * Register detail events
		 */
		registerDetailEvent: function () {
			let moduleClassName = app.getModuleName() + '_Detail_Js',
				parent = false;
			if (typeof window[moduleClassName] === 'undefined') {
				moduleClassName = 'Vtiger_Detail_Js';
			}
			if (typeof window[moduleClassName] !== 'undefined') {
				if (typeof window[moduleClassName] === 'function') {
					parent = new window[moduleClassName]();
				}
				if (typeof window[moduleClassName] === 'object') {
					parent = window[moduleClassName];
				}
				if (parent) {
					parent.registerEvents();
				}
			}
		},
		updateChatConfig() {
			if (window.parent.vuexStore && window.ChatModalVueComponent) {
				window.parent.vuexStore.commit('Chat/setDetailPreview', {
					id: window.app.getRecordId(),
					module: window.app.getModuleName()
				});
			}
		},
		/**
		 * Registers DetailPreview events.
		 */
		registerEvents: function () {
			this.registerDetailEvent();
			this.registerLinkEvent();
			this.registerSizeEvent();
			this.updateChatConfig();
		}
	}
);
