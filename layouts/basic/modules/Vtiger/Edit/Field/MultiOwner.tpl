{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{if $FIELD_MODEL->getUIType() eq '54'}
		{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
		{assign var=ALL_ACTIVEGROUP_LIST value=\App\Fields\Owner::getInstance()->getAccessibleGroups()}
		{assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->getName()}
		{assign var=CURRENT_USER_ID value=$USER_MODEL->get('id')}
		{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
		<div class="tpl-Edit-Field-MultiOwner">
			<select class="select2 form-control {$ASSIGNED_USER_ID}" tabindex="{$FIELD_MODEL->getTabIndex()}"
				data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
				data-name="{$ASSIGNED_USER_ID}" name="{$ASSIGNED_USER_ID}[]" data-fieldinfo='{$FIELD_INFO}'
				{if !empty($SPECIAL_VALIDATOR)}data-validator="{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}" {/if} multiple>
				<optgroup label="{\App\Language::translate('LBL_USERS')}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
						<option value="{$OWNER_ID}"
							data-picklistvalue='{$OWNER_NAME}' {foreach item=USER from=$FIELD_VALUE}
								{if $USER eq $OWNER_ID } selected
								{/if}
							{/foreach}
							data-userId="{$CURRENT_USER_ID}">
							{\App\Purifier::encodeHtml($OWNER_NAME)}
						</option>
					{/foreach}
				</optgroup>
				<optgroup label="{\App\Language::translate('LBL_GROUPS')}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
						<option value="{$OWNER_ID}"
							data-picklistvalue='{$OWNER_NAME}' {foreach item=USER from=$FIELD_VALUE}
								{if $USER eq $OWNER_ID } selected
								{/if}
							{/foreach}>
							{\App\Purifier::encodeHtml($OWNER_NAME)}
						</option>
					{/foreach}
				</optgroup>
			</select>
		{/if}
	</div>
{/strip}
