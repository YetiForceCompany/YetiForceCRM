/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
/*globals jQuery, define, exports, require, document */
(function (factory) {
	"use strict";
	if (typeof define === 'function' && define.amd) {
		define('jstree.category', ['jquery', 'jstree'], factory);
	} else if (typeof exports === 'object') {
		factory(require('jquery'), require('jstree'));
	} else {
		factory(jQuery, jQuery.jstree);
	}
}(function ($, jstree, undefined) {
	"use strict";

	if ($.jstree.plugins.category) {
		return;
	}
	$.jstree.defaults.category = {
		checkClass: ' glyphicon-check',
		uncheckClass: ' glyphicon-unchecked'
	};
	var _i = document.createElement('I');
	_i.className = 'jstree-category glyphicon';
	_i.setAttribute('role', 'presentation');
	$.jstree.plugins.category = function (options, parent) {
		this.bind = function () {
			parent.bind.call(this);
			this._data.category.selected = [];
			this.element.on('model.jstree', $.proxy(function (e, data) {
				var m = this._model.data,
						p = m[data.parent],
						dpc = data.nodes,
						i, j;
				for (i = 0, j = dpc.length; i < j; i++) {
					if (m[dpc[i]].original.type == 'category') {
						m[dpc[i]].category = [];
						m[dpc[i]].category.checked = (m[dpc[i]].category && m[dpc[i]].category.checked) || (m[dpc[i]].original && m[dpc[i]].original.category && m[dpc[i]].original.category.checked);
						if (m[dpc[i]].category.checked) {
							this._data.category.selected.push(dpc[i]);
						}
					}
				}
			}, this));
		};
		this.redraw_node = function (obj, deep, is_callback, force_render) {
			obj = parent.redraw_node.apply(this, arguments);
			if (obj) {
				var i, j, tmp = null, icon = null;
				for (i = 0, j = obj.childNodes.length; i < j; i++) {
					if (obj.childNodes[i] && obj.childNodes[i].className && obj.childNodes[i].className.indexOf("jstree-anchor") !== -1) {
						tmp = obj.childNodes[i];
						break;
					}
				}
				if (tmp && this._model.data[obj.id].category !== undefined) {
					icon = _i.cloneNode(false);
					if (this._model.data[obj.id].category.checked) {
						icon.className += options.checkClass;
					} else {
						icon.className += options.uncheckClass;
					}
					tmp.insertBefore(icon, tmp.childNodes[0]);
				}
			}
			return obj;
		};

		this.select_node = function (obj, supress_event, prevent_open, e) {
			if (e.target.className.indexOf("noAction") < 0 && this.get_node(obj).original.type == 'category') {
				obj = this.get_node(obj);
				if (obj.category.checked) {
					this.uncheckNode(obj, e);
				} else {
					this.checkNode(obj, e);
				}
				return false;
			} else {
				var dom, t1, t2, th;
				if ($.isArray(obj)) {
					obj = obj.slice();
					for (t1 = 0, t2 = obj.length; t1 < t2; t1++) {
						this.select_node(obj[t1], supress_event, prevent_open, e);
					}
					return true;
				}
				obj = this.get_node(obj);
				if (!obj || obj.id === $.jstree.root) {
					return false;
				}
				dom = this.get_node(obj, true);
				if (!obj.state.selected) {
					obj.state.selected = true;
					this._data.core.selected.push(obj.id);
					if (!prevent_open) {
						dom = this._open_to(obj);
					}
					if (dom && dom.length) {
						dom.attr('aria-selected', true).children('.jstree-anchor').addClass('jstree-clicked');
					}
					this.trigger('select_node', {'node': obj, 'selected': this._data.core.selected, 'event': e});
					if (!supress_event) {
						this.trigger('changed', {'action': 'select_node', 'node': obj, 'selected': this._data.core.selected, 'event': e});
					}
				}
			}
		};

		this.deselect_node = function (obj, supress_event, e) {
			if (this.get_node(obj).original.type == 'category') {
				obj = this.get_node(obj);
				if (obj.category.checked) {
					this.uncheckNode(obj, e);
				} else {
					this.checkNode(obj, e);
				}
				return false;
			} else {
				var t1, t2, dom;
				if ($.isArray(obj)) {
					obj = obj.slice();
					for (t1 = 0, t2 = obj.length; t1 < t2; t1++) {
						this.deselect_node(obj[t1], supress_event, e);
					}
					return true;
				}
				obj = this.get_node(obj);
				if (!obj || obj.id === $.jstree.root) {
					return false;
				}
				dom = this.get_node(obj, true);
				if (obj.state.selected) {
					obj.state.selected = false;
					this._data.core.selected = $.vakata.array_remove_item(this._data.core.selected, obj.id);
					if (dom.length) {
						dom.attr('aria-selected', false).children('.jstree-anchor').removeClass('jstree-clicked');
					}
					this.trigger('deselect_node', {'node': obj, 'selected': this._data.core.selected, 'event': e});
					if (!supress_event) {
						this.trigger('changed', {'action': 'deselect_node', 'node': obj, 'selected': this._data.core.selected, 'event': e});
					}
				}
			}
		};

		this.checkNode = function (obj, e) {
			if (!obj.category.checked) {
				var dom = this.get_node(obj, true);
				obj.category.checked = true;
				this._data.category.selected.push(obj.id);
				if (dom && dom.length) {
					dom.children('.jstree-anchor').find('.jstree-category').addClass(options.checkClass).removeClass(options.uncheckClass);
					this.trigger('changed', {action: 'select_node', node: obj, selected: this._data.core.selected, event: e});
				}
			}

		};
		this.uncheckNode = function (obj, e) {
			if (obj.category.checked) {
				var dom = this.get_node(obj, true);
				obj.category.checked = false;
				this._data.category.selected = $.vakata.array_remove_item(this._data.category.selected, obj.id);
				if (dom && dom.length) {
					dom.children('.jstree-anchor').find('.jstree-category').removeClass(options.checkClass).addClass(options.uncheckClass);
					this.trigger('changed', {action: 'deselect_node', node: obj, selected: this._data.core.selected, event: e});
				}
			}
		};
		this.getCategory = function (fullData) {
			var fullData = typeof fullData !== 'undefined' ? true : false;
			var i, j, selected = [];
			for (i = 0, j = this._data.category.selected.length; i < j; i++) {
				if (fullData) {
					selected.push(this._model.data[this._data.category.selected[i]].original);
				} else {
					selected.push(this._model.data[this._data.category.selected[i]].original.record_id);
				}
			}
			return selected;
		};
	};
}));
