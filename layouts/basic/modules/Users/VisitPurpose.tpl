{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Users-VisitPurpose -->
	<form name="VisitPurpose" class="form-horizontal validateForm">
		<input type="hidden" name="module" value="{$MODULE_NAME}"/>
		<input type="hidden" name="action" value="VisitPurpose"/>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-12">
					<textarea id="visitPurpose" maxlength="501" class="" name="visitPurpose" data-validation-engine="validate[required,maxSize[500]]"></textarea>
				</div>
			</div>
			<div class="alert alert-primary" role="alert">
				<span class="mdi mdi-information-outline u-fs-4x mr-2 float-left"></span>
				{\App\Language::translate("LBL_VISIT_PURPOSE_ALERT")}
			</div>
			{if \App\Security\AdminAccess::isPermitted('AdminAccess')}
				<a href="index.php?parent=Settings&module=AdminAccess&view=Index&tab=visitPurpose" class="btn btn-primary mr-2">
					<span class="ayfi yfi-admin-access mr-2"></span>
					{App\Language::translate('LBL_ADMIN_ACCESS','Settings::AdminAccess')} > {App\Language::translate('LBL_HISTORY_ADMINS_VISIT_PURPOSE','Settings::AdminAccess')}
				</a>
			{/if}
		</div>
	</form>
<!-- /tpl-Users-VisitPurpose -->
{/strip}
