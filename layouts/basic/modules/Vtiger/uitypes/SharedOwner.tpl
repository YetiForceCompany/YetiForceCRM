{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{if $FIELD_MODEL->get('uitype') eq '120'}
		{assign var=ALL_ACTIVEUSER_LIST value=$USER_MODEL->getAccessibleUsers('', $MODULE, $FIELD_MODEL->getFieldDataType())}
		{assign var=ALL_ACTIVEGROUP_LIST value=$USER_MODEL->getAccessibleGroups('', $MODULE, $FIELD_MODEL->getFieldDataType())}
		{assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->get('name')}
		{assign var=CURRENT_USER_ID value=$USER_MODEL->get('id')}

		{if $RECORD && $RECORD->get('mode') eq 'fromMapping'}
			{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
		{elseif $MODE neq 'massedit' && $RECORD}
			{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue('', $RECORD->getId())}
		{/if}
		
		{if $FIELD_VALUE neq '' }
			{assign var=FIELD_VALUE value=Vtiger_Functions::getArrayFromValue($FIELD_VALUE)}
			{assign var=NOT_DISPLAY_LIST value=array_diff_key(array_flip($FIELD_VALUE), $ALL_ACTIVEUSER_LIST, $ALL_ACTIVEGROUP_LIST)}
		{else}
			{assign var=FIELD_VALUE value=[]}
		{/if}

		<select class="chzn-select form-control {if !empty($NOT_DISPLAY_LIST)}hideSelected{/if} {$ASSIGNED_USER_ID}" title="{vtranslate($FIELD_MODEL->get('label'), $MODULE)}" data-validNOT_DISPLAY_LISTation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-name="{$ASSIGNED_USER_ID}" name="{$ASSIGNED_USER_ID}[]" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if} multiple>
			<optgroup label="{vtranslate('LBL_USERS')}">
				{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
					<option value="{$OWNER_ID}" data-picklistvalue="{$OWNER_NAME}" 
							{foreach item=USER from=$FIELD_VALUE}
								{if $USER eq $OWNER_ID } selected {/if}
							{/foreach}>
						{$OWNER_NAME}
					</option>
				{/foreach}
			</optgroup>
			<optgroup label="{vtranslate('LBL_GROUPS')}">
				{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
					<option value="{$OWNER_ID}" data-picklistvalue="{$OWNER_NAME}" 
							{foreach item=GROUP from=$FIELD_VALUE}
								{if $GROUP eq $OWNER_ID } selected {/if}
							{/foreach}>
						{vtranslate($OWNER_NAME, $MODULE)}
					</option>
				{/foreach}
			</optgroup>
			{foreach from=$NOT_DISPLAY_LIST key=OWNER_ID item=OWNER_NAME}
				<option value="{$OWNER_ID}" {if in_array(Vtiger_Util_Helper::toSafeHTML($OWNER_NAME), $FIELD_VALUE)}selected{/if} class="hide">{$OWNER_NAME}</option>
			{/foreach}
		</select>
	{/if}
{/strip}
