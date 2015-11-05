{*<!--
/*+***********************************************************************************************************************************
* The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
* in compliance with the License.
* Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
* See the License for the specific language governing rights and limitations under the License.
* The Original Code is YetiForce.
* The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
* All Rights Reserved.
*************************************************************************************************************************************/
-->*}
{strip}
	<div class='editViewContainer'>
		<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
			{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
				<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
			{/if}
			{if !empty($MAPPING_RELATED_FIELD)}
				<input type="hidden" name="mappingRelatedField" value='{Vtiger_Util_Helper::toSafeHTML($MAPPING_RELATED_FIELD)}' />
			{/if}
			{assign var=QUALIFIED_MODULE_NAME value={$MODULE}}
			{assign var=IS_PARENT_EXISTS value=strpos($MODULE,":")}
			{if $IS_PARENT_EXISTS}
				{assign var=SPLITTED_MODULE value=":"|explode:$MODULE}
				<input type="hidden" name="module" value="{$SPLITTED_MODULE[1]}" />
				<input type="hidden" name="parent" value="{$SPLITTED_MODULE[0]}" />
			{else}
				<input type="hidden" name="module" value="{$MODULE}" />
			{/if}
			<input type="hidden" name="action" value="Save" />
			<input type="hidden" name="record" value="{$RECORD_ID}" />
			<input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" />
			<input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" />
			{if $IS_RELATION_OPERATION }
				<input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}" />
				<input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}" />
				<input type="hidden" name="relationOperation" value="{$IS_RELATION_OPERATION}" />
			{/if}
			<div class="contentHeader">
				{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
				<span class="pull-left">
					{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
				</span>
				<span class="pull-right">
					<button class="btn btn-success" type="submit" id="saveBtn1"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
					<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</button>
				</span>
				<div class="clearfix"></div>
			</div>
			{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name="EditViewBlockLevelLoop"}
			{if $BLOCK_FIELDS|@count lte 0}{continue}{/if}
			{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL]}
			{assign var=BLOCKS_HIDE value=$BLOCK->isHideBlock($RECORD_MODEL,$VIEW)}
			{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
			{if $BLOCKS_HIDE}
				<table class="table table-bordered blockContainer showInlineTable">
					<tr>
						<th class="blockHeader" colspan="4">{vtranslate($BLOCK_LABEL, $MODULE)}</th>
					</tr>
					<tr>
						{assign var=COUNTER value=0}
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}

							{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
							{if $FIELD_MODEL->get('uitype') eq '20' || $FIELD_MODEL->get('uitype') eq '19' || $FIELD_MODEL->get('uitype') eq '300'}
								{if $COUNTER eq '1'}
									<td></td><td></td></tr><tr>
									{assign var=COUNTER value=0}
								{/if}
							{/if}
							{if $COUNTER eq 2}
							</tr><tr>
								{assign var=COUNTER value=1}
							{else}
								{assign var=COUNTER value=$COUNTER+1}
							{/if}
							<td class="fieldLabel textAlignRight">
								{if $isReferenceField neq "reference"}<label class="muted pull-right marginRight10px">{/if}
									{if $FIELD_MODEL->isMandatory() eq true && $isReferenceField neq "reference"} <span class="redColor">*</span> {/if}
									{if $isReferenceField eq "reference"}
										{assign var="REFERENCE_LIST" value=$FIELD_MODEL->getReferenceList()}
										{assign var="REFERENCE_LIST_COUNT" value=count($REFERENCE_LIST)}
										{if $REFERENCE_LIST_COUNT > 1}
											{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
											{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
											{if !empty($REFERENCED_MODULE_STRUCT)}
												{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
											{/if}
											{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor referenceMandatory">*</span> {/if}
											<span class="col-xs-10 paddingRightZero pull-right">
												<select id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}_dropDown" class="chzn-select referenceModulesList streched" title="{vtranslate('LBL_RELATED_MODULE_TYPE')}" style="width:140px;">
													<optgroup>
														{foreach key=index item=value from=$REFERENCE_LIST}
															<option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if}>{vtranslate($value, $MODULE)}</option>
														{/foreach}
													</optgroup>
												</select>
											</span>
										{else}
											<label class="muted pull-right marginRight10px">{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}{vtranslate($FIELD_MODEL->get('label'), $MODULE)}</label>
										{/if}
									{else if $FIELD_MODEL->get('uitype') eq "83"}
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) COUNTER=$COUNTER MODULE=$MODULE}
									{else}
										{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
									{/if}
									{if $isReferenceField neq "reference"}</label>{/if}
							</td>
							{if $FIELD_MODEL->get('uitype') neq "83"}
								<td class="fieldValue {$WIDTHTYPE}" {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '20'} colspan="3" {elseif $FIELD_MODEL->get('uitype') eq '300'} colspan="4" {assign var=COUNTER value=$COUNTER+1} {/if}>
									<div class="row">
										<div class="col-md-10">
											{if $FIELD_MODEL->get('uitype') eq "300"}
												<label class="muted">{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}{vtranslate($FIELD_MODEL->get('label'), $MODULE)}</label>
											{/if}
											{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
										</div>
									</div>
								</td>
							{/if}
							{if $BLOCK_FIELDS|@count eq 1 and $FIELD_MODEL->get('uitype') neq "19" and $FIELD_MODEL->get('uitype') neq "20" and $FIELD_MODEL->get('uitype') neq "30" and $FIELD_MODEL->get('uitype') neq '300' and $FIELD_MODEL->get('name') neq "recurringtype"}
								<td class="{$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td>
								{/if}
							{/foreach}
					</tr>
				</table>
				<br>
			{/if}
		{/foreach}
	{/strip}
