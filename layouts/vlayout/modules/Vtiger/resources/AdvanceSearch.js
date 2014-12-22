/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_BasicSearch_Js("Vtiger_AdvanceSearch_Js",{

	//cache will store the search data
	cache : {}

},{
	//container which will store the search elements
	elementContainer : false,
	//instance which represents advance filter
	advanceFilter : false,

	//states whether the validation is registred for filter elements
	filterValidationRegistered : false,

	//contains the filter form element
	filterForm : false,

	/**
	 * Function which will give the container
	 */
	getContainer : function() {
		return this.elementContainer;
	},

	/**
	 *Function which is used to set the continaer
	 *@params : container - element which represent the container
	 *@return current instance
	 */
	setContainer : function(container) {
		this.elementContainer = container;
		return this;
	},

	getFilterForm : function() {
		return jQuery('form[name="advanceFilterForm"]',this.getContainer());
	},

	/**
	 * Function used to get the advance search ui
	 * @return : deferred promise
	 */
	getAdvanceSearch : function() {
		var aDeferred = jQuery.Deferred();
		var moduleName = app.getModuleName();
		var searchModule = this.getSearchModule();

		//Exists in the cache
		if(searchModule in Vtiger_AdvanceSearch_Js.cache) {
			aDeferred.resolve(Vtiger_AdvanceSearch_Js.cache[searchModule]);
			return aDeferred.promise();
		}
        
        //if you are in settings then module should be vtiger
        if(app.getParentModuleName().length > 0) {
            moduleName = 'Vtiger';
        }

        var searchableModulesParams = {
			"module":moduleName,
			"view"	: "BasicAjax",
			"mode"	: "showAdvancedSearch",
			"source_module": searchModule
        };

		var progressInstance = jQuery.progressIndicator();
		AppConnector.request(searchableModulesParams).then(
			function(data){
				progressInstance.hide();
				//add to cache
				Vtiger_AdvanceSearch_Js.cache[searchModule] = data;
				aDeferred.resolve(data);
			},
			function(error,err){
				aDeferred.reject(error);
			}
		);
		return aDeferred.promise();
	},


	/**
	 * Function which intializes search
	 */
	initiateSearch : function() {
        var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var postLoad = function(uiData) {
			thisInstance.setContainer(jQuery('#advanceSearchContainer'));
			thisInstance.registerEvents();
			thisInstance.advanceFilter = new Vtiger_SearchAdvanceFilter_Js(jQuery('.filterContainer',uiData));
			//align it below the search element
			uiData.closest('.blockMsg').position({
				my: "right top",
				at: "right bottom",
				of: ".searchElement",
				using: function(){
					jQuery("#globalmodal").css({ 'margin-left': '-65px', 'margin-top':'30px'});
				}
			});
            if (jQuery('#searchContainer').height() > 200) {
                app.showScrollBar( jQuery('#searchContainer'), {'height':'400px','railVisible':'true'});
            }
            aDeferred.resolve();
		}

		this.getAdvanceSearch().then(
			function(data){
				var params = {};
				params.data = data ;
				params.cb = postLoad;
				//TODO : put this css as attribute of object so that its easy for maintanace
				params.css = {'width':'50%','text-align':'left','background-color':'transparent','border-width':'0px'};
				//not showing overlay
				params.overlayCss = {'opacity':'0.2'};
				
                app.showModalWindow(params);
			},
			function(error) {
                aDeferred.reject();
			}
		)
        return aDeferred.promise();
	},
    
    getNameFields : function() {
        var form = this.getFilterForm();
        return form.find('[name="labelFields"]').data('value');
    },
    
    selectBasicSearchValue : function() {
      var value = jQuery('#globalSearchValue').val();
      if(value.length > 0 ) {
          var form = this.getFilterForm();
          var labelFieldList = this.getNameFields();
          if(typeof labelFieldList == 'undefined' || labelFieldList.length == 0) {
              return;
          }
          var anyConditionContainer = form.find('.anyConditionContainer');
          for(var index in labelFieldList){
            var labelFieldName = labelFieldList[index];
            if(index !=0 ) {
                //By default one condition exits , only if you have multiple label fields you have add one more condition
                anyConditionContainer.find('.addCondition').find('button').trigger('click');
            }
            var conditionRow = anyConditionContainer.find('.conditionList').find('.conditionRow:last');
            var fieldSelectElemnt = conditionRow.find('select[name="columnname"]');
            fieldSelectElemnt.find('option[data-field-name="'+ labelFieldName +'"]').attr('selected','selected');
            fieldSelectElemnt.trigger('change').trigger('liszt:updated');

            var comparatorSelectElemnt = conditionRow.find('select[name="comparator"]');
            //select the contains value
            comparatorSelectElemnt.find('option[value="c"]').attr('selected','selected');
            comparatorSelectElemnt.trigger('liszt:updated');

            var valueElement = conditionRow.find('[name="value"]');
            valueElement.val(value);
          }
          
      }
    },

	/**
	 * Function which invokes search
	 */
	search : function() {
		var conditionValues = this.advanceFilter.getValues();
		var module = this.getSearchModule();

		var params = {};
		params.module = module;
		params.advfilterlist = JSON.stringify(conditionValues);

		return this._search(params);
	},

	/**
	 * Function which shows search results in proper manner
	 * @params : data to be shown
	 */
	showSearchResults : function(data){
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var postLoad = function(data) {
			var blockMsg = jQuery(data).closest('.blockMsg');
			app.showScrollBar(jQuery(data).find('.contents'));
			aDeferred.resolve(data);
		}

		var unblockcd = function(){
			thisInstance.getContainer().remove();
		}

		var html = '<div class="row-fluid">'+
						'<span class="span4 searchHolder"></span>'+
						'<span class="span8 filterHolder marginLeftZero hide"></span>'+
					'</div>';
		var jQhtml = jQuery(html);
		jQuery('.searchHolder',jQhtml).html(data);

		data = jQhtml;

		var params = {};
		params.data = data;
		params.cb = postLoad;
		params.css = {'width':'20%','text-align':'left','margin-left':'-100px'};
		params.overlayCss = {'opacity':'0.2'};
		params.unblockcb = unblockcd;
		app.showModalWindow(params);

		return aDeferred.promise();
	},

	/**
	 * Function which will save the filter
	 */
	saveFilter : function(params) {
		var aDeferred = jQuery.Deferred();
		params.source_module = this.getSearchModule();
		params.status = 1;
		params.advfilterlist = JSON.stringify(this.advanceFilter.getValues(false));

		params.module = 'CustomView';
		params.action = 'Save';

		AppConnector.request(params).then(function(data){
            if(!data.success) {
                var params = {
                    title : app.vtranslate('JS_MESSAGE'),
                    text: data.error.message,
                    animation: 'show',
                    type: 'error'
                };
				Vtiger_Helper_Js.showPnotify(params);
            }
			aDeferred.resolve(data);
		})
		return aDeferred.promise();
	},

	/**
	 * Function which will save the filter and show the list view of new custom view
	 */
	saveAndViewFilter : function(params){
		this.saveFilter(params).then(
			function(response){
				var url = response['result']['listviewurl'];
				window.location.href=url;
			},
			function(error) {

			}
		);
	},

	/**
	 * Function which specify whether the search component and filter component both are shown
	 */
	isSearchAndFilterComponentsShown : function() {
		var modalData = jQuery('#globalmodal');
		var filterComponent = jQuery('.filterHolder',modalData).find('#advanceSearchContainer');
		if(filterComponent.length <= 0 ) {
			return false;
		}
		return true;
	},

	/**
	 * Function which will perform search and other operaions
	 */
	performSearch : function() {
		var thisInstance = this;
		var isSearchResultsAndFilterShown = this.isSearchAndFilterComponentsShown();
		this.search().then(function(data){
			thisInstance.setContainer(thisInstance.getContainer().detach());
			thisInstance.showSearchResults(data).then(function(modalBlock){
				var msgContainer = modalBlock.closest('.blockMsg');
				msgContainer.position({
					my: "left bottom",
					at: "left bottom",
					of: "#globalSearchValue",
					offset: "1 -29"
				});
				thisInstance.registerShowFiler();
				//if the filter already shown , show again
				if(isSearchResultsAndFilterShown) {
					thisInstance.showFilter();
				}
			});

		});
	},

	/**
	 * Function which will show the advance filter next to search results
	 */
	showFilter : function(){
		var modalData = jQuery('#globalmodal');
		var searchHolder = jQuery('.searchHolder', modalData);
		var filterHolder = jQuery('.filterHolder', modalData);
		filterHolder.removeClass('hide').html(this.getContainer());
		//searchHolder.removeClass('span12').css('width' , '35%');;
		modalData.closest('.blockMsg').css('width' , '70%');
	},

	/**
	 * Function which will perform the validation for the advance filter fields
	 * @return : deferred promise - resolves if validation succeded if not failure
	 */
	performValidation : function() {
		var thisInstance = this;
		this.formValidationDeferred = jQuery.Deferred();
		var controlForm = this.getFilterForm();

		var validationDone = function(form, status){
			if(status) {
				thisInstance.formValidationDeferred.resolve();
			}else{
				thisInstance.formValidationDeferred.reject();
			}
		}
		//To perform validation registration only once
		if(!this.filterValidationRegistered){
			this.filterValidationRegistered = true;
			controlForm.validationEngine({
				'onValidationComplete' : validationDone
			});
		}
		//This will trigger the validation
		controlForm.submit();
		return this.formValidationDeferred.promise();
	},

	/**
	 * Function which will register the show filer invocation
	 */
	registerShowFiler : function() {
		var thisInstance = this;
		jQuery('#showFilter').on('click',function(e){
			thisInstance.showFilter();
		});
	},

	/**
	 * Function which will register events
	 */
	registerEvents : function() {
		var thisInstance = this;
		var container = this.getContainer();

		container.on('change','#searchModuleList', function(e){
			var selectElement = jQuery(e.currentTarget);
			var selectedModuleName = selectElement.val();

			thisInstance.setSearchModule(selectedModuleName);

			thisInstance.initiateSearch().then(function(){
                thisInstance.selectBasicSearchValue();
            });
		});

		jQuery('#advanceSearchButton').on('click', function(e){
			var searchModule = thisInstance.getSearchModule();
			//If no module is selected
			if(searchModule.length <= 0) {
				app.getChosenElementFromSelect(jQuery('#searchModuleList'))
						.validationEngine('showPrompt', app.vtranslate('JS_SELECT_MODULE'), 'error','topRight',true)
				return;
			}
			thisInstance.performValidation().then(
				function(){
					 thisInstance.performSearch();
				},
				function(){

				}
			);
		});

		jQuery('#advanceIntiateSave').on('click', function(e){
			var currentElement = jQuery(e.currentTarget);
			currentElement.addClass('hide');
			var actionsContainer = currentElement.closest('.actions');
			jQuery('input[name="viewname"]',actionsContainer).removeClass('zeroOpacity').focus();
			jQuery('#advanceSave').removeClass('hide');
		});

		jQuery('#advanceSave').on('click',function(e){
			var actionsContainer = jQuery(e.currentTarget).closest('.actions');
			var filterNameField = jQuery('input[name="viewname"]',actionsContainer);
			var value = filterNameField.val();
			if(value.length <= 0) {
				filterNameField.validationEngine('showPrompt', app.vtranslate('JS_REQUIRED_FIELD'), 'error','topRight',true);
				return;
			}

			var searchModule = thisInstance.getSearchModule();
			//If no module is selected
			if(searchModule.length <= 0) {
				app.getChosenElementFromSelect(jQuery('#searchModuleList'))
						.validationEngine('showPrompt', app.vtranslate('JS_SELECT_MODULE'), 'error','topRight',true)
				return;
			}

			thisInstance.performValidation().then(function(){
				var params = {};
				params.viewname = value;
				thisInstance.saveAndViewFilter(params);
			});
		});

		//DO nothing on submit of filter form
		this.getFilterForm().on('submit',function(e){
			e.preventDefault();
		})

		//To set the search module with the currently selected values.
		this.setSearchModule(jQuery('#searchModuleList').val());
	}
})