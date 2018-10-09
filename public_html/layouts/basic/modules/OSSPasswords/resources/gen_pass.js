/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

var PasswordHelper;
PasswordHelper = {
	init() {
		return this;
	},
// Function to generate new password
	passwordStrength(password, translations) {
		if (password == '')
			password = document.getElementById('OSSPasswords_editView_fieldName_password').value;

		var desc = [];
		if (translations == '') {
			desc[0] = app.vtranslate('Very Weak');
			desc[1] = app.vtranslate('Weak');
			desc[2] = app.vtranslate('Better');
			desc[3] = app.vtranslate('Medium');
			desc[4] = app.vtranslate('Strong');
			desc[5] = app.vtranslate('Very Strong');
		} else {
			var tstring = translations.split(',');
			desc[0] = tstring[0];
			desc[1] = tstring[1];
			desc[2] = tstring[2];
			desc[3] = tstring[3];
			desc[4] = tstring[4];
			desc[5] = tstring[5];
		}

		var score = 0;

		//if password bigger than 6 give 1 point
		if (password.length > 6)
			score++;

		//if password has both lower and uppercase characters give 1 point
		if ((password.match(/[a-z]/)) && (password.match(/[A-Z]/)))
			score++;

		//if password has at least one number give 1 point
		if (password.match(/\d+/))
			score++;

		//if password has at least one special caracther give 1 point
		if (password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/))
			score++;

		//if password bigger than 12 give another 1 point
		if (password.length > 12)
			score++;

		// password hidden
		if (password == '') {
			document.getElementById("passwordDescription").innerHTML = app.vtranslate('Enter the password');
			document.getElementById("passwordStrength").className = "input-group-text strength0";
		} else if (password == '**********') {
			document.getElementById("passwordDescription").innerHTML = app.vtranslate('Password is hidden');
			document.getElementById("passwordStrength").className = "input-group-text strength0";
		} else {
			document.getElementById("passwordDescription").innerHTML = desc[score];
			document.getElementById("passwordStrength").className = "input-group-text strength" + score;
		}
	},
	showPassword(record) {
		let showPassText = app.vtranslate('LBL_ShowPassword');
		let hidePassText = app.vtranslate('LBL_HidePassword');

		if ($('#show-btn').text() == showPassText) {
			var params = {
				'module': "OSSPasswords",
				'action': "GetPass",
				'record': record
			};

			AppConnector.request(params).done(function (data) {
				var response = data['result'];
				if (response['success']) {
					var el = document.getElementById("OSSPasswords_editView_fieldName_password");
					el.value = response['password'];
					el.onchange();
					$('#copy-button').removeClass('d-none').show();
				}
			});

			// validate password
			this.passwordStrength('', '');

			// change buttons label
			$('#show-btn').text(hidePassText);
		} else {
			document.getElementById("OSSPasswords_editView_fieldName_password").value = '**********';
			$('#show-btn').text(showPassText);
			this.passwordStrength('', '');
			$('#copy-button').hide();
		}
	},
	showDetailsPassword(record) {
		var showPassText = app.vtranslate('LBL_ShowPassword');
		var hidePassText = app.vtranslate('LBL_HidePassword');

		if ($('#show-btn').text() == showPassText) {
			var params = {
				'module': "OSSPasswords",
				'action': "GetPass",
				'record': record
			};

			AppConnector.request(params).done(function (data) {
				var response = data['result'];
				if (response['success']) {
					var el = document.getElementById("detailPassword");
					el.innerHTML = response['password'];
					$('#copy-button').removeClass('d-none').show();
				}
			});

			// change buttons label
			$('#show-btn').html('<span class="fas fa-eye-slash u-mr-5px"></span>' + hidePassText);
		} else {
			document.getElementById("detailPassword").innerHTML = '**********';
			$('#show-btn').html('<span class="fas fa-eye u-mr-5px"></span>' + showPassText);
			$('#copy-button').hide();
		}
	},
	showPasswordQuickEdit(record) {
		var hidePassText = app.vtranslate('LBL_HidePassword');

		var params = {
			'module': "OSSPasswords",
			'action': "GetPass",
			'record': record
		};
		AppConnector.request(params).done(function (data) {
			var response = data['result'];
			if (response['success']) {
				var el = document.getElementById("detailPassword");
				el.innerHTML = response['password'];
				$("input[name='password']").val(response['password']);
				$('#copy-button').removeClass('d-none').show();
			}
		});
		// change buttons label
		$('#show-btn').text(hidePassText);
	}
}
$(document).ready(function () {
	PasswordHelper.init();
});