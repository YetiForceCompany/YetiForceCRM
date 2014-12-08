/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_List_Js("Rss_List_Js",{},
{ 
    /**
     * Function get the height of the document
     * @return <integer> height
     */
    getDocumentHeight : function() {
        return jQuery(document).height();
    },
    
    registerRssAddButtonClickEvent : function() {
        var thisInstance = this;
        jQuery(document).on('click', '.rssAddButton',function(e) {
            thisInstance.showRssAddForm();
        })
    },
    
    /**
     * Function show rssAddForm model
     */
    showRssAddForm : function() {
        var thisInstance = this;
        var progressInstance = jQuery.progressIndicator();
        thisInstance.getRssAddFormUi().then(function(data) {
            var resetPasswordUi = jQuery('.rssAddFormContainer').find('#rssAddFormUi');
            if(resetPasswordUi.length > 0){
                resetPasswordUi = resetPasswordUi.clone(true,true);
                progressInstance.hide();
                var callBackFunction = function(data) {
                    var params = app.validationEngineOptions;
                    var form = data.find('#rssAddForm');
                    params.onValidationComplete = function(form, valid){
                        if(valid) {
                            thisInstance.rssFeedSave(form);
                        }
                        return false;
                    }
                    form.validationEngine(params);
                }
                var modalWindowParams = {
                        data : resetPasswordUi,
                        cb : callBackFunction
                }
                app.showModalWindow(modalWindowParams);
            }
        });
    },
    
    /**
     * Function to get the rss add form
     * @param <string> url
     */
    getRssAddFormUi : function(url) {
        var aDeferred = jQuery.Deferred();
        var resetPasswordContainer = jQuery('.rssAddFormContainer');
        var resetPasswordUi = resetPasswordContainer.find('#rssAddFormUi');
        if(resetPasswordUi.length == 0) {
            var actionParams = {
                    'module' : app.getModuleName(),
                    'view' : 'ViewTypes',
                    'mode' : 'getRssAddForm'
            };
            AppConnector.request(actionParams).then(
                function(data){
                    resetPasswordContainer.html(data);
                    aDeferred.resolve(data);
                },
                function(textStatus, errorThrown){
                    aDeferred.reject(textStatus, errorThrown);
                }
            );
        } else {
            aDeferred.resolve();
        }
        return aDeferred.promise();
    },
    
    /**
     * Function to save rss feed
     * @parm form
     */
    rssFeedSave : function(form) {
        var thisInstance = this;
        var data = form.serializeFormData();
        var progressIndicatorElement = jQuery.progressIndicator({
            'position' : 'html',
            'blockInfo' : {
                'enabled' : true
            }
        });
        var params = {
        'module': app.getModuleName(),
        'action' : 'Save',
        'feedurl' : data.feedurl
        }
        AppConnector.request(params).then(
            function(result) {
                progressIndicatorElement.progressIndicator({
                    'mode' : 'hide'
                });
                if(result.result.success){
                    app.hideModalWindow();
                    thisInstance.getRssFeeds(result.result.id).then(function() {
                        thisInstance.loadRssWidget().then(function() { 
                            var params = {
                                    title : app.vtranslate('JS_MESSAGE'),
                                    text: app.vtranslate(result.result.message),
                                    animation: 'show',
                                    type: 'info'
                            };
                            Vtiger_Helper_Js.showPnotify(params);
                        });
                    });
                } else {
                    var params = {
                                title : app.vtranslate('JS_MESSAGE'),
                                text: app.vtranslate(result.result.message),
                                animation: 'show'
                        };
                    Vtiger_Helper_Js.showPnotify(params);
                }
            }
        );
    },
    
    /**
     * Function to register click on the rss sidebar link
     */
    registerRssUrlClickEvent : function() {
        var thisInstance = this;
        jQuery('.quickWidgetContainer').on('click','.rssLink', function(e) {
            var element = jQuery(e.currentTarget);
            var id = element.data('id');
            thisInstance.getRssFeeds(id);
        });
    },
    
    /**
     * Function to get the feeds for specific id
     * @param <integer> id
     */
    getRssFeeds : function(id) {
        var thisInstance = this;
        var aDeferred = jQuery.Deferred();
        var container = thisInstance.getListViewContainer();
        var progressIndicatorElement = jQuery.progressIndicator({
            'position' : 'html',
            'blockInfo' : {
                'enabled' : true
            }
        });
        var params = {
            'module' : app.getModuleName(),
            'view'   : 'List',
            'id'     : id
        }
        AppConnector.requestPjax(params).then(function(data) {
            aDeferred.resolve(data);
            container.find('#listViewContents').html(data);
            thisInstance.setFeedContainerHeight(container);
            progressIndicatorElement.progressIndicator({
                'mode' : 'hide'
            })
        });
        
        return aDeferred.promise();  
    }, 
    
    /**
     * Function to get the height of the Feed Container 
     * @param container
     */
    setFeedContainerHeight : function(container) {
        var height = this.getDocumentHeight()/4;
        container.find('.feedListContainer').height(height);
    },
    
    /**
     * Function to register the click of feeds
     * @param container
     */
    registerFeedClickEvent : function(container) {
        var thisInstance = this;
        container.on('click' , '.feedLink', function(e) {
            var element = jQuery(e.currentTarget);
            var url = element.data('url');
            var frameElement = thisInstance.getFrameElement(url)
            container.find('.feedFrame').html(frameElement);
        });
    },
    
    /**
     * Function to get the iframe element
     * @param <string> url
     * @retrun <element> frameElement
     */
    getFrameElement : function(url) {
        var progressIndicatorElement = jQuery.progressIndicator({
            'position' : 'html',
            'blockInfo' : {
                'enabled' : true
            }
        });
        var frameElement = jQuery('<iframe>', {
            id:  'feedFrame',
            scrolling: 'auto',
            width: '100%',
            height: this.getDocumentHeight()/2
        });
        frameElement.addClass('table-bordered');
        this.getHtml(url).then(function(html) {
            progressIndicatorElement.progressIndicator({
                'mode' : 'hide'
            });
            var frame = frameElement[0].contentDocument;
            frame.open();
            frame.write(html);
            frame.close();
        });
        
        return frameElement;
    },
    
    /**
     * Function to get the html contents from url
     * @param <string> url
     * @return <string> html contents
     */
    getHtml : function(url) {
        var aDeferred = jQuery.Deferred();
        var params = {
            'module' : app.getModuleName(),
            'action' : 'GetHtml',
            'url'    : url
        }
        AppConnector.request(params).then(function(data) {
            aDeferred.resolve(data.result.html);
        });
        
        return aDeferred.promise();  
    },
    
    /**
     * Function to register record delete event 
     */
    registerDeleteRecordClickEvent: function(){
        var container = this.getListViewContainer();
        var thisInstance = this;
        container.on('click','#deleteButton', function(e) {
            thisInstance.deleteRecord(container);
        })
    },
    
    /**
     * Function to delete the record
     */
    deleteRecord : function(container) {
        var thisInstance = this;
        var recordId = container.find('#recordId').val();
		var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
		Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
			function(e) {
				var module = app.getModuleName();
				var postData = {
					"module": module,
					"action": "DeleteAjax",
					"record": recordId
				}
				var deleteMessage = app.vtranslate('JS_RECORD_GETTING_DELETED');
				var progressIndicatorElement = jQuery.progressIndicator({
					'message' : deleteMessage,
					'position' : 'html',
					'blockInfo' : {
						'enabled' : true
					}
				});
				AppConnector.request(postData).then(
					function(data){
						progressIndicatorElement.progressIndicator({
							'mode' : 'hide'
						})
						if(data.success) {
                            thisInstance.getRssFeeds().then(function() {
                                thisInstance.loadRssWidget();
                            });
						} else {
							var  params = {
								text : app.vtranslate(data.error.message),
								title : app.vtranslate('JS_LBL_PERMISSION')
							}
							Vtiger_Helper_Js.showPnotify(params);
						}
					},
					function(error,err){

					}
				);
			},
			function(error, err){
			}
		);
    },
    
    /**
     * Function to register make default button click event
     */
    registerMakeDefaultClickEvent : function(container) {
        var thisInstance = this;
        container.on('click','#makeDefaultButton',function() {
            thisInstance.makeDefault(container);
        }); 
    },
    
    /**
     * Function to make a record as default rss feed
     */
    makeDefault : function(container) {
        var listInstance = Vtiger_List_Js.getInstance();
        var recordId = container.find('#recordId').val();
        var module = app.getModuleName();
        var postData = {
            "module": module,
            "action": "MakeDefaultAjax",
            "record": recordId
        }
        var progressIndicatorElement = jQuery.progressIndicator({
            'position' : 'html',
            'blockInfo' : {
                'enabled' : true
            }
        });
        AppConnector.request(postData).then(
            function(data){
                progressIndicatorElement.progressIndicator({
                    'mode' : 'hide'
                })
                if(data.success) {
                    var params = {
                        title : app.vtranslate('JS_MESSAGE'),
                        text: app.vtranslate(result.result.message),
                        animation: 'show',
                        type: 'info'
                    };
                    Vtiger_Helper_Js.showPnotify(params);
                } else {
                    var  params = {
                        text : app.vtranslate(data.error.message),
                        title : app.vtranslate('JS_LBL_PERMISSION')
                    }
                    Vtiger_Helper_Js.showPnotify(params);
                }
            }
        );
    },
    
    loadRssWidget : function () {
        var aDeferred = jQuery.Deferred();
        var widgetContainer = jQuery('.widgetContainer');
        var url = widgetContainer.data('url');
        AppConnector.request(url).then(function(data) {
            aDeferred.resolve(data);
            widgetContainer.html(data);
        });
        return aDeferred.promise();
    },
    
    registerEvents : function() {
        this._super();
        var container = this.getListViewContainer();
        this.registerRssAddButtonClickEvent();
        this.registerRssUrlClickEvent();
        this.registerFeedClickEvent(container);
        this.registerMakeDefaultClickEvent(container);
        this.setFeedContainerHeight(container);
    }
});