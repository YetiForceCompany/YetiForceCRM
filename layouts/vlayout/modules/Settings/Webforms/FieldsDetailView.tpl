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
<div class="contents-topscroll">
<div class="topscroll-div">
	&nbsp;
</div>
</div>
<div class="listViewEntriesDiv contents-bottomscroll">
	<div class="bottomscroll-div">
		<table class="table table-bordered">
			<tr class="blockHeader" colspan=8>
				<th class="blockHeader" colspan="5">
					{vtranslate($SOURCE_MODULE, {$SOURCE_MODULE})} {vtranslate('LBL_FIELD_INFORMATION', {$MODULE_NAME})}
				</th>
			</tr>
			<tr>
				<td class="paddingLeft20"><b>{vtranslate('LBL_MANDATORY', {$MODULE_NAME})}</b></td>
				<td><b>{vtranslate('LBL_HIDDEN', {$MODULE_NAME})}</b></td>
				<td><b>{vtranslate('LBL_FIELD_NAME', {$MODULE_NAME})}</b></td>
				<td><b>{vtranslate('LBL_OVERRIDE_VALUE', {$MODULE_NAME})}</b></td>
				<td><b>{vtranslate('LBL_WEBFORM_REFERENCE_FIELD', {$MODULE_NAME})}</b></td>
			</tr>
			{foreach item=FIELD_MODEL key=FIELD_NAME from=$SELECTED_FIELD_MODELS_LIST}
			{assign var=FIELD_STATUS value="{$FIELD_MODEL->get('required')}"}
			{assign var=FIELD_HIDDEN_STATUS value="{$FIELD_MODEL->get('hidden')}"}
			<tr>
				<td class="paddingLeft20">
					{if ($FIELD_STATUS eq 1) or ($FIELD_MODEL->isMandatory(true))}
						{assign var=FIELD_VALUE value="LBL_YES"}
					{else}
						{assign var=FIELD_VALUE value="LBL_NO"}
					{/if}
					{vtranslate({$FIELD_VALUE}, {$SOURCE_MODULE})}
				</td>
				<td>
					{if $FIELD_HIDDEN_STATUS eq 1}
						{assign var=FIELD_VALUE value="LBL_YES"}
					{else}
						{assign var=FIELD_VALUE value="LBL_NO"}
					{/if}
					{vtranslate({$FIELD_VALUE}, {$SOURCE_MODULE})}
				</td>
				<td>
					{vtranslate($FIELD_MODEL->get('label'), {$SOURCE_MODULE})}
					{if $FIELD_MODEL->isMandatory()}
						<span class="redColor">*</span>
					{/if}
				</td>
				<td>
					{if $FIELD_MODEL->getFieldDataType() eq 'reference'}
						{assign var=EXPLODED_FIELD_VALUE value = 'x'|explode:$FIELD_MODEL->get('defaultvalue')}
						{assign var=FIELD_VALUE value=$EXPLODED_FIELD_VALUE[1]}
						{if !isRecordExists($FIELD_VALUE)}
							{assign var=FIELD_VALUE value=0}
						{/if}
					{else}
						{assign var=FIELD_VALUE value=$FIELD_MODEL->get('defaultvalue')}
					{/if}
					{$FIELD_MODEL->getDisplayValue($FIELD_VALUE, $RECORD->getId(), $RECORD)}
				</td>
				<td>
					{if Settings_Webforms_Record_Model::isCustomField($FIELD_MODEL->get('name'))}
						{vtranslate('LBL_LABEL', $MODULE_NAME)} : {vtranslate($FIELD_MODEL->get('label'), $MODULE_NAME)}
					{else}
						{vtranslate({$FIELD_MODEL->get('neutralizedfield')}, $SOURCE_MODULE)}
					{/if}
				</td>
			</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
</div>
{/strip}