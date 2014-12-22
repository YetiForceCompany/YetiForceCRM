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
<input type="hidden" name="selectedFieldsData" val=""/>
<input type="hidden" name="mode" value="{$MODE}"/>
<input type="hidden" name="targetModule" value="{$SOURCE_MODULE}"/>
<div class="contents-topscroll">
<div class="topscroll-div">
	&nbsp;
</div>
</div>
<div class="listViewEntriesDiv contents-bottomscroll" style="overflow-x: visible !important;">
	<div class="bottomscroll-div">
		<table class="table table-bordered" width="100%" name="targetModuleFields">
			<colgroup>
				<col style="width:5%;">
				<col style="width:5%;">
				<col style="width:25%;">
				<col style="width:40%;">
				<col style="width:25%;">
			</colgroup>
			<tr class="blockHeader">
				<th class="blockHeader" colspan="5">
					{vtranslate($SOURCE_MODULE, $SOURCE_MODULE)} {vtranslate('LBL_FIELD_INFORMATION', $MODULE)}
				</th>
			</tr>
			<tr>
				<td colspan="5">
					<span class="row-fluid">
						<span class="span1"><span class="pull-right pushDown"><b>{vtranslate('LBL_ADD_FIELDS', $MODULE)}</b></span></span>
						<span class="span9">
							<select id="fieldsList" multiple="multiple" data-placeholder="{vtranslate('LBL_SELECT_FIELDS_OF_TARGET_MODULE', $MODULE)}" class="row-fluid select2" style="width:100%">
								{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$ALL_FIELD_MODELS_LIST name="EditViewBlockLevelLoop"}
									{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
										{assign var="FIELD_INFO" value=json_encode($FIELD_MODEL->getFieldInfo(), 4)}
											<option value="{$FIELD_MODEL->get('name')}" data-field-info='{$FIELD_INFO}' data-mandatory="{($FIELD_MODEL->isMandatory(true) eq 1) ? "true":"false"}"
											{if (array_key_exists($FIELD_MODEL->get('name'), $SELECTED_FIELD_MODELS_LIST)) or ($FIELD_MODEL->isMandatory(true))}selected{/if}>
												{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
												{if $FIELD_MODEL->isMandatory(true)}
													<span class="redColor">*</span>
												{/if}
											</option>
									{/foreach}
								{/foreach}
							</select>
						</span>
						<span class="span2">
							<span class="pull-right">
								<button type="button" id="saveFieldsOrder" class="btn btn-success" disabled="disabled">{vtranslate('LBL_SAVE_FIELDS_ORDER', $MODULE)}</button>
							</span>
						</span>
					</span>
				</td>
			</tr>
			<tr name="fieldHeaders">
				<td class="textAlignCenter"><b>{vtranslate('LBL_MANDATORY', $MODULE)}</b></td>
				<td class="textAlignCenter"><b>{vtranslate('LBL_HIDDEN', $MODULE)}</b></td>
				<td><b>{vtranslate('LBL_FIELD_NAME', $MODULE)}</b></td>
				<td class="textAlignCenter"><b>{vtranslate('LBL_OVERRIDE_VALUE', $MODULE)}</b></td>
				<td><b>{vtranslate('LBL_WEBFORM_REFERENCE_FIELD', $MODULE)}</b></td>
			</tr>

			{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$ALL_FIELD_MODELS_LIST name="EditViewBlockLevelLoop"}
				{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
					{if $FIELD_MODEL->isMandatory(true) || array_key_exists($FIELD_NAME,$SELECTED_FIELD_MODELS_LIST)}
						{if array_key_exists($FIELD_NAME,$SELECTED_FIELD_MODELS_LIST)}
							{assign var=SELECETED_FIELD_MODEL value=$SELECTED_FIELD_MODELS_LIST.$FIELD_NAME}
							{assign var=FIELD_MODEL value=$FIELD_MODEL->set('fieldvalue',$SELECETED_FIELD_MODEL->get('fieldvalue'))}
						{/if}
						<tr data-name="{$FIELD_MODEL->getFieldName()}" class="listViewEntries" data-type="{$FIELD_MODEL->getFieldDataType()}" data-mandatory-field={($FIELD_MODEL->isMandatory(true) eq 1) ? "true":"false"}>
							<td class="textAlignCenter">
								{if !empty($SELECETED_FIELD_MODEL)}
									<input type="hidden" value="{$SELECETED_FIELD_MODEL->get('sequence')}" class="sequenceNumber" name='selectedFieldsData[{$FIELD_NAME}][sequence]'/>
								{else}
									<input type="hidden" value="" class="sequenceNumber" name='selectedFieldsData[{$FIELD_NAME}][sequence]'/>
								{/if}
								<input type="hidden" value="0" name='selectedFieldsData[{$FIELD_NAME}][required]'/>
								<input type="checkbox" {if ($FIELD_MODEL->isMandatory(true) eq 1) or ($SELECETED_FIELD_MODEL->get('required') eq 1)}checked="checked"{/if} 
									   {if $FIELD_MODEL->isMandatory(true) eq 1} readonly="true"{/if} 
									   name='selectedFieldsData[{$FIELD_NAME}][required]' class="markRequired mandatoryField" value="1" />
							</td>
							<td class="textAlignCenter">
								<input type="hidden" value="0" name='selectedFieldsData[{$FIELD_NAME}][hidden]'/>
								<input type="checkbox" {if (!empty($SELECETED_FIELD_MODEL)) and ($SELECETED_FIELD_MODEL->get('hidden') eq 1)} checked="checked"{/if}
								name="selectedFieldsData[{$FIELD_NAME}][hidden]" class="markRequired hiddenField" value="1"/>
							</td>
							<td class="fieldLabel" data-label="{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}{if $FIELD_MODEL->isMandatory(true)}*{/if}">
								{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}{if $FIELD_MODEL->isMandatory(true)}<span class="redColor">*</span>{/if}
							</td>
							{assign var=DATATYPEMARGINLEFT value= array("date","currency","percentage","reference")}
							{assign var=IS_PARENT_EXISTS value=strpos($MODULE,":")}
							{if $IS_PARENT_EXISTS}
								{assign var=SPLITTED_MODULE value=":"|explode:$MODULE}
								{assign var=MODULE value="{$SPLITTED_MODULE[1]}"}
							{/if}
							<td class="fieldValue textAlignCenter" data-name="{$FIELD_MODEL->getFieldName()}" {if in_array($FIELD_MODEL->getFieldDataType(),$DATATYPEMARGINLEFT)} {/if}>
								{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(), $SOURCE_MODULE) BLOCK_FIELDS=$BLOCK_FIELDS MODULE_NAME=$MODULE}
							</td>
							<td>
								{if Settings_Webforms_Record_Model::isCustomField($FIELD_MODEL->get('name'))}
									{vtranslate('LBL_LABEL', $QUALIFIED_MODULE)} : {vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
								{else}
									{vtranslate({$FIELD_MODEL->get('name')}, $SOURCE_MODULE)}
								{/if}
								{if !$FIELD_MODEL->isMandatory(true)}
									<div class="pull-right actions">
										<span class="actionImages"><a class="removeTargetModuleField"><i class="icon-remove-sign"></i></a></span>
									</div>
								{/if}
							</td>
						</tr>
					{/if}
				{/foreach}
			{/foreach}
			</tbody>
		</table>
	</div>
</div>
{/strip}