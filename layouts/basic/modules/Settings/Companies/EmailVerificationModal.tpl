{*<!-- {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Companies-EmailVerificationModal -->
	<div class="modal-body email-verification-modal">
		<div class="row">
			<div class="col-md-4 text-center py-4 u-max-w-xsm-30 text-center m-auto">
				<img src="{App\Layout::getPublicUrl('layouts/resources/Logo/yetiforce_capterra.png')}"
					alt="Yetiforce Logo" class="w-100">
			</div>
			<div class="col-md-8">
				<form>
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="parent" value="{$PARENT_MODULE}" />
					<input type="hidden" name="action" value="VerifyEmail" />
					<div class="js-send-verification-email" data-js="validationEngine">
						<h5>{App\Language::translate('LBL_EMAIL_VERIFICATION_FORM_HEADER', {$MODULE_NAME})}</h5>
						<p>
							{App\Language::translate('LBL_EMAIL_VERIFICATION_FORM_DESCRIPTION', {$MODULE_NAME})}
						</p>
						<div class="form-group row">
							<div class="input-group col">
								<div class="input-group-prepend">
									<span class="input-group-text">
										<span class="fas fa-envelope"></span>
									</span>
								</div>
								<input
									id="email"
									type="text"
									class="form-control validate[required,custom[email]]"
									name="email"
									placeholder="{App\Language::translate('LBL_EMAIL', $MODULE_NAME)}"
									value="{\App\Purifier::encodeHtml($EMAIL)}">
							</div>
						</div>
						{if $LOCK_EXIT}
							<div class="form-group form-check">
								<input type="checkbox" id="terms_agreement" name="terms_agreement"
									class="form-check-input validate[required]" value="1">
								<label class="form-check-label ml-2" for="terms_agreement">
									<span class="redColor">*</span>&nbsp;
									{assign var="TERMS" value="<a href=\"index.php?module=Dependencies&view=Credits&parent=Settings&displayLicenseModal=YetiForce\" target=\"_blank\">{App\Language::translateArgs('LBL_TERMS_OF_SERVICE', {$MODULE_NAME})}</a>"}
									{assign var="PRIVACY_POLICY" value="<a href=\"https://yetiforce.com/en/privacy-policy.html\" target=\"_blank\">{App\Language::translateArgs('LBL_PRIVACY_POLICY', {$MODULE_NAME})}</a>"}
									{App\Language::translateArgs('LBL_TERMS_AGREEMENT', {$MODULE_NAME}, {$TERMS}, {$PRIVACY_POLICY})}
								</label>
							</div>
						{/if}
						<div class="form-group form-check">
							<input type="checkbox" id="newsletter_agreement" name="newsletter_agreement" class="form-check-input" value="1">
							<label class="form-check-label ml-2" for="newsletter_agreement">
								{App\Language::translate('LBL_NEWSLETTER_AGREEMENT', {$MODULE_NAME})}
							</label>
						</div>
						<div class="modal-footer row">
							{if $LOCK_EXIT}
								<a class="btn btn-danger js-post-action" role="button" href="index.php?module=Users&parent=Settings&action=Logout">
									<span class="fas fa-power-off mr-2"></span><strong>{\App\Language::translate('LBL_SIGN_OUT', $MODULE_NAME)}</strong>
								</a>
							{/if}
							<button type="button" class="js-email-verification-request-modal__save btn btn-success">
								<span class="fas fa-check mr-1"></span>
								{App\Language::translate('LBL_SEND', $MODULE_NAME)}
							</button>
						</div>
					</div>
					<div class="js-check-verification-code" style="display: none;">
						<p>
							{App\Language::translate('LBL_EMAIL_VERIFICATION_FORM_2_DESCRIPTION', {$MODULE_NAME})}
						</p>
						<input
							id="code"
							type="text"
							class="form-control validate[required]"
							name="code"
							placeholder="{App\Language::translate('LBL_VERIFICATION_CODE', $MODULE_NAME)}">
						<div class="modal-footer row">
							<button type="button" class="js-email-verification-modal__back btn btn-secondary">
								<span class="fas fa-arrow-left mr-1"></span>
								{App\Language::translate('LBL_BACK', $MODULE_NAME)}
							</button>
							<button type="button" class="js-email-verification-confirm-modal__save btn btn-success">
								<span class="fas fa-check mr-1"></span>
								{App\Language::translate('LBL_SEND', $MODULE_NAME)}
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-Companies-EmailVerificationModal -->
{/strip}
