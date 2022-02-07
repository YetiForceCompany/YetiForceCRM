{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Users-VisitPurpose -->
	<form name="VisitPurpose" class="form-horizontal validateForm">
		<input type="hidden" name="module" value="{$MODULE_NAME}" />
		<input type="hidden" name="action" value="VisitPurpose" />
		<div class="modal-body">
			<div class="row">
				<div class="col-md-12">
					<textarea id="visitPurpose" maxlength="501" class="" name="visitPurpose" data-validation-engine="validate[required,maxSize[500]]"></textarea>
				</div>
			</div>
			{if !$CURRENT_USER->isSuperUser()}
				<div class="alert alert-primary" role="alert">
					<span class="mdi mdi-information-outline u-fs-4x mr-2 float-left"></span>
					{\App\Language::translate("LBL_VISIT_PURPOSE_ALERT")}
				</div>
			{/if}
		</div>
	</form>
	<!-- /tpl-Users-VisitPurpose -->
{/strip}
