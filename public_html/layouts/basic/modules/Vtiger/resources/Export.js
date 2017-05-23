/* {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} */
jQuery.Class("Vtiger_Export_Js", {}, {
	exportForm: false,
	getForm: function () {
		if (this.exportForm == false) {
			this.exportForm = jQuery('#exportForm');
		}
		return this.exportForm;
	},
	initEvent: function () {
		var form = this.getForm();
		var xmlTpl = form.find('.xml-tpl');
		form.find('#exportType').on('change', function (e) {
			if (xmlTpl.length) {
				if (jQuery(this).val() == 'xml') {
					xmlTpl.removeClass('hide');
				} else {
					xmlTpl.addClass('hide');
				}
			}
		});
	},
	registerEvents: function () {
		this.initEvent();
	}
})
