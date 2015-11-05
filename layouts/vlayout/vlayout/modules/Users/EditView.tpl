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
<div class="editViewContainer">
	<form class="form-horizontal recordEditView equalSplit" id="EditView" name="EditView" method="post" enctype="multipart/form-data" action="index.php">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="action" value="Save" />
		<input type="hidden" name="record" value="{$RECORD_ID}" />
		<input type="hidden" name="isPreference" value="{$IS_PREFERENCE}" />
		<input type="hidden" name="timeFormatOptions" data-value='{$DAY_STARTS}' />
		{if $IS_RELATION_OPERATION }
			<input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}" />
			<input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}" />
			<input type="hidden" name="relationOperation" value="{$IS_RELATION_OPERATION}" />
		{/if}
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		<div class="contentHeader row">
		{assign var=SINGLE_MODULE_NAME value='Single_'|cat:$MODULE}
			<span class="col-md-8">
		{if $RECORD_ID neq ''}
			<h3 class="col-md-8 marginLeftZero" title='{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} "{$RECORD_STRUCTURE_MODEL->getRecordName()}"'>{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} "{$RECORD_STRUCTURE_MODEL->getRecordName()}"</h3>
		{else}
			<h3 class="col-md-8 marginLeftZero" title="{vtranslate('LBL_CREATING_NEW', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)}">{vtranslate('LBL_CREATING_NEW', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)}</h3>
		{/if}
			</span>
			<div class="col-md-4">
				<span class="pull-right" style="padding-right: 15px">
					<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
					<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</button>
				</span>
			</div>
		</div>
		<div class="col-md-12">
			{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
				<table class="table table-bordered marginLeftZero">
				{if $BLOCK_FIELDS|@count gt 0}
				<tr class="listViewActionsDiv">
					<th colspan="4">{vtranslate($BLOCK_LABEL, $MODULE)}</th>
				</tr>
				<tr>
				{assign var=COUNTER value=0}
				{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
					{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
					{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
					{assign var="refrenceListCount" value=count($refrenceList)}
					{if $COUNTER eq 2}
						</tr><tr>
						{assign var=COUNTER value=1}
					{else}
						{assign var=COUNTER value=$COUNTER+1}
					{/if}
					<td class="fieldLabel {$WIDTHTYPE}">
					{if {$isReferenceField} eq "reference"}
						{if $refrenceListCount > 1}
							<select style="width: 150px;" class="chzn-select" id="referenceModulesList">
								<optgroup>
									{foreach key=index item=value from=$refrenceList}
										<option value="{$value}">{$value}</option>
									{/foreach}
								</optgroup>
							</select>
						{/if}
						{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
					{else}
						{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
					{/if}
					{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
					</td>
					<td class="fieldValue {$WIDTHTYPE}" {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
						{include file=$FIELD_MODEL->getUITypeModel()->getTemplateName()|@vtemplate_path:$MODULE}
					</td>
				{/foreach}
				</tr>
					{if $BLOCK_LABEL eq 'LBL_CALENDAR_SETTINGS'}
						<tr id="selectUsers" {if $SHARED_TYPE != 'seletedusers'} style="display: none;" {/if}>
							<td class="fieldLabel {$WIDTHTYPE}">
								{vtranslate('Share calendar users', $MODULE)}
							</td>
							<td class="fieldValue {$WIDTHTYPE}">
                                <select class="select2 row" id="selectedUsers" name="sharedusers[]" multiple data-placeholder="{vtranslate('LBL_SELECT_USERS',$MODULE)}">
                                    {foreach key=ID item=USER from=$ALL_USERS}
                                        {if $ID neq $USER_MODEL->get('id')}
                                            <option value="{$ID}" {if array_key_exists($ID, $SHAREDUSERS)} selected {/if}>{$USER->getName()}</option> 
                                        {/if}
                                    {/foreach}	
                                </select>
							</td>
						</tr>
					{/if}
				{/if}
				</table>
            <br>
			{/foreach}

			<div class='pull-right'>
				<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
				<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</button>
			</div>
			<br><br><br>
		</div>
    </form>
</div>
{/strip}
