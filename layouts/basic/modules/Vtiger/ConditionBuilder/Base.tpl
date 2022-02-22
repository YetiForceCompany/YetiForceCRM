{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-ConditionBuilder-Base -->
	<div class="input-group input-group-sm">
		<input class="form-control js-condition-builder-value" value="{\App\Purifier::encodeHtml($VALUE)}" autocomplete="off"
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}"
			data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}"
			data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			data-js="value" />
	</div>
	<!-- /tpl-Base-ConditionBuilder-Base -->
{/strip}
