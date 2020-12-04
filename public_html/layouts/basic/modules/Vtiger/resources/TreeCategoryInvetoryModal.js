/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Vtiger_TreeCategoryInvetory_Js',
	{},
	{
		modalContainer: false,
		treeInstance: false,
		treeData: false,
		windowParent: app.getWindowParent(),
		getModalContainer: function () {
			if (this.modalContainer == false) {
				this.modalContainer = jQuery('#modalTreeCategoryInvetoryModal');
			}
			return this.modalContainer;
		},
		getRecords: function (container) {
			if (this.treeData == false && container !== 'undefined') {
				let treeValues = container.find('#treePopupValues').val();
				this.treeData = JSON.parse(treeValues);
			}
			return this.treeData;
		},

		/*
		 * Function generates a tree.
		 */
		generateTree: function (container) {
			let thisInstance = this;
			if (thisInstance.treeInstance == false) {
				thisInstance.treeInstance = container.find('#treePopupContents');
				let plugins = ['search', 'category', 'checkbox'];
				thisInstance.treeInstance.jstree(
					$.extend(
						true,
						{
							core: {
								data: thisInstance.getRecords(),
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
						thisInstance.treeInstance.data('params')
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
		getSearchEvent: function () {
			let thisInstance = this;
			let valueSearch = $('#valueSearchTree');
			let btnSearch = $('#btnSearchTree');
			valueSearch.on('keypress', function (e) {
				if (e.which == 13) {
					thisInstance.searching(valueSearch.val());
				}
			});
			btnSearch.on('click', function () {
				thisInstance.searching(valueSearch.val());
			});
		},

		/*
		 * Function retrieves the selected records.
		 */
		registerGetSelectRecords: function (container) {
			const thisInstance = this;
			container.find('[name="saveButton"]').on('click', function (e) {
				let recordsToAdd = [];
				let cSelected = thisInstance.treeInstance.jstree('getCategory', true);
				$.each(cSelected, function (index, treeElement) {
					if (treeElement.attr === 'record') {
						recordsToAdd[treeElement.record_id] = treeElement.text;
					}
				});
				thisInstance.saveSelectRecordsEvent(recordsToAdd);
			});
		},

		/*
		 * Function saves the selected records.
		 */
		saveSelectRecordsEvent: function (params) {
			if (params.length !== 0) {
				for (let i in params) {
					let parentElem = Vtiger_Inventory_Js.getInventoryInstance().addItem('Products');
					Vtiger_Edit_Js.getInstance().setReferenceFieldValue(parentElem, {
						name: params[i],
						id: i
					});
				}
				app.hideModalWindow();
			} else {
				app.showNotify({
					text: app.vtranslate('JS_PLEASE_SELECT_ONE_RECORD'),
					type: 'error'
				});
			}
		},

		/*
		 * Function counts the selected records.
		 */
		registerCounterSelected: function () {
			let thisInstance = this;
			this.treeInstance.on('changed.jstree', function (e, data) {
				let counterSelected = 0;
				let html = '';
				$.each(thisInstance.treeInstance.jstree('get_selected', true), function (index, value) {
					let id = value.original.record_id.toString();
					if (id.indexOf('T')) {
						counterSelected++;
					}
				});
				html = app.vtranslate('JS_SELECTED_ELEMENTS') + ': ' + counterSelected;
				$('.counterSelected').text(html);
			});
		},

		/**
		 * Register modal events
		 */
		registerEvents: function () {
			let container = this.getModalContainer();
			this.getRecords(container);
			this.generateTree(container);
			this.registerGetSelectRecords(container);
			this.getSearchEvent();
			this.registerCounterSelected();
		}
	}
);
jQuery(function () {
	let instance = new Vtiger_TreeCategoryInvetory_Js();
	instance.registerEvents();
});
