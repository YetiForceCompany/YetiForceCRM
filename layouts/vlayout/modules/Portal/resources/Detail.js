/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_Detail_Js("Portal_Detail_Js",{},{
    
    registerAddBookmark : function() {
        jQuery('#addBookmark').click(function() {
            var params = {
                'module' : app.getModuleName(),
                'parent' : app.getParentModuleName(),
                'view' : 'EditAjax'
            };
            Portal_List_Js.editBookmark(params);
        });
    },
    
    registerDetailViewChangeEvent : function() {
        jQuery('#bookmarksDropdown').change(function() {
            var selectedBookmark = jQuery('#bookmarksDropdown').val();
            var progressIndicatorElement = jQuery.progressIndicator({
                'position' : 'html',
                'blockInfo' : {
                    'enabled' : true
                }
            });
            var url = 'index.php?module='+app.getModuleName()+'&view=Detail&record='+selectedBookmark;
            window.location.href = url;
        });
    },
    
    registerEvents : function(){
        this._super();
        this.registerAddBookmark();
        this.registerDetailViewChangeEvent();
    }
});