{*<!-- {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-ConditionBuilder-DateTimeRange input-group input-group-sm js-datetime-range-field" data-js="daterangepicker">
		<input class="dateTimePickerField form-control js-condition-builder-value"
			data-js="val"
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}"
			value="{\App\Purifier::encodeHtml(implode(',', array_map(['\\App\\Fields\\DateTime', 'formatToDisplay'], explode(',', $VALUE))))}"
			data-calendar-type="range"
			data-date-format="{$USER_MODEL->get('date_format')}"
			data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}"
			data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			autocomplete="off" />
		<div class=" input-group-append">
			<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
				<span class="fas fa-calendar-alt"></span>
			</span>
		</div>
	</div>
{/strip}
