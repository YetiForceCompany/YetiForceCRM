{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Calendar-InviteEmail -->
	<form class="js-form" data-js="validationEngine">
		<div class="modal-body">
			<div class="fieldLabel">
				<span class="redColor">*</span> {\App\Language::translate('LBL_PARTICIPANTS_EMAIL', $MODULE_NAME)}:
			</div>
			<div class="fieldValue" >
				<input type="text" class="js-invite-email-input form-control validate[required]" data-validation-engine="validate[required,custom[email],funcCall[Calendar_Edit_Js.checkEmail]]" />
			</div>
		</div>
	</form>
	<!-- /tpl-Calendar-InviteEmail -->
{/strip}
