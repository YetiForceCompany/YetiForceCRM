/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class('Settings_CustomView_Index_Js', {
}, {
	container: false,
	contents: false,
	initEvants: function (container) {
		var thisInstance = this;
		container.on('click', '.delete', function (e) {
			thisInstance.deleteFilter(e);
		});
		container.on('switchChange.bootstrapSwitch', '.updateField', function (e, state) {
			thisInstance.updateField(e, state);
		});
		container.on('click', '.update,.createFilter', function (e) {
			thisInstance.update(e);
		});
		container.on('change', '#moduleFilter', function (e) {
			thisInstance.registerFilterChange(e);
		});
	},
	update: function (e) {
		var target = $(e.currentTarget);
		var editUrl = target.data('editurl');
		Vtiger_CustomView_Js.loadFilterView(editUrl);
	},
	updateField: function (e) {
		var thisInstance = this;
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		var progress = $.progressIndicator({
			'message': app.vtranslate('JS_SAVE_LOADER_INFO'),
			'blockInfo': {
				'enabled': true
			}
		});
		var params = {
			'cvid': closestTrElement.data('cvid'),
			'mod': closestTrElement.data('mod'),
			'name': target.attr('name'),
			'value': target.prop('checked') ? 1 : 0,
		};
		app.saveAjax('updateField', params).then(function (data) {
			thisInstance.getContainer().find('#moduleFilter').trigger('change');
		});
	},
	deleteFilter: function (e) {
		var thisInstance = this;
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		var progress = $.progressIndicator({
			'message': app.vtranslate('JS_SAVE_LOADER_INFO'),
			'blockInfo': {
				'enabled': true
			}
		});
		app.saveAjax('delete', {
			'cvid': closestTrElement.data('cvid'),
		}).then(function (data) {
			thisInstance.getContainer().find('#moduleFilter').trigger('change');
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
					'containment': 'tbody',
					'revert': true,
					'tolerance': 'pointer',
					'cursor': 'move',
					'helper': function (e, ui) {
						//while dragging helper elements td element will take width as contents width
						//so we are explicitly saying that it has to be same width so that element will not
						//look like disturbed
						ui.children().each(function (index, element) {
							element = jQuery(element);
							element.width(element.width());
						})
						return ui;
					},
					'update': function (e, ui) {
						thisInstance.updateSequence();
					}
				});
			});
		}
	},
	updateSequence: function () {
		var sequences = [];
		this.getContents().find('tbody tr').each(function (n, row) {
			var cvId = jQuery(row).data('cvid');
			sequences.push(cvId);
		});
		app.saveAjax('upadteSequences', sequences).then(function (data) {
			if (data.success) {
				Vtiger_Helper_Js.showPnotify({text: data.result.message, type: 'success'});
			}
		});
	},
	registerFilterChange: function (e) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var progress = $.progressIndicator({
			'message': app.vtranslate('JS_LOADING_PLEASE_WAIT'),
			'blockInfo': {
				'enabled': true
			}
		});
		var params = {
			module: app.getModuleName(),
			view: app.getViewName(),
			parent: app.getParentModuleName(),
			sourceModule: jQuery(e.currentTarget).val()
		}
		AppConnector.requestPjax(params).then(
				function (data) {
					var contents = thisInstance.getContents().html(data);
					app.showBtnSwitch(contents.find('.switchBtn'));
					thisInstance.makeFilterListSortable(contents);
					thisInstance.getContainer().find('.createFilter').data('editurl', contents.find('#addFilterUrl').val());
					progress.progressIndicator({'mode': 'hide'});
					aDeferred.resolve(data);
				},
				function (error) {
					aDeferred.reject();
				}
		);
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
	registerEvents: function () {
		var container = this.getContainer();
		this.initEvants(container);
		this.makeFilterListSortable(container);
	}

});
