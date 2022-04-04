/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_List_Js(
	'Settings_PDF_List_Js',
	{},
	{
		getListContainer: function () {
			return jQuery('#listViewContainer');
		},
		registerFilterChangeEvent: function () {
			var thisInstance = this;
			jQuery('#moduleFilter').on('change', function (e) {
				jQuery('#pageNumber').val('1');
				jQuery('#pageToJump').val('1');
				jQuery('#orderBy').val('');
				jQuery('#sortOrder').val('');
				var params = {
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					sourceModule: jQuery(e.currentTarget).val()
				};
				//Make the select all count as empty
				jQuery('#recordsCount').val('');
				//Make total number of pages as empty
				jQuery('#totalPageCount').text('');
				thisInstance.getListViewRecords(params).done(function (data) {
					thisInstance.updatePagination();
				});
			});
		},
		/*
		 * Function to register the list view row click event
		 */
		registerRowClickEvent: function () {
			var listViewContentDiv = this.getListViewContentContainer();
			listViewContentDiv.on('click', '.listViewEntries', function (e) {
				if ($(e.target).closest('div').hasClass('actions')) return;
				if ($(e.target).is('button') || $(e.target).parent().is('button')) return;
				if ($(e.target).closest('a').hasClass('noLinkBtn')) return;
				if ($(e.target).is('input[type="checkbox"]')) return;
				if ($.contains($(e.currentTarget).find('td:last-child').get(0), e.target)) return;
				if ($.contains($(e.currentTarget).find('td:first-child').get(0), e.target)) return;
				let recordUrl = $(e.currentTarget).find('a.js-edit').attr('href');
				if (typeof recordUrl !== 'undefined') {
					window.location.href = recordUrl;
				}
			});
		},
		getDefaultParams: function () {
			var pageNumber = jQuery('#pageNumber').val();
			var module = app.getModuleName();
			var parent = app.getParentModuleName();
			var params = {
				module: module,
				parent: parent,
				page: pageNumber,
				view: 'List',
				sourceModule: jQuery('#moduleFilter').val()
			};
			return params;
		},
		registerAddNewTemplate: function (container) {
			jQuery('#addButton', container).on('click', function () {
				var selectedModule = jQuery('#moduleFilter option:selected').val();
				var url = jQuery(this).data('url');
				if (selectedModule.length) {
					url += '&source_module=' + selectedModule;
				}
				window.location.href = url;
			});
		},
		registerImportTemplate: function (container) {
			jQuery('#importButton', container).on('click', function () {
				window.location.href = jQuery(this).data('url');
			});
		},
		registerTemplateDelete: function (container) {
			const self = this;
			if (container == undefined) {
				container = self.getListContainer();
			}
			container.find('.templateDelete').on('click', function (e) {
				e.stopPropagation();
				e.preventDefault();
				let deleteId = $(this).closest('tr').data('id');
				app.showConfirmModal({
					title: app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE'),
					confirmedCallback: () => {
						Settings_PDF_List_Js.deleteById(deleteId, false).done(function () {
							self.registerTemplateDelete(container);
						});
					}
				});
			});
		},
		/*
		 * Function which will give you all the list view params
		 */
		getListViewRecords: function (urlParams) {
			var thisInstance = this;
			var aDeferred = jQuery.Deferred();
			this._super(urlParams).done(function (data) {
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
	}
);
