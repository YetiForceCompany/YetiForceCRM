{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="js-multi-email-item tpl-Base-Edit-Field-MultiEmailValue">
		<div class="input-group mb-1">
			<input type="text" class="js-multi-email form-control"
				value="{$ITEM['e']}"
				placeholder="{\App\Language::translate('LBL_EMAIL_ADRESS', $MODULE)}"
				data-validation-engine="validate[{if $FIELD_MODEL->isMandatory()} required,{/if}funcCall[Vtiger_MultiEmail_Validator_Js.invokeValidation]]" />
			<div class="input-group-append" title="{\App\Language::translate('LBL_CONSENT_TO_SEND', $MODULE)}">
				<span class="js-multi-email-consenticon input-group-text btn btn-light" {if $ITEM['o'] eq 1}style="display:none" {/if}>
					<span class="fas fa-ban"></span>
				</span>
				<span class="js-multi-email-consenticon js-multi-email-consent input-group-text btn btn-light" {if $ITEM['o'] eq 0}style="display:none" {/if}>
					<span class="fas fa-check"></span>
				</span>
			</div>
			<div class="input-group-append" title="{\App\Language::translate('LBL_REMOVE', $MODULE)}">
				<span class="js-multi-email-remove input-group-text btn btn-outline-danger" {if $ITEMS_COUNT lte 1}style="display:none" {/if}>
					<span class="fas fa-times"></span>
				</span>
			</div>
		</div>
	</div>
{/strip}
