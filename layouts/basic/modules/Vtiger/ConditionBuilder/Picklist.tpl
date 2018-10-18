{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="FIELD_INFO" value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	{assign var=PLACE_HOLDER value=($FIELD_MODEL->isEmptyPicklistOptionAllowed() && !($FIELD_MODEL->isMandatory() eq true && $FIELD_VALUE neq ''))}
	<div class="tpl-ConditionBuilder-PickList">
		<select name="{$FIELD_MODEL->getFieldName()}" class="select2 form-control"
				title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
				data-fieldinfo='{$FIELD_INFO|escape}'
				data-selected-value='{$FIELD_VALUE}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}>
			{if !empty($PLACE_HOLDER)}
				<optgroup class="p-0">
					<option value="">{\App\Language::translate('LBL_SELECT_OPTION')}</option>
				</optgroup>
			{/if}
			{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
				<option value="{\App\Purifier::encodeHtml($PICKLIST_NAME)}"
						title="{\App\Purifier::encodeHtml($PICKLIST_NAME)}" {if trim($FIELD_VALUE) eq trim($PICKLIST_NAME)} selected {/if}>{\App\Purifier::encodeHtml($PICKLIST_VALUE)}</option>
			{/foreach}
		</select>
	</div>
{/strip}
