{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}

{strip}
	<div class="form-group mr-1 mb-2 js-multi-email-row-{counter}">
		<label for="staticEmail2" class="sr-only">Email</label>
		<div class="input-group">
			<div class="input-group-prepend">
				<button class="btn btn-outline-danger border js-remove-item" data-js="click" type="button">
					<span class="fas fa-times"></span>
				</button>
			</div>

			<input type="text" class="form-control"
				   name="{$FIELD_MODEL->getFieldName()}_tmp"
				   placeholder="{\App\Language::translate('LBL_EMAIL_ADRESS', $MODULE)}"
				   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_MultiEmail_Validator_Js.invokeValidation]]"
				   value="{$ITEM['e']}"
				   aria-label="{\App\Language::translate('LBL_REMOVE', $MODULE)}"/>
			<div class="input-group-append btn-group-toggle" data-js="click" data-toggle="buttons">
				<label class="btn btn-outline-default border {if !empty($ITEM['o']) && $ITEM['o'] }active{/if}">
					<div class="c-float-label__container"
						 title="{\App\Language::translate('LBL_CONSENT_TO_SEND', $MODULE)}">
						<div class="c-float-label__hidden-ph">
							Zgoda
						</div>
						<input id="Opted out" type="checkbox" autocomplete="off"
							   {if !empty($ITEM['o']) && $ITEM['o'] }checked="checked"{/if} />
						<span class="far {if !empty($ITEM['o']) && $ITEM['o'] }fa-check-square{else}fa-square{/if}  position-absolute"
							  title="{\App\Language::translate('LBL_CONSENT_TO_SEND', $MODULE)}"></span>
						<label class="c-float-label__label" for="Opted out">
							Zgoda
						</label>
					</div>
				</label>
			</div>

		</div>
	</div>
{/strip}

