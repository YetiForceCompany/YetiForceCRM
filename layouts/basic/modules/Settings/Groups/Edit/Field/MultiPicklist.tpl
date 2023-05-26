{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Groups-Edit-Field-MultiPicklist -->
	{if $FIELD_MODEL->getName() eq 'members'}
		{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
		{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
		{assign var=MEMBERS_ALL value=$FIELD_MODEL->getUITypeModel()->getMembersList($RECORD)}
		<div class="row">
			<div class="col-12">
				<ul class="list-inline groupMembersColors mb-1 d-flex flex-nowrap flex-column flex-sm-row">
					{foreach from=array_keys($MEMBERS_ALL) item=GROUP_LABEL}
						<li class="{$GROUP_LABEL} text-center px-4 m-0 list-inline-item w-100">
							{\App\Language::translate($GROUP_LABEL, $QUALIFIED_MODULE)}
						</li>
					{/foreach}
				</ul>
			</div>
			<div class="col-12">
				<select id="{$MODULE}_{$VIEW}_fieldName_{$FIELD_MODEL->getName()}" tabindex="{$FIELD_MODEL->getTabIndex()}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}" multiple class="select2 form-control" name="{$FIELD_MODEL->getFieldName()}[]" data-fieldinfo='{$FIELD_INFO}' {if $FIELD_MODEL->isMandatory() eq true} data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" {/if}{if $FIELD_MODEL->get('dataSelect')} data-select="{$FIELD_MODEL->get('dataSelect')}" {/if}{if $FIELD_MODEL->isEditableReadOnly()} readonly="readonly" {/if}>
					{foreach from=$MEMBERS_ALL key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
						<optgroup label="{\App\Language::translate($GROUP_LABEL)}">
							{foreach from=$ALL_GROUP_MEMBERS key=MEMBER_ID item=MEMBER}
								<option class="{$MEMBER['type']}" value="{$MEMBER_ID}" {if in_array($MEMBER_ID, $FIELD_VALUE)} selected {/if}>{\App\Language::translate($MEMBER['name'])}</option>
							{/foreach}
						</optgroup>
					{/foreach}
				</select>
			</div>
		</div>
	{elseif $FIELD_MODEL->getName() eq 'parentid'}
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
							data-picklistvalue="{$OWNER_NAME}" {if in_array($OWNER_ID, $FIELD_VALUE)} selected="selected" {/if}
							data-userId="{$CURRENT_USER_ID}">
							{$OWNER_NAME}
						</option>
					{/foreach}
				</optgroup>
			{/if}
		{/function}
		<div class="w-100">
			<select class="select2 form-control {$FIELD_NAME}" multiple tabindex="{$FIELD_MODEL->getTabIndex()}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}"
				data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
				data-name="{$FIELD_NAME}" name="{$FIELD_NAME}[]" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}
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
	{else}
		{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), 'Vtiger')}
	{/if}
	<!-- /tpl-Settings-Groups-Edit-Field-MultiPicklist -->
{/strip}
