{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="form-group mr-1 mb-1 js-multi-email-row-{counter}">
		<label for="staticEmail2" class="sr-only">
			{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}
		</label>
		<div class="input-group">
			<div class="input-group-prepend">
				<button class="btn btn-outline-danger border js-remove-item" data-js="click" type="button"
						id="button-addon1">
					<span class="fas fa-times"></span>
				</button>
			</div>
			<input type="text" class="form-control"
				   name="{$FIELD_MODEL->getFieldName()}_tmp"
				   placeholder="{\App\Language::translate('LBL_EMAIL_ADRESS', $MODULE)}"
				   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_MultiEmail_Validator_Js.invokeValidation]]"
				   value="{$ITEM}"
				   aria-label="{\App\Language::translate('LBL_REMOVE', $MODULE)}"
				   aria-describedby="{\App\Language::translate('LBL_REMOVE', $MODULE)}"/>
			<div class="input-group-append btn-group-toggle" id="button-addon4"
				 data-toggle="buttons">
				<label class="btn btn-outline-primary border">
					<div class="c-float-label__container">
						<div class="c-float-label__hidden-ph">
							{\App\Language::translate('LBL_CONSENT_TO_SEND', $MODULE)}
						</div>
						<input id="Opted out" type="checkbox" autocomplete="off">
						<span class="far fa-square position-absolute"></span>
						<label class="c-float-label__label" for="Opted out">
							{\App\Language::translate('LBL_CONSENT_TO_SEND', $MODULE)}
						</label>
					</div>
				</label>
			</div>
		</div>
	</div>
{/strip}
