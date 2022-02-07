{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-ConditionBuilder-Time input-group input-group-sm time">
		{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
		<input class="clockPicker form-control js-condition-builder-value"
			value="{$FIELD_MODEL->getEditViewDisplayValue($VALUE)}"
			data-js="timepicker" data-format="{$USER_MODEL->get('hour_format')}"
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModule()->getName())}"
			autocomplete="off"
			data-fieldinfo='{$FIELD_INFO}'
			data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" />
		<div class=" input-group-append">
			<span class="input-group-text u-cursor-pointer js-clock__btn" data-js="click">
				<span class="far fa-clock"></span>
			</span>
		</div>
	</div>
{/strip}
