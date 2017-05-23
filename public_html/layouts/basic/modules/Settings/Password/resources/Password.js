/* {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} */
var Settings_Password_Js = {
	loadAction: function() {
        jQuery("#big_letters").change(function() {
			Settings_Password_Js.saveConf(jQuery(this).attr('name'), jQuery(this).is(':checked'));
        });
        jQuery("#small_letters").change(function() {
			Settings_Password_Js.saveConf(jQuery(this).attr('name'), jQuery(this).is(':checked'));
        });
        jQuery("#numbers").change(function() {
			Settings_Password_Js.saveConf(jQuery(this).attr('name'), jQuery(this).is(':checked'));
        });
        jQuery("#special").change(function() {
			Settings_Password_Js.saveConf(jQuery(this).attr('name'), jQuery(this).is(':checked'));
        });
        jQuery("#min_length").change(function() {
			Settings_Password_Js.saveConf(jQuery(this).attr('name'), jQuery(this).val());
        });
        jQuery("#max_length").change(function() {
			Settings_Password_Js.saveConf(jQuery(this).attr('name'), jQuery(this).val());
        });		
		jQuery('#min_length').keyup(function () {  
			this.value = this.value.replace(/[^0-9\.]/g,''); 
		});
		jQuery('#max_length').keyup(function () {  
			this.value = this.value.replace(/[^0-9\.]/g,''); 
		});
	},
	saveConf: function( type , vale ) {
        var params = {
			'module' : app.getModuleName(),
			'parent' : app.getParentModuleName(),
			'action': "Save",
            'type': type,
            'vale': vale
        }
        AppConnector.request(params).then(
			function(data) {
				var response = data['result'];
				var params = {
					text: response,
					type: 'info',
					animation: 'show'
				};
				Vtiger_Helper_Js.showPnotify(params);
			},
			function(data, err) {

			}
        );
	},
	registerEvents : function() {
		Settings_Password_Js.loadAction();
	}
}
jQuery(document).ready(function(){
	Settings_Password_Js.registerEvents();
})