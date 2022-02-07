{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-ConditionBuilder-BaseNoValidation input-group input-group-sm">
		<input class="form-control js-condition-builder-value"
			data-js="val"
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}"
			value="{\App\Purifier::encodeHtml($VALUE)}"
			data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}"
			data-validation-engine="validate[required]"
			autocomplete="off" />
	</div>
{/strip}
