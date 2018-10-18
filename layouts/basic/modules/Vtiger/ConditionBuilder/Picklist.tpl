{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=MODEL value=$FIELD_MODEL}
	{assign var=PICKLIST_VALUES value=$MODEL->getPicklistValues()}
	{assign var=FIELD_VALUE value=$MODEL->getEditViewDisplayValue($MODEL->get('fieldvalue'),$RECORD)}
	<div class="tpl-ConditionBuilder-PickList">
		<select class="select2 form-control conditionBuilderValue"
				title="{\App\Language::translate($MODEL->getFieldLabel(), $MODULE)}"
				multiple="multiple"
		<optgroup class="p-0">
			<option value="">{\App\Language::translate('LBL_SELECT_OPTION')}</option>
		</optgroup>
		{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
			<option value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}"
					title="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" {if trim($FIELD_VALUE) eq trim($PICKLIST_NAME)} selected {/if}>{\App\Purifier::encodeHtml($PICKLIST_VALUE)}</option>
		{/foreach}
		</select>
	</div>
{/strip}
