/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Vtiger_TreeCategory_Js',
	{},
	{
		modalContainer: false,
		treeInstance: false,
		treeData: false,
		windowParent: app.getWindowParent(),
		getModalContainer: function () {
			if (this.modalContainer == false) {
				this.modalContainer = jQuery('#modalTreeCategoryModal');
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
		generateTree: function (container) {
			let thisInstance = this;
			if (thisInstance.treeInstance == false) {
				thisInstance.treeInstance = container.find('#treePopupContents');
				let plugins = ['search', 'category'];
				if (thisInstance.getRelationType() == '2') {
					plugins.push('checkbox');
				}
				if (thisInstance.getRelationType() == '1') {
					plugins.push('edit');
				}
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
		getRelationType: function () {
			return this.getModalContainer().find('#relationType').val();
		},
		searching: function (text) {
			this.treeInstance.jstree(true).search(text);
		},
		registerSearchEvent: function () {
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
		registerSaveRecords: function (container) {
			const thisInstance = this;
			let ord = [],
				ocd = [];
			$.each(thisInstance.getRecords(), function (index, value) {
				if (value.state && value.state.selected && value.attr === 'record') {
					ord.push(value.record_id);
				}
				if (value.category && value.category.checked && value.attr !== 'record') {
					ocd.push(value.record_id);
				}
			});
			container.find('[name="saveButton"]').on('click', function (e) {
				let recordsToAdd = [],
					categoryToAdd = [],
					recordsToRemove = Object.assign([], ord),
					categoryToRemove = Object.assign([], ocd);
				let saveButton = $(this);
				saveButton.attr('disabled', 'disabled');
				let cSelected = thisInstance.treeInstance.jstree('getCategory', true);
				$.each(cSelected, function (index, treeElement) {
					let value = treeElement.record_id;
					if (treeElement.attr === 'record') {
						if (jQuery.inArray(value, recordsToRemove) == -1) {
							recordsToAdd.push(value);
						} else {
							recordsToRemove.splice(recordsToRemove.indexOf(value), 1);
						}
					} else if (treeElement.attr !== 'record') {
						if (jQuery.inArray(value, categoryToRemove) == -1) {
							categoryToAdd.push(value);
						} else {
							categoryToRemove.splice(categoryToRemove.indexOf(value), 1);
						}
					}
				});
				let params = {
					module: thisInstance.windowParent.app.getModuleName(),
					action: 'RelationAjax',
					mode: 'updateRelation',
					recordsToAdd: recordsToAdd,
					recordsToRemove: recordsToRemove,
					categoryToAdd: categoryToAdd,
					categoryToRemove: categoryToRemove,
					src_record: thisInstance.windowParent.app.getRecordId(),
					related_module: container.find('#relatedModule').val()
				};
				if (recordsToAdd.length > 4) {
					app.showConfirmModal({
						title: app.vtranslate('JS_INFORMATION'),
						text: app.vtranslate('JS_SAVE_SELECTED_ITEMS_ALERT').replace('__LENGTH__', recordsToAdd.length),
						confirmedCallback: () => {
							thisInstance.saveRecordsEvent(params);
						},
						rejectedCallback: () => {
							saveButton.removeAttr('disabled');
						}
					});
				} else {
					thisInstance.saveRecordsEvent(params);
				}
			});
		},
		saveRecordsEvent: function (params) {
			const self = this;
			AppConnector.request(params).done(function (res) {
				self.windowParent.Vtiger_Detail_Js.getInstance().reloadTabContent();
				app.hideModalWindow();
			});
		},
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
		registerEvents: function () {
			let container = this.getModalContainer();
			this.getRecords(container);
			this.generateTree(container);
			this.registerSaveRecords(container);
			this.registerSearchEvent();
			this.registerCounterSelected();
		}
	}
);
jQuery(function () {
	let instance = new Vtiger_TreeCategory_Js();
	instance.registerEvents();
});
