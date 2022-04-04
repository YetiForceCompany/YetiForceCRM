/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
/*globals jQuery, define, exports, require, document */
(function (factory) {
	'use strict';
	if (typeof define === 'function' && define.amd) {
		define('jstree.edit', ['jquery', 'jstree'], factory);
	} else if (typeof exports === 'object') {
		factory(require('jquery'), require('jstree'));
	} else {
		factory(jQuery, jQuery.jstree);
	}
})(function ($, jstree) {
	'use strict';

	if ($.jstree.plugins.edit) {
		return;
	}
	$.jstree.defaults.edit = {
		createClass: ' fas fa-plus-circle',
		deleteClass: ' fas fa-times-circle'
	};
	var _i = document.createElement('I');
	_i.className = 'jstree-edit .fas noAction ';
	_i.setAttribute('role', 'presentation');
	$.jstree.plugins.edit = function (options, parent) {
		this.bind = function () {
			parent.bind.call(this);
			this.element.on(
				'select_node.jstree',
				$.proxy(function (obj, data) {
					const modal = $(data.event.currentTarget).closest('#treePopupContainer');
					const module = modal.find('#relatedModule').val();
					if ($(data.event.target).hasClass('jstree-edit')) {
						const obj = data.node;
						if (obj.original.attr !== 'record') {
							app.hideModalWindow();
							const callbackFunction = function () {
								$('.showModal[data-module="OutsourcedProducts"]').trigger('click');
								Vtiger_Detail_Js.getInstance().loadWidgets();
							};
							const QuickCreateParams = {
								callbackFunction: callbackFunction,
								data: {
									productname: obj.original.text,
									parent_id: app.getRecordId(),
									pscategory: obj.original.record_id
								},
								noCache: true
							};
							App.Components.QuickCreate.createRecord(module, QuickCreateParams);
						} else {
							app.hideModalWindow();
							app.showConfirmModal({
								title: app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE'),
								confirmedCallback: () => {
									AppConnector.request({
										module: module,
										action: 'State',
										record: obj.original.record_id,
										state: 'Trash'
									}).done(function (res) {
										$('.showModal[data-module="OutsourcedProducts"]').trigger('click');
										Vtiger_Detail_Js.getInstance().loadWidgets();
									});
								},
								rejectedCallback: () => {
									$('.showModal[data-module="OutsourcedProducts"]').trigger('click');
								}
							});
						}
					}
				}, this)
			);
		};
		this.redraw_node = function (obj, deep, is_callback, force_render) {
			obj = parent.redraw_node.apply(this, arguments);
			if (obj) {
				var i,
					j,
					tmp = null,
					icon = null;
				for (i = 0, j = obj.childNodes.length; i < j; i++) {
					if (
						obj.childNodes[i] &&
						obj.childNodes[i].className &&
						obj.childNodes[i].className.indexOf('jstree-anchor') !== -1
					) {
						tmp = obj.childNodes[i];
						break;
					}
				}
				if (tmp && this._model.data[obj.id].original.type !== undefined) {
					icon = _i.cloneNode(false);
					if (this._model.data[obj.id].original.attr !== 'record') {
						icon.className += options.createClass;
					} else {
						icon.className += options.deleteClass;
					}
					tmp.appendChild(icon, tmp.childNodes[0]);
				}
			}
			return obj;
		};
	};
});
