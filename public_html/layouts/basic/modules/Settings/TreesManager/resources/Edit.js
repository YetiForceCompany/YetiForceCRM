/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class('Settings_TreesManager_Edit_Js', {}, {
	jstreeInstance: false,
	jstreeLastID: 0,
	registerEvents: function () {
		const self = this,
			editContainer = $("#EditView"),
			jstreeInstance = self.createTree();
		editContainer.validationEngine();
		$('.addNewElementBtn').on('click', () => {
			const newElement = $('input.addNewElement'),
				ref = jstreeInstance.jstree(true);
			if (newElement.val() === '') {
				const message = app.vtranslate('JS_FIELD_CAN_NOT_BE_EMPTY');
				newElement.validationEngine('showPrompt', message, 'error', 'bottomLeft', true);
				return false;
			}
			self.jstreeLastID += 1;
			ref.create_node('#', {
				id: self.jstreeLastID,
				text: newElement.val(),
				icon: false
			}, 'last');
			newElement.val('');
		});
		$('.saveTree').on('click', () => {
			jstreeInstance.jstree('deselect_all', true);
			const json = jstreeInstance.jstree("get_json");
			let forSave = [];
			$.each(json, function (index, value) {
				if (value.text == value.li_attr.text) {
					value.text = value.li_attr.key;
				}
				forSave[index] = value;
			});
			$('#treeValues').val(JSON.stringify(forSave));
			editContainer.submit();
		});
		$('.addNewElement').on('keydown', (event) => {
			if (event.keyCode == 13) {
				$('.addNewElementBtn').trigger("click");
				event.preventDefault();
				return false;
			}
		});
	},
	createTree: function () {
		const self = this;
		if (this.jstreeInstance == false) {
			self.jstreeLastID = parseInt($('#treeLastID').val());
			let treeValues = $('#treeValues').val(),
				data = JSON.parse(treeValues);
			self.jstreeInstance = $("#treeContents");
			self.jstreeInstance.jstree({
				core: {
					data: data,
					themes: {
						name: 'proton',
						responsive: true
					},
					check_callback: true
				},
				contextmenu: {
					items: {
						create: {
							label: app.vtranslate('JS_JSTREE_CREATE'),
							action: function (data) {
								let treeInstance = $.jstree.reference(data.reference),
									selectedNode = treeInstance.get_node(data.reference);
								self.jstreeLastID = self.jstreeLastID + 1;
								treeInstance.create_node(selectedNode, {
									id: self.jstreeLastID,
									text: app.vtranslate('JS_NEW_ITEM')
								}, "last", function (new_node) {
									setTimeout(function () {
										treeInstance.edit(new_node);
									}, 0);
								});
							}
						},
						rename: {
							label: app.vtranslate('JS_JSTREE_RENAME'),
							action: function (data) {
								let treeInstance = $.jstree.reference(data.reference),
									selectedNode = treeInstance.get_node(data.reference);
								treeInstance.edit(selectedNode);
							}
						},
						changeIcon: {
							label: app.vtranslate('JS_JSTREE_CHANGE_ICON'),
							action: function (data) {
								let treeInstance = $.jstree.reference(data.reference),
									selectedNode = treeInstance.get_node(data.reference);
								Settings_Vtiger_Index_Js.selectIcon().done(function (data) {
									if (data['name'] == '-') {
										self.jstreeInstance.jstree(true).set_icon(selectedNode.id, false);
									} else {
										self.jstreeInstance.jstree(true).set_icon(selectedNode.id, data['name']);
									}
								});
							}
						},
						remove: {
							label: app.vtranslate('JS_JSTREE_REMOVE'),
							action: function (data) {
								let treeInstance = $.jstree.reference(data.reference),
									id = treeInstance.get_selected(),
									status = true;
								$.each(id, function (index, value) {
									let menu = treeInstance.get_node(value);
									if (menu.children.length > 0) {
										Settings_Vtiger_Index_Js.showMessage({
											text: app.vtranslate('JS_YOU_CANNOT_DELETE_PERENT_ITEM'),
											type: 'error'
										});
										status = false;
									}
								});
								if (status) {
									self.deleteItemEvent(id, treeInstance).done(function (e) {
										if (e.length > 0) {
											$.each(id, function (index, value) {
												treeInstance.delete_node(value);
											});
										}
									});
								}
							}
						},
						ccp: {
							label: app.vtranslate('JS_JSTREE_CCP'),
							submenu: {
								cut: {
									label: app.vtranslate('JS_JSTREE_CUT'),
									"action": function (data) {
										let treeInstance = $.jstree.reference(data.reference),
											selectedNode = treeInstance.get_node(data.reference);
										if (treeInstance.is_selected(selectedNode)) {
											treeInstance.cut(treeInstance.get_top_selected());
										} else {
											treeInstance.cut(selectedNode);
										}
									}
								},
								paste: {
									label: app.vtranslate('JS_JSTREE_PASTE'),
									"action": function (data) {
										let treeInstance = $.jstree.reference(data.reference),
											selectedNode = treeInstance.get_node(data.reference);
										treeInstance.paste(selectedNode);
									}
								},
							}
						}
					}
				},
				plugins: ["contextmenu", "dnd"]
			});
		}
		return this.jstreeInstance;
	},
	deleteItemEvent: function (id, inst) {
		let self = this,
			aDeferred = jQuery.Deferred(),
			data = inst.get_json();
		$.each(id, function (index, id) {
			data = self.checkChildren(id, data);
		});
		if (data.length == 0) {
			Settings_Vtiger_Index_Js.showMessage({
				text: app.vtranslate('JS_YOU_CANNOT_DELETE_ALL_THE_ITEMS'),
				type: 'error'
			})
			aDeferred.resolve();
			return aDeferred.promise();
		}
		app.showModalWindow(null, 'index.php?module=TreesManager&parent=Settings&view=ReplaceTreeItem', function (wizardContainer) {
			let jstreeInstanceReplace = wizardContainer.find('#treePopupContents');
			jstreeInstanceReplace.jstree({
				core: {
					data: data,
					themes: {
						name: 'proton',
						responsive: true
					},
				},
			}).on("loaded.jstree", function (event, data) {
				$(this).jstree("open_all");
			});
			wizardContainer.find('.js-modal__save').on('click', () => {
				let selected = jstreeInstanceReplace.jstree("get_selected"),
					replaceIdsElement = $('#replaceIds'),
					replaceIds = replaceIdsElement.val(),
					data = [];
				if (replaceIds !== '') {
					data = JSON.parse(replaceIds);
				}
				if (!selected.length || selected.length > 1) {
					let message = 'JS_ONLY_ONE_ITEM_SELECTED';
					if (!selected.length) {
						message = 'JS_NO_ITEM_SELECTED';
					}
					Settings_Vtiger_Index_Js.showMessage({
						type: 'error',
						text: app.vtranslate(message)
					});
					return false;
				}
				data = $.merge(data, [{'old': id, 'new': selected}]);
				replaceIdsElement.val(JSON.stringify(data));
				app.hideModalWindow();
				aDeferred.resolve(selected);
			});
		});
		return aDeferred.promise();
	},
	checkChildren: function (id, data) {
		let self = this,
			dataNew = [];
		for (var key in data) {
			if (data[key].id != id) {
				if (data[key].children.length) {
					data[key].children = self.checkChildren(id, data[key].children);
				}
				dataNew.push(data[key]);
			}
		}
		return dataNew;
	}
});
