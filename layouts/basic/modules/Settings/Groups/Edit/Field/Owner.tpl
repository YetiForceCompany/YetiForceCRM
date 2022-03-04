{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Groups-Edit-Field-Owner -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=OWNERS_ALL value=$FIELD_MODEL->getUITypeModel()->getOwnerList($RECORD)}
	{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
	{assign var=CURRENT_USER_ID value=$USER_MODEL->get('id')}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	{function OPTGRUOP BLOCK_NAME='' OWNERS=[] ACTIVE='inactive'}
		{if $OWNERS}
			<optgroup label="{\App\Language::translate($BLOCK_NAME)}">
				{foreach key=OWNER_ID item=OWNER_NAME from=$OWNERS}
					<option value="{$OWNER_ID}"
						data-picklistvalue="{$OWNER_NAME}" {if $FIELD_VALUE eq $OWNER_ID} selected="selected" {/if}
						data-userId="{$CURRENT_USER_ID}">
						{$OWNER_NAME}
					</option>
				{/foreach}
			</optgroup>
		{/if}
	{/function}
	<div class="w-100">
		<select class="select2 form-control {$FIELD_NAME}" tabindex="{$FIELD_MODEL->getTabIndex()}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}"
			data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			data-name="{$FIELD_NAME}" name="{$FIELD_NAME}" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}
			{if $FIELD_MODEL->isEditableReadOnly() || !$USER_MODEL->isAdminUser()}readonly="readonly" {/if}>
			{if !$FIELD_MODEL->isMandatory()}
				<optgroup class="p-0">
					<option value="0">{\App\Language::translate('LBL_SELECT_OPTION')}</option>
				</optgroup>
			{/if}
			{foreach from=$OWNERS_ALL item=OWNERS key=BLOCK_NAME}
				{OPTGRUOP BLOCK_NAME=$BLOCK_NAME OWNERS=$OWNERS}
			{/foreach}
		</select>
	</div>
	<!-- /tpl-Settings-Groups-Edit-Field-Owner -->
{/strip}
