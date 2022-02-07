/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_CustomView_Index_Js',
	{},
	{
		container: false,
		contents: false,
		/**
		 * Register events
		 * @param {jQuery} container
		 */
		initEvents: function (container) {
			container.on('click', '.js-delete-filter', (e) => {
				this.deleteFilter(e);
			});
			container.on('change', '.js-update-field', (e) => {
				this.updateField(e);
			});
			container.on('click', '.js-update,.js-create-filter', (e) => {
				this.update(e);
			});
			container.on('change', '.js-module-filter', (e) => {
				this.registerFilterChange(e);
			});
		},
		/**
		 * Load form to edit filter
		 * @param {jQuery.Event} e
		 */
		update: function (e) {
			var target = $(e.currentTarget);
			var editUrl = target.data('editurl');
			new CustomView(editUrl);
		},
		/**
		 * Update parameter
		 * @param {jQuery.Event} e
		 */
		updateField: function (e) {
			const thisInstance = this,
				target = $(e.currentTarget),
				closestTrElement = target.closest('.js-filter-row');
			$.progressIndicator({
				message: app.vtranslate('JS_SAVE_LOADER_INFO'),
				blockInfo: {
					enabled: true
				}
			});
			const params = {
				cvid: closestTrElement.data('cvid'),
				mod: closestTrElement.data('mod'),
				name: target.attr('name'),
				value: target.val()
			};
			app.saveAjax('updateField', {}, params).done(function (data) {
				thisInstance.getContainer().find('.js-module-filter').trigger('change');
			});
		},
		/**
		 * Delete filter
		 * @param {jQuery.Event} e
		 */
		deleteFilter: function (e) {
			var thisInstance = this;
			var target = $(e.currentTarget);
			var closestTrElement = target.closest('.js-filter-row');
			$.progressIndicator({
				message: app.vtranslate('JS_SAVE_LOADER_INFO'),
				blockInfo: {
					enabled: true
				}
			});
			app
				.saveAjax(
					'delete',
					{},
					{
						cvid: closestTrElement.data('cvid')
					}
				)
				.done(function () {
					thisInstance.getContainer().find('.js-module-filter').trigger('change');
				});
		},
		/**
		 * Function to regiser the event to make the filters sortable
		 */
		makeFilterListSortable: function (container) {
			var thisInstance = this;
			var tbody = container.find('tbody');

			if (tbody.children().length > 1) {
				tbody.each(function () {
					jQuery(this).sortable({
						containment: 'tbody',
						revert: true,
						tolerance: 'pointer',
						cursor: 'move',
						helper: function (e, ui) {
							//while dragging helper elements td element will take width as contents width
							//so we are explicitly saying that it has to be same width so that element will not
							//look like disturbed
							ui.children().each(function (index, element) {
								element = jQuery(element);
								element.width(element.width());
							});
							return ui;
						},
						update: function (e, ui) {
							thisInstance.updateSequence();
						}
					});
				});
			}
		},
		/**
		 * Update sequences
		 */
		updateSequence: function () {
			var sequences = [];
			this.getContents()
				.find('.js-filter-row')
				.each(function (n, row) {
					var cvId = $(row).data('cvid');
					sequences.push(cvId);
				});
			app.saveAjax('upadteSequences', sequences).done(function (data) {
				if (data.success) {
					app.showNotify({ text: data.result.message, type: 'success' });
				}
			});
		},
		/**
		 * Load list of filter for module
		 * @param {jQuery.Event} e
		 */
		registerFilterChange: function (e) {
			var thisInstance = this;
			var aDeferred = $.Deferred();
			var progress = $.progressIndicator({
				message: app.vtranslate('JS_LOADING_PLEASE_WAIT'),
				blockInfo: {
					enabled: true
				}
			});
			var params = {
				module: app.getModuleName(),
				view: app.getViewName(),
				parent: app.getParentModuleName(),
				sourceModule: $(e.currentTarget).val()
			};
			AppConnector.requestPjax(params)
				.done(function (data) {
					var contents = thisInstance.getContents().html(data);
					thisInstance.makeFilterListSortable(contents);
					thisInstance
						.getContainer()
						.find('.js-create-filter')
						.data('editurl', contents.find('#js-add-filter-url').val());
					progress.progressIndicator({ mode: 'hide' });
					aDeferred.resolve(data);
				})
				.fail(function (error) {
					aDeferred.reject();
				});
			return aDeferred.promise();
		},
		getContainer: function () {
			if (this.container == false) {
				this.container = jQuery('div.contentsDiv');
			}
			return this.container;
		},
		getContents: function () {
			if (this.contents == false) {
				this.contents = this.getContainer().find('.indexContents');
			}
			return this.contents;
		},
		/**
		 * Main function
		 */
		registerEvents: function () {
			var container = this.getContainer();
			this.initEvents(container);
			this.makeFilterListSortable(container);
		}
	}
);
