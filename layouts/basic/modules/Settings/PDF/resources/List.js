/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Settings_Vtiger_List_Js("Settings_PDF_List_Js", {}, {
	getListContainer: function () {
		return jQuery('#listViewContainer');
	},
	registerFilterChangeEvent: function () {
		var thisInstance = this;
		jQuery('#moduleFilter').on('change', function (e) {
			jQuery('#pageNumber').val('1');
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
			jQuery('#totalPageCount').text('');
			thisInstance.getListViewRecords(params).then(
					function (data) {
						thisInstance.updatePagination();
					}
			);
		});
	},
	/*
	 * Function to register the list view row click event
	 */
	registerRowClickEvent: function () {
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click', '.listViewEntries', function (e) {
			var editUrl = jQuery(e.currentTarget).find('.glyphicon-pencil').closest('a').attr('href');
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
		};
		return params;
	},
	registerAddNewTemplate: function (container) {
		jQuery('#addButton', container).on('click', function () {
			var selectedModule = jQuery('#moduleFilter option:selected').val();
			window.location.href = jQuery(this).data('url') + '&source_module=' + selectedModule;
		});
	},
	registerImportTemplate: function (container) {
		jQuery('#importButton', container).on('click', function () {
			window.location.href = jQuery(this).data('url');
		});
	},
	registerTemplateDelete: function (container) {
		var thisInstance = this;
		if (container == undefined) {
			container = thisInstance.getListContainer();
		}
		container.find('.templateDelete').on('click', function (e) {
			e.stopPropagation();
			e.preventDefault();
			var templateId = jQuery(this).closest('tr').data('id');
			Vtiger_List_Js.deleteRecord(templateId).then(function () {
				thisInstance.registerTemplateDelete(container);
			});
		});
	},
	/*
	 * Function which will give you all the list view params
	 */
	getListViewRecords: function (urlParams) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		this._super(urlParams).then(function (data) {
			thisInstance.registerTemplateDelete();
			aDeferred.resolve(data);
		});
		return aDeferred.promise();
	},
	registerEvents: function () {
		this._super();
		var container = this.getListContainer();
		this.registerFilterChangeEvent();
		this.registerAddNewTemplate(container);
		this.registerTemplateDelete(container);
		this.registerImportTemplate(container);
	}
});
