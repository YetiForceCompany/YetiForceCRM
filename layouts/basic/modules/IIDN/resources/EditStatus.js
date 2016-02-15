/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("IIDN_EditStatus_Js", {}, {
	formElement: false,
	moduleName: 'IIDN',
	registerChangeStatus: function () {
		var thisInstance = this;
		jQuery('#modalEditStatus .changeStatus').on('click', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			currentTarget.closest('.modal').addClass('hide');
			thisInstance.updateStatus(currentTarget);
		});
	},
	updateStatus: function (currentTarget) {
		var thisInstance = this;
		var params = {
			'record': currentTarget.data('id'),
			'state': currentTarget.data('state')
		}
		app.hideModalWindow();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		app.saveAjax('updateStatus', params, {'module': this.moduleName}).then(
				function (data) {
					if (data.success) {
						var viewName = app.getViewName();
						if (viewName === 'Detail') {
							if (app.getModuleName() == thisInstance.moduleName) {
								window.location.reload();
							} else {
								Vtiger_Detail_Js.reloadRelatedList();
								progressIndicatorElement.progressIndicator({'mode': 'hide'});
							}
						}
						if (viewName == 'List') {
							var listInstance = new Vtiger_List_Js();
							listInstance.getListViewRecords();
						}
					} else {
						return false;
					}
				}
		);
	},
	registerHelpInfo: function () {
		var form = this.getForm();
		var elemente = app.showPopoverElementView(form.find('.helpInfoPopover'), {trigger: 'click', html: true});
		elemente.trigger('click').trigger('click');
		elemente.on('shown.bs.popover', function (e, i) {
			var element = jQuery(e.currentTarget);
			var popover = element.next();
			app.showScrollBar(popover.find('.popover-content'), {
				height: '300px',
				railVisible: true,
			});
		});
	},
	getForm: function () {
		if (this.formElement == false) {
			this.setForm(jQuery('#modalEditStatus'));
		}
		return this.formElement;
	},
	setForm: function (element) {
		this.formElement = element;
		return this;
	},
	registerEvents: function () {
		this.registerChangeStatus();
		this.registerHelpInfo();
		this.getForm().find('.convert').each(function () {
			var text = jQuery(this).text();
			jQuery(this).text(text);
		})
	}

});

jQuery(document).ready(function (e) {
	var instance = new IIDN_EditStatus_Js();
	instance.registerEvents();
})
