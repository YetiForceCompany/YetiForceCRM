/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("Vtiger_EditFieldByModal_Js", {
	getInstance: function () {
		var module = jQuery('#moduleBasic').val();
		if (typeof module != 'undefined') {
			var moduleClassName = module + "_EditFieldByModal_Js";
		}
		var fallbackClassName = Vtiger_EditFieldByModal_Js;
		if (typeof window[moduleClassName] != 'undefined') {
			var instance = new window[moduleClassName]();
		} else {
			var instance = new fallbackClassName();
		}
		return instance;
	}
}, {
	formElement: false,
	moduleName: false,
	registerEditState: function () {
		var thisInstance = this;
		var form = this.getForm();
		form.find('.editState').on('click', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			currentTarget.closest('.modal').addClass('hide');
			thisInstance.editState(currentTarget);
		});
	},
	editState: function (currentTarget) {
		var thisInstance = this;
		var params = {
			'module': this.moduleName,
			'action': 'EditFieldByModal',
			'param': {
				'record': currentTarget.data('id'),
				'state': currentTarget.data('state'),
				'fieldName': currentTarget.closest('.fieldButton').data('name')
			}
		}
		app.hideModalWindow();
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		AppConnector.request(params).then(
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
						if (viewName == 'DashBoard') {
							var instance = new Vtiger_DashBoard_Js();
							instance.getContainer().find('a[name="drefresh"]').trigger('click');
							progressIndicatorElement.progressIndicator({'mode': 'hide'});
						}
					} else {
						return false;
					}
				},
				function (error) {
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
			this.setForm(jQuery('#modalEditFieldByModal'));
		}
		return this.formElement;
	},
	setForm: function (element) {
		this.formElement = element;
		return this;
	},
	setModule: function (element) {
		this.moduleName = this.getForm().find('.moduleBasic').val();
	},
	registerEvents: function () {
		this.setModule();
		this.registerEditState();
		this.registerHelpInfo();
		this.getForm().find('.convert').each(function () {
			var text = jQuery(this).text();
			jQuery(this).text(text);
		})
	}

});

jQuery(document).ready(function (e) {
	var init = function () {
		var instance = Vtiger_EditFieldByModal_Js.getInstance();
		instance.registerEvents();
	}
	window.setTimeout(init, 10)
})
