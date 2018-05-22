/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
jQuery.Class('Settings_TreesManager_Edit_Js', {}, {
	jstreeInstance: false,
	jstreeLastID: 0,
	registerEvents: function () {
		const thisInstance = this,
			editContainer = $("#EditView"),
			jstreeInstance = thisInstance.createTree();
		editContainer.validationEngine();
		$('.addNewElementBtn').on('click', function (e) {
			const newElement = $('input.addNewElement'),
				ref = jstreeInstance.jstree(true);
			if (newElement.val() === '') {
				const message = app.vtranslate('JS_FIELD_CAN_NOT_BE_EMPTY');
				newElement.validationEngine('showPrompt', message, 'error', 'bottomLeft', true);
				return false;
			}
			thisInstance.jstreeLastID += 1;
			ref.create_node('#', {
				id: thisInstance.jstreeLastID,
				text: newElement.val(),
				icon: false
			}, 'last');
			newElement.val('');
		});
		$('.saveTree').on('click', function (e) {
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
		$('.addNewElement').on('keydown', function (event) {
			if (event.keyCode == 13) {
				$('.addNewElementBtn').trigger("click");
				event.preventDefault();
				return false;
			}
		});
	},
	createTree: function () {
		var thisInstance = this;
		if (this.jstreeInstance == false) {
			thisInstance.jstreeLastID = parseInt($('#treeLastID').val());
			var treeValues = $('#treeValues').val();
			var data = JSON.parse(treeValues);
			thisInstance.jstreeInstance = $("#treeContents");
			thisInstance.jstreeInstance.jstree({
				core: {
					data: data,
					themes: {
						name: 'proton',
						responsive: true
					},
					check_callback: true,
				},
				contextmenu: {
					items: {
						create: {
							label: app.vtranslate('JS_JSTREE_CREATE'),
							action: function (data) {
								var inst = $.jstree.reference(data.reference);
								obj = inst.get_node(data.reference);
								thisInstance.jstreeLastID = thisInstance.jstreeLastID + 1;
								inst.create_node(obj, {
									id: thisInstance.jstreeLastID,
									text: app.vtranslate('JS_NEW_ITEM')
								}, "last", function (new_node) {
									setTimeout(function () {
										inst.edit(new_node);
									}, 0);
								});
							}
						},
						rename: {
							label: app.vtranslate('JS_JSTREE_RENAME'),
							action: function (data) {
								var inst = $.jstree.reference(data.reference),
									obj = inst.get_node(data.reference);
								inst.edit(obj);
							}
						},
						changeIcon: {
							label: app.vtranslate('JS_JSTREE_CHANGE_ICON'),
							action: function (data) {
								var instanceTree = $.jstree.reference(data.reference);
								var node = instanceTree.get_node(data.reference);
								Settings_Vtiger_Index_Js.selectIcon().then(function (data) {
									if (data['name'] == '-') {
										thisInstance.jstreeInstance.jstree(true).set_icon(node.id, false);
									} else {
										thisInstance.jstreeInstance.jstree(true).set_icon(node.id, data['name']);
									}
								});
							}
						},
						remove: {
							label: app.vtranslate('JS_JSTREE_REMOVE'),
							action: function (data) {
								var inst = $.jstree.reference(data.reference);
								var obj = inst.get_node(data.reference);
								var id = inst.get_selected();
								var status = true;
								$.each(id, function (index, value) {
									var menu = inst.get_node(value);
									if (menu.children.length > 0) {
										Settings_Vtiger_Index_Js.showMessage({
											text: app.vtranslate('JS_YOU_CANNOT_DELETE_PERENT_ITEM'),
											type: 'error'
										})
										status = false;
									}
								});
								if (status) {
									thisInstance.deleteItemEvent(id, inst).then(function (e) {
										if (e.length > 0) {
											$.each(id, function (index, value) {
												inst.delete_node(value);
											});
										}
									})
								}
							}
						},
						ccp: {
							label: app.vtranslate('JS_JSTREE_CCP'),
							submenu: {
								cut: {
									label: app.vtranslate('JS_JSTREE_CUT'),
									"action": function (data) {
										var inst = $.jstree.reference(data.reference),
											obj = inst.get_node(data.reference);
										if (inst.is_selected(obj)) {
											inst.cut(inst.get_top_selected());
										} else {
											inst.cut(obj);
										}
									}
								},
								paste: {
									label: app.vtranslate('JS_JSTREE_PASTE'),
									"action": function (data) {
										var inst = $.jstree.reference(data.reference),
											obj = inst.get_node(data.reference);
										inst.paste(obj);
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
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var data = inst.get_json();
		$.each(id, function (index, id) {
			data = thisInstance.checkChildren(id, data);
		});
		if (data.length == 0) {
			Settings_Vtiger_Index_Js.showMessage({
				text: app.vtranslate('JS_YOU_CANNOT_DELETE_ALL_THE_ITEMS'),
				type: 'error'
			})
			aDeferred.resolve();
			return aDeferred.promise();
		}
		app.showModalWindow(null, "index.php?module=TreesManager&parent=Settings&view=ReplaceTreeItem", function (wizardContainer) {
			var form = jQuery('form', wizardContainer);
			jstreeInstanceReplace = wizardContainer.find('#treePopupContents');
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
			form.on('submit', function (e) {
				var selected = jstreeInstanceReplace.jstree("get_selected");
				var replaceIds = $('#replaceIds').val();
				if (replaceIds == '') {
					var data = [];
				} else {
					var data = JSON.parse(replaceIds);
				}
				if (!selected.length) {
					var params = {};
					params['type'] = 'error';
					params['text'] = app.vtranslate('JS_NO_ITEM_SELECTED');
					Settings_Vtiger_Index_Js.showMessage(params);
					return false;
				} else if (selected.length > 1) {
					var params = {};
					params['type'] = 'error';
					params['text'] = app.vtranslate('JS_ONLY_ONE_ITEM_SELECTED');
					Settings_Vtiger_Index_Js.showMessage(params);
					return false;
				}
				data = $.merge(data, [{'old': id, 'new': selected}]);
				$('#replaceIds').val(JSON.stringify(data));
				app.hideModalWindow();
				aDeferred.resolve(selected);
			});
		});
		return aDeferred.promise();
	},
	checkChildren: function (id, data) {
		var thisInstance = this;
		var dataNew = [];
		for (var key in data) {
			if (data[key].id != id) {
				if (data[key].children.length) {
					data[key].children = thisInstance.checkChildren(id, data[key].children);
				}
				dataNew.push(data[key]);
			}
		}
		return dataNew;
	}
});
