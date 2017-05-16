/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Settings_Vtiger_List_Js("Settings_MappedFields_List_Js", {}, {
	getListContainer: function () {
		return jQuery('#listViewContainer');
	},
	registerFilterChangeEvent: function () {
		var thisInstance = this;
		jQuery('#moduleFilter').on('change', function (e) {
			jQuery('#pageNumber').val("1");
			jQuery('#pageToJump').val('1');
			jQuery('#orderBy').val('');
			jQuery("#sortOrder").val('');
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				sourceModule: jQuery(e.currentTarget).val()
			}
			//Make the select all count as empty
			jQuery('#recordsCount').val('');
			//Make total number of pages as empty
			jQuery('#totalPageCount').text("");
			thisInstance.getListViewRecords(params).then(
					function (data) {
						thisInstance.updatePagination();
						thisInstance.registerBasic();
					}
			);
		});
	},
	/*
	 * Function to register the list view row click event
	 */
	registerRowClickEvent: function () {
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click', '.listViewEntries td:not(.tdActions)', function (e) {
			var editUrl = jQuery(e.currentTarget).parent().find('.glyphicon-pencil').closest('a').attr('href');
			window.location.href = editUrl;
		});
	},
	getDefaultParams: function () {
		var pageNumber = jQuery('#pageNumber').val();
		var module = app.getModuleName();
		var parent = app.getParentModuleName();
		var params = {
			'module': module,
			'parent': parent,
			'page': pageNumber,
			'view': "List",
			sourceModule: jQuery('#moduleFilter').val()
		}

		return params;
	},
	registerAddNewTemplate: function (container) {
		jQuery('#addButton', container).on('click', function () {
			var selectedModule = jQuery('#moduleFilter option:selected').val();
			window.location.href = jQuery(this).data('url') + ((selectedModule) ? '&source_module=' + selectedModule : '');
		});
	},
	registerImportTemplate: function (container) {
		var thisInstance = this;
		jQuery('#importButton', container).on('click', function (e) {
			var currentElement = jQuery(e.currentTarget);
			var url = currentElement.data('url');
			if (typeof url != 'undefined') {
				app.showModalWindow(null, url,
						function (data) {
							var form = data.find('form');
							form.validationEngine(app.validationEngineOptions);
							form.on('submit', function (e) {
								var form = jQuery(e.currentTarget);
								var invalidFields = form.data('jqv').InvalidFields;
								if (invalidFields.length > 0) {
									//If validation fails, form should submit again
									form.removeData('submit');
									return;
								}
								var progressIndicatorElement = jQuery.progressIndicator({
									'position': 'html',
									'blockInfo': {
										'enabled': true
									}
								});
								thisInstance.importSave(form).then(
										function (data) {
											app.hideModalWindow();
											progressIndicatorElement.progressIndicator({'mode': 'hide'})
											Settings_Vtiger_Index_Js.showMessage({text: data.result.message, type: 'info'});
											jQuery('#moduleFilter').trigger('change');
										},
										function (error, err) {
										}
								);
								e.preventDefault();
							});
						});
			}
			e.stopPropagation();
		});
	},
	importSave: function (form) {
		var aDeferred = jQuery.Deferred();
		var formData = new FormData(form[0]);
		if (typeof file != "undefined") {
			formData.append("imported_xml", file);
			delete file;
		}
		if (formData) {
			var params = {
				url: "index.php",
				type: "POST",
				data: formData,
				processData: false,
				contentType: false
			};
			AppConnector.request(params).then(
					function (data) {
						aDeferred.resolve(data);
					},
					function (textStatus, errorThrown) {
						aDeferred.reject(textStatus, errorThrown);
					}
			);
		}
		return aDeferred.promise();
	},
	registerDeleteMap: function () {
		var thisInstance = this;
		this.getListContainer().find('.deleteMap').each(function (index) {
			jQuery(this).on('click', function (e) {
				e.stopPropagation();
				e.preventDefault();
				var templateId = jQuery(this).closest('tr').data('id');
				Vtiger_List_Js.deleteRecord(templateId).then(function () {
					thisInstance.registerBasic();
				});
			});
		});
	},
	registerBasic: function () {
		this.registerDeleteMap();
	},
	registerEvents: function () {
		this._super();
		var container = this.getListContainer();
		this.registerFilterChangeEvent();
		this.registerAddNewTemplate(container);
		this.registerImportTemplate(container);
		this.registerBasic();
	}
});
