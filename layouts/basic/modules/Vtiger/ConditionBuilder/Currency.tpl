{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-ConditionBuilder-Currency input-group input-group-sm">
		{assign var="BASE_CURRENCY" value=\App\Fields\Currency::getDefault()}
		{assign var="SYMBOL_PLACEMENT" value=$USER_MODEL->currency_symbol_placement}
		{if $SYMBOL_PLACEMENT neq '1.0$'}
			<span class="input-group-prepend row">
				<span class="input-group-text">{$BASE_CURRENCY['currency_symbol']}</span>
			</span>
		{/if}
		<input class="form-control js-condition-builder-value"
			data-js="val"
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}"
			value="{\App\Purifier::encodeHtml($VALUE)}"
			data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}"
			data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			autocomplete="off" />
		{if $SYMBOL_PLACEMENT eq '1.0$'}
			<span class="input-group-append row">
				<span class="input-group-text">{$BASE_CURRENCY['currency_symbol']}</span>
			</span>
		{/if}
	</div>
{/strip}
