/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
'use strict';

Vtiger_BasicSearch_Js(
	'Vtiger_AdvanceSearch_Js',
	{
		//cache will store the search data
		cache: {}
	},
	{
		//container which will store the search elements
		elementContainer: false,
		//instance which represents advance filter
		advanceFilter: false,
		//states whether the validation is registred for filter elements
		filterValidationRegistered: false,
		//contains the filter form element
		filterForm: false,
		//container which will store the parent elements
		parentContainer: false,
		/**
		 * Function which will give the container
		 */
		getContainer: function() {
			return this.elementContainer;
		},
		/**
		 *Function which is used to set the continaer
		 *@params : container - element which represent the container
		 *@return current instance
		 */
		setContainer: function(container) {
			this.elementContainer = container;
			return this;
		},
		/**
		 * Function which will give the parent container
		 */
		getParentContainer: function() {
			return this.parentContainer;
		},
		/**
		 *Function which is used to set the continaer
		 *@params : container - element which represent the container
		 *@return current instance
		 */
		setParentContainer: function(container) {
			this.setMainContainer(container);
			this.parentContainer = container;
			return this;
		},
		getFilterForm: function() {
			return $('form[name="advanceFilterForm"]', this.getContainer());
		},
		/**
		 * Function used to get the advance search ui
		 * @return : deferred promise
		 */
		getAdvanceSearch: function() {
			var aDeferred = $.Deferred();
			var searchModule = this.getSearchModule();
			//Exists in the cache
			if (searchModule in Vtiger_AdvanceSearch_Js.cache) {
				aDeferred.resolve(Vtiger_AdvanceSearch_Js.cache[searchModule]);
				return aDeferred.promise();
			}
			var searchableModulesParams = {
				module: app.getModuleName(),
				searchModule: searchModule,
				view: 'BasicAjax',
				mode: 'showAdvancedSearch'
			};
			if (app.getParentModuleName()) {
				searchableModulesParams.parent = app.getParentModuleName();
			}
			var progressInstance = $.progressIndicator();
			AppConnector.request(searchableModulesParams)
				.done(function(data) {
					progressInstance.hide();
					//add to cache
					Vtiger_AdvanceSearch_Js.cache[searchModule] = data;
					aDeferred.resolve(data);
				})
				.fail(function(error, err) {
					aDeferred.reject(error);
				});
			return aDeferred.promise();
		},
		/**
		 * Function which intializes search
		 */
		initiateSearch: function() {
			var aDeferred = $.Deferred();
			var thisInstance = this;
			var postLoad = function(uiData) {
				thisInstance.setContainer($('#advanceSearchContainer'));
				thisInstance.filterValidationRegistered = false;
				thisInstance.registerEvents();
				thisInstance.advanceFilter = new Vtiger_SearchAdvanceFilter_Js($('.filterContainer', uiData));
				aDeferred.resolve();
			};
			thisInstance
				.getAdvanceSearch()
				.done(function(data) {
					var params = {};
					params.data = data;
					params.cb = postLoad;
					app.hideModalWindow();
					app.showModalWindow(params);
				})
				.fail(function(error) {
					aDeferred.reject();
				});
			return aDeferred.promise();
		},
		getNameFields: function() {
			var form = this.getFilterForm();
			return form.find('[name="labelFields"]').data('value');
		},
		selectBasicSearchValue: function() {
			var parent = this.getParentContainer();
			if (parent == false) {
				return false;
			}
			var value = parent.find('.js-global-search__value').val();
			if (value.length > 0) {
				var form = this.getFilterForm();
				var labelFieldList = this.getNameFields();
				if (typeof labelFieldList === 'undefined' || labelFieldList == null || labelFieldList.length == 0) {
					return;
				}
				var anyConditionContainer = form.find('.anyConditionContainer');
				for (var index in labelFieldList) {
					var labelFieldName = labelFieldList[index];
					if (index != 0) {
						//By default one condition exits , only if you have multiple label fields you have add one more condition
						anyConditionContainer
							.find('.addCondition')
							.find('button')
							.trigger('click');
					}
					var conditionRow = anyConditionContainer.find('.conditionList').find('.js-conditions-row:last');
					var fieldSelectElemnt = conditionRow.find('select[name="columnname"]');
					fieldSelectElemnt.find('option[data-field-name="' + labelFieldName + '"]').attr('selected', 'selected');
					fieldSelectElemnt.trigger('change').trigger('change');

					var comparatorSelectElemnt = conditionRow.find('select[name="comparator"]');
					//select the contains value
					comparatorSelectElemnt.find('option[value="c"]').attr('selected', 'selected');
					comparatorSelectElemnt.trigger('change');

					var valueElement = conditionRow.find('[name="value"]');
					valueElement.val(value);
				}
			}
		},
		/**
		 * Function which invokes search
		 */
		search: function() {
			var conditionValues = this.advanceFilter.getValues();
			var module = this.getSearchModule();
			return this._search({
				module: app.getModuleName(),
				searchModule: module,
				advfilterlist: JSON.stringify(conditionValues)
			});
		},
		/**
		 * Function which shows search results in proper manner
		 * @params : data to be shown
		 */
		showSearchResults: function(data) {
			var aDeferred = $.Deferred();
			var postLoad = function(data) {
				//app.showScrollBar($(data).find('.contents'));
				aDeferred.resolve(data);
			};
			var html =
				'<div class="row">' +
				'<span class="col-md-4 searchHolder"></span>' +
				'<span class="col-md-8 filterHolder marginLeftZero d-none"></span>' +
				'</div>';
			var jQhtml = $(html);
			$('.searchHolder', jQhtml).html(data);

			data = jQhtml;

			var params = {};
			params.data = data;
			params.cb = postLoad;
			app.showModalWindow(params);

			return aDeferred.promise();
		},
		/**
		* Remaps values to save search conditions into custom view
		*/
		advSearchToCVFormat: function(values) {
			const cvFilter = {'condition':'AND', 'rules':[]};
			const cvANDConditions = {'condition':'AND', 'rules':[]};
			const cvORConditions = {'condition':'OR', 'rules':[]};
			$.each(values['1']['columns'], function(idx, item) { // loop on AND group
				const condition = {
					'fieldname': app.getModuleName() + ':' + item['columnname'].split(':')[2],
					'operator': item['comparator'],
					'value': item['value']
				}
				cvANDConditions['rules'].push(condition);
				
			});
			$.each(values['2']['columns'], function(idx, item) { // loop on OR group
				const condition = {
					'fieldname': app.getModuleName() + ':' + item['columnname'].split(':')[2],
					'operator': item['comparator'],
					'value': item['value']
				}
				cvORConditions['rules'].push(condition);
			});
			if(cvANDConditions['rules'].length) {
				cvFilter['rules'].push(cvANDConditions);
			}
			if(cvORConditions['rules'].length) {
				cvFilter['rules'].push(cvORConditions);
			}
			return(cvFilter);
		},
		/**
		 * Function which will save the filter
		 */
		saveFilter: function(params) {
			var aDeferred = $.Deferred();
			params.source_module = this.getSearchModule();
			params.status = 1;
			params.advfilterlist = JSON.stringify(this.advSearchToCVFormat(this.advanceFilter.getValues(false)));
			params.module = 'CustomView';
			params.action = 'Save';
			AppConnector.request(params).done(function(data) {
				if (!data.success) {
					var params = {
						title: app.vtranslate('JS_MESSAGE'),
						text: data.error.message,
						type: 'error'
					};
					Vtiger_Helper_Js.showPnotify(params);
				}
				aDeferred.resolve(data);
			});
			return aDeferred.promise();
		},
		/**
		 * Function which will save the filter and show the list view of new custom view
		 */
		saveAndViewFilter: function(params) {
			this.saveFilter(params).done(
				function(response) {
					var url = response['result']['listviewurl'];
					window.location.href = url;
				},
				function(error) {}
			);
		},
		/**
		 * Function which specify whether the search component and filter component both are shown
		 */
		isSearchAndFilterComponentsShown: function() {
			var modalData = $('#' + Window.lastModalId);
			var filterComponent = $('.filterHolder', modalData).find('#advanceSearchContainer');
			if (filterComponent.length <= 0) {
				return false;
			}
			return true;
		},
		/**
		 * Function which will perform search and other operaions
		 */
		performSearch: function() {
			var thisInstance = this;
			var isSearchResultsAndFilterShown = this.isSearchAndFilterComponentsShown();
			this.search().done(function(data) {
				thisInstance.setContainer(thisInstance.getContainer().detach());
				thisInstance.showSearchResults(data).done(function(modalBlock) {
					thisInstance.registerShowFiler();
					//if the filter already shown , show again
					if (isSearchResultsAndFilterShown) {
						thisInstance.showFilter();
					}
				});
			});
		},
		/**
		 * Function which will show the advance filter next to search results
		 */
		showFilter: function() {
			var thisInstance = this;
			var callback = function() {
				app.showModalWindow(thisInstance.getContainer());
			};
			app.hideModalWindow(callback);
		},
		/**
		 * Function which will perform the validation for the advance filter fields
		 * @return : deferred promise - resolves if validation succeded if not failure
		 */
		performValidation: function() {
			var thisInstance = this;
			this.formValidationDeferred = $.Deferred();
			var controlForm = this.getFilterForm();

			var validationDone = function(form, status) {
				if (status) {
					thisInstance.formValidationDeferred.resolve();
				} else {
					thisInstance.formValidationDeferred.reject();
				}
			};
			//To perform validation registration only once
			if (!this.filterValidationRegistered) {
				this.filterValidationRegistered = true;
				controlForm.validationEngine({
					onValidationComplete: validationDone
				});
			}
			//This will trigger the validation
			controlForm.submit();
			return this.formValidationDeferred.promise();
		},
		/**
		 * Function which will register the show filer invocation
		 */
		registerShowFiler: function() {
			var thisInstance = this;
			$('#showFilter').on('click', function(e) {
				thisInstance.showFilter();
			});
		},
		/**
		 * Function which will register events
		 */
		registerEvents: function() {
			var thisInstance = this;
			var container = this.getContainer();

			container.on('change', '#searchModuleList', function(e) {
				var selectElement = $(e.currentTarget);
				var selectedModuleName = selectElement.val();
				thisInstance.setSearchModule(selectedModuleName);
				thisInstance.initiateSearch().done(function() {
					thisInstance.selectBasicSearchValue();
				});
			});

			$('#advanceSearchButton').on('click', function(e) {
				var searchModule = thisInstance.getSearchModule();
				//If no module is selected
				if (searchModule.length <= 0) {
					app
						.getChosenElementFromSelect($('#searchModuleList'))
						.validationEngine('showPrompt', app.vtranslate('JS_SELECT_MODULE'), 'error', 'topRight', true);
					return;
				}
				thisInstance
					.performValidation()
					.done(function() {
						thisInstance.performSearch();
					})
					.fail(function() {});
			});

			$('#advanceIntiateSave').on('click', function(e) {
				var currentElement = $(e.currentTarget);
				currentElement.addClass('d-none');
				var actionsContainer = currentElement.closest('.actions');
				$('.js-name-filter', actionsContainer)
					.removeClass('d-none')
					.focus();
				$('#advanceSave').removeClass('d-none');
			});

			$('#advanceSave').on('click', function(e) {
				var actionsContainer = $(e.currentTarget).closest('.actions');
				var filterNameField = $('input[name="viewname"]', actionsContainer);
				var value = filterNameField.val();
				if (value.length <= 0) {
					filterNameField.validationEngine(
						'showPrompt',
						app.vtranslate('JS_REQUIRED_FIELD'),
						'error',
						'topRight',
						true
					);
					return;
				}

				var searchModule = thisInstance.getSearchModule();
				//If no module is selected
				if (searchModule.length <= 0) {
					app
						.getChosenElementFromSelect($('#searchModuleList'))
						.validationEngine('showPrompt', app.vtranslate('JS_SELECT_MODULE'), 'error', 'topRight', true);
					return;
				}

				thisInstance.performValidation().done(function() {
					var params = {};
					params.viewname = value;
					thisInstance.saveAndViewFilter(params);
				});
			});

			//DO nothing on submit of filter form
			this.getFilterForm().on('submit', function(e) {
				e.preventDefault();
			});

			//To set the search module with the currently selected values.
			this.setSearchModule($('#searchModuleList').val());
		}
	}
);
