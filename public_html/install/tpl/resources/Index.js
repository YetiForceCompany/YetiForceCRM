/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 ************************************************************************************/

jQuery.Class(
	'Install_Index_Js',
	{
		fieldsCached: [
			'db_server',
			'db_username',
			'db_name',
			'currency_name',
			'firstname',
			'lastname',
			'admin_email',
			'dateformat',
			'default_timezone'
		],
		checkUsername: function(field, rules, i, options) {
			let fieldValue = field.val(),
				negativeRegex = /^[a-zA-Z0-9_.@]{3,64}$/,
				result = negativeRegex.test(fieldValue);
			if (!result) {
				return app.vtranslate('JS_CONTAINS_ILLEGAL_CHARACTERS');
			}
			let logins = JSON.parse($('#not_allowed_logins').val());
			if ($.inArray(fieldValue, logins) !== -1) {
				return app.vtranslate('LBL_INVALID_USERNAME_ERROR');
			}
		}
	},
	{
		registerEventForStep1: function() {
			jQuery('.bt_install').on('click', function(e) {
				jQuery('input[name="mode"]').val('step2');
				jQuery('form[name="step1"]').submit();
			});
			jQuery('.bt_migrate').on('click', function(e) {
				jQuery('input[name="mode"]').val('mStep0');
				jQuery('form[name="step1"]').submit();
			});
		},
		registerEventForStep2: function() {
			let modalContainer = $('.js-license-modal');
			modalContainer.on('shown.bs.modal', function(e) {
				app.registerDataTables(modalContainer.find('.js-data-table'), {
					lengthMenu: [[10, 25, 50, -1], [10, 25, 50, app.vtranslate('JS_ALL')]],
					retrieve: true
				});
			});
		},
		registerEventForStepChooseHost() {
			$('.js-buy-modal').on('click', e => {
				$.get('Install.php?mode=showBuyModal').done(data => {
					app.showModalWindow(data)
				})
			})
		},
		registerEventForStep3: function() {
			$('#recheck').on('click', function() {
				window.location.reload();
			});
			let elements = jQuery('.js-wrong-status');
			$('.js-confirm').on('submit', function(e) {
				if (elements.length > 0) {
					e.preventDefault();
					app.showConfirmModal(app.vtranslate('LBL_PHP_WARNING')).done(function(data) {
						if (data) {
							elements = false;
							$('form[name="step3"]').submit();
							return;
						}
					});
				}
			});
		},
		checkPwdEvent: function() {
			var thisInstance = this;
			jQuery('input[name="password"]').on('blur', function() {
				thisInstance.checkPwd(jQuery(this).val());
			});
		},
		checkPwd: function(pass) {
			let error = false;

			if (pass.length < 8) {
				jQuery('#passwordError').html(app.vtranslate('LBL_PASS_TO_SHORT'));
				error = true;
			} else if (pass.length > 32) {
				jQuery('#passwordError').html(app.vtranslate('LBL_PASS_TO_LONG'));
				error = true;
			} else if (pass.search(/\d/) == -1) {
				jQuery('#passwordError').html(app.vtranslate('LBL_PASS_NO_NUM'));
				error = true;
			} else if (pass.search(/[A-Z]/) == -1) {
				jQuery('#passwordError').html(app.vtranslate('LBL_PASS_LACK_OF_CAPITAL_LETTERS'));
				error = true;
			} else if (pass.search(/[a-z]/) == -1) {
				jQuery('#passwordError').html(app.vtranslate('LBL_PASS_LACK_OF_LOWERCASE_LETTERS'));
				error = true;
			}

			return error;
		},
		registerEventForStep4: function() {
			var config = JSON.parse(localStorage.getItem('yetiforce_install'));
			Install_Index_Js.fieldsCached.forEach(function(field) {
				if (config && typeof config[field] !== 'undefined') {
					var formField = jQuery('[name="' + field + '"]');
					if ('SELECT' == jQuery(formField).prop('tagName')) {
						jQuery(formField).val(config[field]);
						jQuery(formField).select2('destroy');
						App.Fields.Picklist.showSelect2ElementView(jQuery(formField));
					} else if ('INPUT' == jQuery(formField).prop('tagName') && 'checkbox' == jQuery(formField).attr('type')) {
						if (true == config[field]) {
							jQuery(formField).prop('checked', true);
							jQuery('.config-table tr.d-none').removeClass('d-none');
						}
					} else {
						jQuery(formField).val(config[field]);
					}
				}
			});

			function clearPasswordError() {
				jQuery('#passwordError').html('');
			}

			function setPasswordError() {
				jQuery('#passwordError').html(app.vtranslate('LBL_PASS_REENTER_ERROR'));
			}

			jQuery('input[name="retype_password"]').on('blur', function(e) {
				var element = jQuery(e.currentTarget);
				var password = jQuery('input[name="password"]').val();
				if (password !== element.val()) {
					setPasswordError();
				}
			});

			jQuery('input[name="password"]').on('blur', function(e) {
				var retypePassword = jQuery('input[name="retype_password"]');
				if (retypePassword.val() != '' && retypePassword.val() !== jQuery(e.currentTarget).val()) {
					jQuery('#passwordError').html(app.vtranslate('LBL_PASS_REENTER_ERROR'));
				} else {
					clearPasswordError();
				}
			});

			jQuery('input[name="retype_password"]').on('keypress', function(e) {
				clearPasswordError();
			});
			$('form[name="step4"]').on('submit', e => {
				if (this.checkForm()) {
					e.preventDefault();
				} else {
					$('form[name="step4"]').off('submit');
					this.submitForm();
				}
			});
			this.checkPwdEvent();
		},
		registerEventForStep5: function() {
			jQuery('input[name="step6"]').on('click', function() {
				var error = jQuery('#errorMessage');
				if (error.length) {
					app.showAlert(app.vtranslate('LBL_RESOLVE_ERROR'));
					return false;
				} else {
					jQuery('#progressIndicator').removeClass('d-none');
					jQuery('form[name="step5"]')
						.submit()
						.hide();
				}
			});
		},
		registerEventForStep6: function() {
			var form = $('form[name="step6"]');
			form.on('submit', function() {
				if (form.validationEngine('validate')) {
					form.submit();
					$('.js-submit').attr('disabled', true);
				} else {
					app.formAlignmentAfterValidation(form);
				}
			});
		},
		registerEventForMigration: function() {
			var step = jQuery('input[name="mode"]').val();
			if (step == 'mStep3') {
				jQuery('form').on('submit', function() {
					jQuery('#progressIndicator').show();
					jQuery('#mainContainer').hide();
				});
			}
		},
		checkForm() {
			let error = false;
			if (
				jQuery('#passwordError')
					.html()
					.trim()
			) {
				error = true;
			}
			if (this.checkPwd(jQuery('input[name="password"]').val())) {
				error = true;
			}
			return error;
		},
		submitForm() {
			window.localStorage.setItem(
				'yetiforce_install',
				JSON.stringify({
					db_server: document.step4.db_server.value,
					db_username: document.step4.db_username.value,
					db_name: document.step4.db_name.value,
					currency_name: document.step4.currency_name.value,
					firstname: document.step4.firstname.value,
					lastname: document.step4.lastname.value,
					admin_email: document.step4.admin_email.value,
					dateformat: document.step4.dateformat.value,
					default_timezone: document.step4.default_timezone.value
				})
			);
		},
		changeLanguage: function(e) {
			jQuery('input[name="mode"]').val('step1');
			jQuery('form[name="step1"]').submit();
		},
		registerEvents: function() {
			const form = $('form');
			jQuery('input[name="back"]').on('click', function() {
				window.history.back();
			});
			form.validationEngine(app.validationEngineOptions);
			this.registerEventForStep1();
			this.registerEventForStep2();
			this.registerEventForStep3();
			this.registerEventForStep4();
			this.registerEventForStep5();
			this.registerEventForStep6();
			this.registerEventForMigration();
			if (form.attr('name') === 'step-stepChooseHost') {
				this.registerEventForStepChooseHost();
			}
			$('select[name="lang"]').on('change', this.changeLanguage);
		}
	}
);
jQuery(document).ready(function() {
	var install = new Install_Index_Js();
	install.registerEvents();
});
