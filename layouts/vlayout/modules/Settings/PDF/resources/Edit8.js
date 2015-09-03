/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Settings_PDF_Edit_Js("Settings_PDF_Edit8_Js", {}, {
	step8Container: false,
	advanceFilterInstance: false,
	ckEditorInstance: false,
	fieldValueMap: false,
	init: function () {
		this.initialize();
	},
	/**
	 * Function to get the container which holds all the reports step1 elements
	 * @return jQuery object
	 */
	getContainer: function () {
		return this.step8Container;
	},
	/**
	 * Function to set the reports step1 container
	 * @params : element - which represents the reports step1 container
	 * @return : current instance
	 */
	setContainer: function (element) {
		this.step8Container = element;
		return this;
	},
	/**
	 * Function  to intialize the reports step1
	 */
	initialize: function (container) {
		if (typeof container == 'undefined') {
			container = jQuery('#pdf_step8');
		}
		if (container.is('#pdf_step8')) {
			this.setContainer(container);
		} else {
			this.setContainer(jQuery('#pdf_step8'));
		}
	},
	
	submit : function(){
		var aDeferred = jQuery.Deferred();
		var form = this.getContainer();
		var formData = form.serializeFormData();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});
		AppConnector.request(formData).then(
			function(data) {
				form.hide();
				if(data.result) {
					Settings_Vtiger_Index_Js.showMessage({text : app.vtranslate('JS_PDF_SAVED_SUCCESSFULLY')});
					var pdfRecordElement = jQuery('[name="record"]',form);
					if(pdfRecordElement.val() === '') {
						pdfRecordElement.val(data.result.id);
					}
					setTimeout(function() {
						window.location.href = "index.php?module=PDF&parent=Settings&page=1&view=List";
						progressIndicatorElement.progressIndicator({
							'mode' : 'hide'
						});
					}, 1000);
				}
			},
			function(error,err){

			}
		);
		return aDeferred.promise();
	},

	registerEvents: function () {
		var container = this.getContainer();
		app.changeSelectElementView(container);
	}
});
