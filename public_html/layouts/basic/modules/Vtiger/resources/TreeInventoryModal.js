/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Base_TreeInventoryModal_JS',
	{},
	{
		/**
		 * Container
		 */
		container: false,
		/**
		 * Tree instance
		 */
		treeInstance: false,
		/**
		 * Set event for select row
		 * @param {function} cb
		 */
		setSelectEvent: function (cb) {
			this.selectEvent = cb;
		},
		/**
		 * Gets tree data
		 */
		getTreeData: function () {
			let treeData = this.container.find('.js-tree-value').val();
			return treeData ? JSON.parse(treeData) : [];
		},

		/*
		 * Function generates a tree.
		 */
		generateTree: function () {
			if (this.treeInstance == false) {
				this.treeInstance = this.container.find('.js-tree-contents');
				let plugins = ['search', 'category', 'checkbox'];
				this.treeInstance.jstree(
					$.extend(
						true,
						{
							core: {
								data: this.getTreeData(),
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
						this.treeInstance.data('params')
					)
				);
			}
		},

		/*
		 * Function looks up the entered phrases.
		 */
		searching: function (text) {
			this.treeInstance.jstree(true).search(text);
		},

		/*
		 * Function retrieves the search terms.
		 */
		registerSearchEvent: function () {
			let valueSearch = this.container.find('#valueSearchTree');
			let btnSearch = this.container.find('#btnSearchTree');
			valueSearch.on('keypress', (e) => {
				if (e.which == 13) {
					this.searching(valueSearch.val());
				}
			});
			btnSearch.on('click', () => {
				this.searching(valueSearch.val());
			});
		},

		/*
		 * Function retrieves the selected records.
		 */
		registerSelectRecords: function () {
			this.container.find('.js-modal__save').on('click', (e) => {
				let recordsToAdd = [];
				$.each(this.treeInstance.jstree('getCategory', true), function (_, treeElement) {
					if (treeElement.attr === 'record') {
						recordsToAdd[treeElement.record_id] = treeElement.text;
					}
				});
				if (recordsToAdd.length) {
					this.selectEvent(recordsToAdd);
					app.hideModalWindow(false, this.container.parent().attr('id'));
				} else {
					app.showNotify({ text: app.vtranslate('JS_PLEASE_SELECT_ONE_RECORD') });
				}
			});
		},

		/**
		 * Register modal events
		 */
		registerEvents: function (container) {
			this.container = container;
			this.generateTree();
			this.registerSelectRecords();
			this.registerSearchEvent();
		}
	}
);
