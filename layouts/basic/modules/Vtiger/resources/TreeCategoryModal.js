/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("Vtiger_TreeCategory_Js", {}, {
	modalContainer: false,
	treeInstance: false,
	treeData: false,
	getModalContainer: function () {
		if (this.modalContainer == false) {
			this.modalContainer = jQuery('#globalmodal .modal-content');
		}
		return this.modalContainer;

	},
	getRecords: function (container) {
		if (this.treeData == false && container != 'undefined') {
			var treeValues = container.find('#treePopupValues').val();
			this.treeData = JSON.parse(treeValues);
		}
		return this.treeData;

	},
	generateTree: function (container) {
		var thisInstance = this;
		if (thisInstance.treeInstance == false) {
			thisInstance.treeInstance = container.find("#treePopupContents");
			thisInstance.treeInstance.jstree({
				core: {
					data: thisInstance.getRecords(),
					themes: {
						name: 'proton',
						responsive: true
					}
				},
				plugins: [
					"category",
					"checkbox",
					"search"
				]
			});
		}
	},
	searching: function (text) {
		this.treeInstance.jstree(true).search(text);
	},
	registerSearchEvent: function () {
		var thisInstance = this;
		var valueSearch = $('#valueSearchTree');
		var btnSearch = $('#btnSearchTree');
		valueSearch.keypress(function (e) {
			if (e.which == 13) {
				thisInstance.searching(valueSearch.val());
			}
		});
		btnSearch.click(function () {
			thisInstance.searching(valueSearch.val());
		});
	},
	registerSaveRecords: function (container) {
		var thisInstance = this;
		var ord = [], ocd = [], rSelected = [], cSelected = [], recordsToAdd = [], recordsToRemove = [], categoryToAdd = [], categoryToRemove = [];
		$.each(thisInstance.getRecords(), function (index, value) {
			if (value.state && value.state.selected && value.type == "record") {
				ord.push(value.record_id);
			}
			if (value.category && value.category.checked) {
				ocd.push(value.record_id);
			}
		});

		container.find('[name="saveButton"]').on('click', function (e) {
			$(this).attr('disabled', 'disabled');
			$.each(thisInstance.treeInstance.jstree("get_selected", true), function (index, value) {
				if (jQuery.inArray(value.original.record_id, ord) == -1 && value.original.type == "record") {
					recordsToAdd.push(value.original.record_id);
				}
				rSelected.push(value.original.record_id);
			});
			cSelected = thisInstance.treeInstance.jstree("getCategory");
			$.each(ord, function (index, value) {
				if (jQuery.inArray(value, rSelected) == -1) {
					recordsToRemove.push(value);
				}
			});
			$.each(cSelected, function (index, value) {
				if (jQuery.inArray(value, ocd) == -1) {
					categoryToAdd.push(value);
				}
			});
			$.each(ocd, function (index, value) {
				if (jQuery.inArray(value, cSelected) == -1) {
					categoryToRemove.push(value);
				}
			});
			AppConnector.request({
				module: app.getModuleName(),
				action: 'RelationAjax',
				mode: 'updateRelation',
				recordsToAdd: recordsToAdd,
				recordsToRemove: recordsToRemove,
				categoryToAdd: categoryToAdd,
				categoryToRemove: categoryToRemove,
				src_record: app.getRecordId(),
				related_module: container.find('#related_module').val(),
			}).then(function (res) {
				var thisInstance = Vtiger_Detail_Js.getInstance();
				var selectedTab = thisInstance.getSelectedTab();
				thisInstance.registerRelatedModulesRecordCount(selectedTab);
				selectedTab.trigger('click');
				app.hideModalWindow();
			})
		});
	},
	registerCounterSelected: function () {
		var thisInstance = this;
		this.treeInstance.on("changed.jstree", function (e, data) {
			var counterSelected = 0;
			var html = '';
			$.each(thisInstance.treeInstance.jstree("get_selected", true), function (index, value) {
				var id = value.original.record_id.toString();
				if (id.indexOf("T")) {
					counterSelected++;
				}
			});
			html = app.vtranslate('JS_SELECTED_ELEMENTS') + ': ' + counterSelected;
			$('.counterSelected').text(html);
		});
	},
	registerEvents: function () {
		var container = this.getModalContainer();
		this.getRecords(container);
		this.generateTree(container);
		this.registerSaveRecords(container);
		this.registerSearchEvent();
		this.registerCounterSelected();
	}
});
jQuery(function () {
	var instance = new Vtiger_TreeCategory_Js();
	instance.registerEvents();
});
