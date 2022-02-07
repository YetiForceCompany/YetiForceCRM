/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Base_TreeModal_JS',
	{},
	{
		/**
		 * Event for select row
		 */
		selectEvent: false,
		/**
		 * Modal container
		 */
		container: false,
		/**
		 * Jstree element
		 */
		tree: false,
		/**
		 * Multiple selection
		 */
		multiple: false,
		/**
		 * Set event for select row
		 * @param {function} cb
		 */
		setSelectEvent: function (cb) {
			this.selectEvent = cb;
		},
		/**
		 * Generate tree
		 */
		generateTree: function () {
			let plugins = [];
			if (this.multiple) {
				plugins.push('category');
				plugins.push('checkbox');
			}
			plugins.push('search');
			this.tree.jstree(
				$.extend(
					true,
					{
						core: {
							data: JSON.parse(this.container.find('.js-tree-value').val()),
							themes: {
								name: 'proton',
								responsive: true
							}
						},
						checkbox: {
							three_state: false
						},
						plugins: plugins
					},
					this.tree.data('params')
				)
			);
		},
		/**
		 * Register select events
		 */
		registerSelectEvent: function () {
			if (this.multiple) {
				this.container.find('[name="saveButton"]').on('click', () => {
					let id = [],
						name = [];
					$.each(this.tree.jstree('getCategory', true), function (index, value) {
						id.push('T' + value.id);
						name.push(value.text);
					});
					this.selectEvent({ id: id.join(), name: name.join(', ') });
					app.hideModalWindow(false, this.container.parent().attr('id'));
				});
			} else {
				this.tree.on('select_node.jstree', (event, data) => {
					this.selectEvent({ id: 'T' + data.node.id, name: data.node.text });
					app.hideModalWindow(false, this.container.parent().attr('id'));
				});
			}
		},
		registerSearchEvent: function () {
			const thisInstance = this;
			let valueSearch = this.container.find('#valueSearchTree');
			valueSearch.on('keypress', function (e) {
				if (e.which == 13) {
					thisInstance.searchingInTree(valueSearch.val());
				}
			});
			this.container.find('#btnSearchTree').on('click', function () {
				thisInstance.searchingInTree(valueSearch.val());
			});
		},
		searchingInTree: function (text) {
			this.tree.jstree(true).search(text);
		},
		/**
		 * Register base events
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			this.container = modalContainer;
			this.tree = this.container.find('.js-tree-contents');
			this.multiple = this.container.find('.js-multiple').val() == 1 ? true : false;
			this.generateTree();
			this.registerSearchEvent();
			this.registerSelectEvent();
		}
	}
);
