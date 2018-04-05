{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{if $FIELD_MODEL->getUIType() eq '120'}
		{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance($MODULE)->getAccessibleUsers('',$FIELD_MODEL->getFieldDataType())}
		{assign var=ALL_ACTIVEGROUP_LIST value=\App\Fields\Owner::getInstance($MODULE)->getAccessibleGroups('',$FIELD_MODEL->getFieldDataType())}
		{assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->getName()}
		{assign var=CURRENT_USER_ID value=$USER_MODEL->get('id')}
		{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
		{if $FIELD_VALUE neq '' }
			{assign var=FIELD_VALUE value=vtlib\Functions::getArrayFromValue($FIELD_VALUE)}
			{assign var=NOT_DISPLAY_LIST value=array_diff_key(array_flip($FIELD_VALUE), $ALL_ACTIVEUSER_LIST, $ALL_ACTIVEGROUP_LIST)}
		{else}
			{assign var=FIELD_VALUE value=[]}
		{/if}
		<div class="tpl-Edit-Field-SharedOwner">
			<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value=""/>
			<select class="select2 form-control {if !empty($NOT_DISPLAY_LIST)}hideSelected{/if} {$ASSIGNED_USER_ID}"
					title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
					data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
					data-name="{$ASSIGNED_USER_ID}" name="{$ASSIGNED_USER_ID}[]" data-fieldinfo='{$FIELD_INFO}'
					multiple {if !empty($SPECIAL_VALIDATOR)} data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if}
					{if AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
					data-ajax-search="1" data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=getOwners&fieldName={$ASSIGNED_USER_ID}" data-minimum-input="{AppConfig::performance('OWNER_MINIMUM_INPUT_LENGTH')}"
					{/if}>
				{if AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
					{foreach item=USER from=$FIELD_VALUE}
						{assign var=OWNER_NAME value=\App\Fields\Owner::getLabel($USER)}
						<option value="{$USER}" data-picklistvalue="{$OWNER_NAME}" selected="selected">
							{\App\Purifier::encodeHtml($OWNER_NAME)}
						</option>
					{/foreach}
				{else}
					<optgroup label="{\App\Language::translate('LBL_USERS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
							<option value="{$OWNER_ID}" data-picklistvalue="{$OWNER_NAME}"
									{foreach item=USER from=$FIELD_VALUE}
								{if $USER eq $OWNER_ID } selected {/if}
									{/foreach}>
								{$OWNER_NAME}
							</option>
						{/foreach}
					</optgroup>
					<optgroup label="{\App\Language::translate('LBL_GROUPS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
							<option value="{$OWNER_ID}" data-picklistvalue="{$OWNER_NAME}"
									{foreach item=GROUP from=$FIELD_VALUE}
								{if $GROUP eq $OWNER_ID } selected {/if}
									{/foreach}>
								{\App\Language::translate($OWNER_NAME, $MODULE)}
							</option>
						{/foreach}
					</optgroup>
					{foreach from=$NOT_DISPLAY_LIST key=OWNER_ID item=OWNER_NAME}
						<option value="{$OWNER_ID}"
								{if in_array(\App\Purifier::encodeHtml($OWNER_NAME), $FIELD_VALUE)}selected{/if}
								disabled class="d-none">{\App\Purifier::encodeHtml($OWNER_NAME)}</option>
					{/foreach}
				{/if}
			</select>
		</div>
	{/if}
{/strip}
