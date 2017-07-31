/* {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} */
function DataAccessSave() {
    this.displayInfo = false;
    this.checkAllDoc = function(form, view) {
        var thisInstance = this;
        jQuery(form).on('click', function() {
            thisInstance.displayInfo = false;
            var requestParams = {},
            recordId = jQuery("#recordId").val();
            if ('Edit' == view) {
                recordId = jQuery('[name="record"]').val();
            }
            requestParams.data = {
                module: "DataAccess",
                action: "CheckDoc",
                rel_module: app.getModuleName(),
                record: recordId,
                form: form.serializeArray(),
            }
            requestParams.async = false;
            AppConnector.request(requestParams).done(function(data) {
                var json = jQuery.extend({}, data['result']);
                jQuery('#validation_data').text(JSON.stringify(json))
            });
        });
		if ('Edit' == view) {
			jQuery('[type="text"]').on('keyup', function() {
				thisInstance.displayInfo = false;
				var requestParams = {},
				recordId = jQuery("#recordId").val();
				if ('Edit' == view) {
					recordId = jQuery('[name="record"]').val();
				}
				requestParams.data = {
					module: "DataAccess",
					action: "CheckDoc",
					rel_module: app.getModuleName(),
					record: recordId,
					form: form.serializeArray(),
				}
				requestParams.async = false;
				AppConnector.request(requestParams).done(function(data) {
					var json = jQuery.extend({}, data['result']);
					jQuery('#validation_data').text(JSON.stringify(json))
				});
			});
			jQuery('[type="text"]').on('change', function() {
				thisInstance.displayInfo = false;
				var requestParams = {},
				recordId = jQuery("#recordId").val();
				if ('Edit' == view) {
					recordId = jQuery('[name="record"]').val();
				}
				requestParams.data = {
					module: "DataAccess",
					action: "CheckDoc",
					rel_module: app.getModuleName(),
					record: recordId,
					form: form.serializeArray(),
				}
				requestParams.async = false;
				AppConnector.request(requestParams).done(function(data) {
					var json = jQuery.extend({}, data['result']);
					jQuery('#validation_data').text(JSON.stringify(json))
				});
			});
		}

    };
	this.preSaveDetail = function(form) {
		var thisInstance = this;
		form.on('Vtiger.Field.PreSave', function(e) {
			var json = jQuery('#validation_data').text();
			var data = JSON.parse(json);
			 if (data.passCondition) {
				if (!data.condition_allow) {
					var msg = data.tr[0] + '<br /><br />' + data.tr[1] + ': <br />';
					for (var i = 0; i < data.not_attach_doc_list.length; i++) {
						msg += '- ' + data.not_attach_doc_list[i] + '<br />';
					}
					if (!thisInstance.displayInfo) {
						thisInstance.displayInfo = true;
						Vtiger_Helper_Js.showPnotify(msg);
					}
					e.preventDefault();
				}
			}
		})
	};
	this.preSaveEdit = function() {
		var thisInstance = this;
		jQuery(':submit').on('click', function() {
			var json = jQuery('#validation_data').text();
			var data = JSON.parse(json);
			if (data.passCondition) {
				if(!data.condition_allow){
					var msg = data.tr[0] + '<br /><br />' + data.tr[1] + ': <br />';
					for (var i = 0; i < data.not_attach_doc_list.length; i++) {
						msg += '- ' + data.not_attach_doc_list[i];
					}
					if (!thisInstance.displayInfo) {
						Vtiger_Helper_Js.showPnotify(msg);
						thisInstance.displayInfo = true;
					}
					return false;
				}
			}
		})
	};
	this.registerEvents = function() {
		var thisInstance = this;
		var view = app.getViewName();
		var moduleName = app.getModuleName();
		jQuery('body').append('<div id="data_access_save" style="display:none;"></div>');
		if ('Detail' == view) {
			var form = jQuery('#detailView');
			thisInstance.checkAllDoc(form, view);
			thisInstance.preSaveDetail(form);
			
		} else if ('Edit' == view) {
			if (jQuery('[name="record"]').val() != "") {
				var form = jQuery('#EditView');
				thisInstance.checkAllDoc(form, view);
				thisInstance.preSaveEdit();
			}
		}
		var editViewForm = this.getForm();
		form.on('Vtiger.Field.PreSave', function(e) {
			e.preventDefault();
		});
		form.on('Vtiger.Record.PreSave', function(e) {
			e.preventDefault();
		});
		
		//Vtiger_Edit_Js.recordPreSave
	};
}
jQuery(document).ready(function() {
    var dc = new DataAccessSave();
    dc.registerEvents();
});
