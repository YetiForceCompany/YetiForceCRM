{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
	<select name="{$FIELD_MODEL->getFieldName()}" class="chzn-select form-control" title="{\App\Language::translate($FIELD_MODEL->get('label'), $MODULE)}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Json::encode($SPECIAL_VALIDATOR)}'{/if} data-selected-value='{$FIELD_MODEL->get('fieldvalue')}' {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if}>
		{if $FIELD_VALUE && empty($PICKLIST_VALUES[$FIELD_VALUE])}
			<optgroup label="{\App\Language::translate('LBL_VALUE_NOT_FOUND')}">
				<option value="{$FIELD_VALUE}" title="{$FIELD_VALUE}" selected>{$FIELD_VALUE}</option>
			</optgroup>
		{/if}
		<optgroup label="{\App\Language::translate($FIELD_MODEL->get('label'), $MODULE)}">
			{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="" {if $FIELD_MODEL->isMandatory() eq true && $FIELD_MODEL->get('fieldvalue') neq ''} disabled{/if}>{\App\Language::translate('LBL_SELECT_OPTION')}</option>{/if}
			{foreach item=VALUE key=KEY from=$FIELD_MODEL->getPicklistValues()}
				{assign var="TRANSLATE" value=\App\Language::translateSingleMod($KEY,'Other.Country')}
				<option value="{$KEY}" title="{$TRANSLATE}" {if $FIELD_VALUE eq $KEY}selected{/if}>{$TRANSLATE}</option>
			{/foreach}
		</optgroup>
	</select>
{/strip}
