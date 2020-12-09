/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Base_TreeCategoryInventoryModal_JS',
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
		getRecords: function () {
			let container = this.modalContainer;
			if (this.treeData == false && container !== 'undefined') {
				let treeValues = container.find('#treePopupValues').val();
				this.treeData = JSON.parse(treeValues);
			}
			return this.treeData;
		},

		/*
		 * Function generates a tree.
		 */
		generateTree: function () {
			let thisInstance = this;
			if (thisInstance.treeInstance == false) {
				thisInstance.treeInstance = this.modalContainer.find('#treePopupContents');
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
		registerGetSelectRecords: function () {
			const thisInstance = this;
			this.modalContainer.find('[name="saveButton"]').on('click', function (e) {
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

		/**
		 * Register modal events
		 */
		registerEvents: function (container) {
			this.modalContainer = container;
			this.getRecords();
			this.generateTree();
			this.registerGetSelectRecords();
			this.getSearchEvent();
		}
	}
);
