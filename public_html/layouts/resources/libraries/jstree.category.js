/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
/*globals jQuery, define, exports, require, document */
(function (factory) {
	'use strict';
	if (typeof define === 'function' && define.amd) {
		define('jstree.category', ['jquery', 'jstree'], factory);
	} else if (typeof exports === 'object') {
		factory(require('jquery'), require('jstree'));
	} else {
		factory(jQuery, jQuery.jstree);
	}
})(function ($, jstree) {
	'use strict';

	if ($.jstree.plugins.category) {
		return;
	}
	$.jstree.defaults.category = {
		checkClass: ' far fa-check-square',
		uncheckClass: ' far fa-square',
		undeterminedClass: ' fas fa-minus-square'
	};
	var _i = document.createElement('I');
	_i.className = 'jstree-category';
	_i.setAttribute('role', 'presentation');
	$.jstree.plugins.category = function (options, parent) {
		this.bind = function () {
			parent.bind.call(this);
			this._data.category.selected = [];
			this.element.on(
				'model.jstree',
				$.proxy(function (e, data) {
					var m = this._model.data,
						dpc = data.nodes,
						i,
						j;
					for (i = 0, j = dpc.length; i < j; i++) {
						if (m[dpc[i]].original.type == 'category') {
							m[dpc[i]].category = [];
							m[dpc[i]].category.checked =
								(m[dpc[i]].category && m[dpc[i]].category.checked) ||
								(m[dpc[i]].original && m[dpc[i]].original.category && m[dpc[i]].original.category.checked);
							if (m[dpc[i]].category.checked) {
								this._data.category.selected.push(dpc[i]);
							}
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
			var condition;
			if (e.target.className.baseVal === undefined) {
				condition = e.target.className.indexOf('noAction');
			} else {
				condition = e.target.className.baseVal.indexOf('noAction');
			}
			if (condition < 0 && this.get_node(obj).original.type == 'category') {
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
					this.trigger('select_node', { node: obj, selected: this._data.core.selected, event: e });
					if (!supress_event) {
						this.trigger('changed', {
							action: 'select_node',
							node: obj,
							selected: this._data.core.selected,
							event: e
						});
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
					this.trigger('deselect_node', {
						node: obj,
						selected: this._data.core.selected,
						event: e
					});
					if (!supress_event) {
						this.trigger('changed', {
							action: 'deselect_node',
							node: obj,
							selected: this._data.core.selected,
							event: e
						});
					}
				}
			}
		};

		this.areAllChildrenWithStates = function (obj, states) {
			let len = obj.children_d.length;
			for (let i = 0; i < len; i++) {
				let child = this.get_node(obj.children_d[i]);
				if (typeof child.category.checked === 'undefined') {
					child.category.checked = false;
				}
				if (states.indexOf(child.category.checked) === -1) {
					return false;
				}
			}
			return true;
		};

		this.checkNode = function (obj, e, traversing = false) {
			let dom = this.get_node(obj, true);
			this._data.category.selected.push(obj.id);
			let cascade = this.settings.checkbox.cascade;
			if (typeof cascade !== 'undefined' && cascade) {
				if (cascade.indexOf('down') !== -1 && !traversing) {
					if (this.is_closed(obj)) {
						this.open_node(obj);
					}
					obj.children.forEach((child) => {
						this.checkNode(this.get_node(child), e);
					});
				}
				if (this.areAllChildrenWithStates(obj, [true])) {
					obj.category.checked = true;
				} else {
					obj.category.checked = null;
				}
			} else {
				obj.category.checked = true;
			}
			if (dom && dom.length) {
				let item = dom.children('.jstree-anchor').find('.jstree-category');
				item.removeClass(options.uncheckClass).removeClass(options.undeterminedClass);
				if (obj.category.checked === true) {
					item.addClass(options.checkClass);
				} else if (obj.category.checked === null) {
					item.addClass(options.undeterminedClass);
				}
				this.trigger('changed', {
					action: 'select_node',
					node: obj,
					selected: this._data.core.selected,
					event: e
				});
			}
			if (typeof cascade !== 'undefined' && cascade) {
				if (cascade.indexOf('up') !== -1) {
					if (obj.parent !== $.jstree.root) {
						let parent = this.get_node(obj.parent);
						this.checkNode(parent, e, 'up');
					}
				}
			}
		};

		this.uncheckNode = function (obj, e, traversing = false) {
			let cascade = this.settings.checkbox.cascade;
			if (typeof cascade !== 'undefined' && cascade) {
				if (cascade.indexOf('down') !== -1 && !traversing) {
					if (this.is_closed(obj)) {
						this.open_node(obj);
					}
					obj.children_d.forEach((childId) => {
						this.uncheckNode(this.get_node(childId), e, traversing);
					});
				}
				if (this.areAllChildrenWithStates(obj, [false])) {
					obj.category.checked = false;
				} else {
					obj.category.checked = null;
				}
			} else {
				obj.category.checked = false;
			}
			let dom = this.get_node(obj, true);
			this._data.category.selected = $.vakata.array_remove_item(this._data.category.selected, obj.id);
			if (dom && dom.length) {
				let item = dom.children('.jstree-anchor').find('.jstree-category');
				item.removeClass(options.checkClass).removeClass(options.undeterminedClass);
				if (obj.category.checked === false) {
					item.addClass(options.uncheckClass);
				} else if (obj.category.checked === null) {
					item.addClass(options.undeterminedClass);
				}
				this.trigger('changed', {
					action: 'deselect_node',
					node: obj,
					selected: this._data.core.selected,
					event: e
				});
			}
			if (typeof cascade !== 'undefined' && cascade) {
				if (cascade.indexOf('up') !== -1) {
					if (obj.parent !== $.jstree.root) {
						let parent = this.get_node(obj.parent);
						this.uncheckNode(parent, e, 'up');
					}
				}
			}
		};
		this.getCategory = function (fullData) {
			fullData = typeof fullData !== 'undefined';
			let i,
				j,
				selected = [];
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
});
