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
			<input type="hidden" name="module" value="{$MODULE}" />
			<input type="hidden" name="action" value="Save" />
			<input type="hidden" name="record" value="{$RECORD_ID}" />
			<input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" />
			<input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" />
			{if $IS_RELATION_OPERATION }
				<input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}" />
				<input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}" />
				<input type="hidden" name="relationOperation" value="{$IS_RELATION_OPERATION}" />
			{/if}
			<div class="contentHeader row">
				{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
				{if $RECORD_ID neq ''}
					<span class="col-md-8 font-x-x-large textOverflowEllipsis" title="{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} {$RECORD_STRUCTURE_MODEL->getRecordName()}">{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} - {$RECORD_STRUCTURE_MODEL->getRecordName()}</span>
				{else}
					<span class="col-md-8 font-x-x-large textOverflowEllipsis">{vtranslate('LBL_CREATING_NEW', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)}</span>
				{/if}
				<span class="pull-right">
					<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
					<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</button>
				</span>
			</div>
			{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name="EditViewBlockLevelLoop"}
				{if $BLOCK_LABEL eq 'HEADER' OR $BLOCK_LABEL eq 'CONTENT' OR $BLOCK_LABEL eq 'FOOTER'}
					{if $BLOCK_LABEL eq 'HEADER'}
						<div class="">
							<div class="btn-group" data-toggle="buttons">
								<label class="btn btn-primary" data-block="{$BLOCK_LABEL}">
									<input type="checkbox" text="{vtranslate({$BLOCK_LABEL}, {$MODULE})}">{vtranslate({$BLOCK_LABEL}, {$MODULE})}
								</label>
								<label class="btn btn-primary" data-block="CONTENT">
									<input type="checkbox" text="{vtranslate('CONTENT', {$MODULE})}">{vtranslate('CONTENT', {$MODULE})} 
								</label>
								<label class="btn btn-primary" data-block="FOOTER">
									<input type="checkbox" text="{vtranslate('FOOTER', {$MODULE})}">{vtranslate('FOOTER', {$MODULE})}
								</label>
								<label class="btn btn-primary" data-block="CONDITIONS">
									<input type="checkbox" text="{vtranslate('CONDITIONS', {$MODULE})}">{vtranslate('CONDITIONS', {$MODULE})}
								</label>
							</div>
						</div>
					{/if}
					<table class="table table-bordered blockContainer showInlineTable">	
						<tbody>
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
								{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
								{if $FIELD_MODEL->get('uitype') neq "83"}
								<td id="DOC_{$BLOCK_LABEL}" class="{$BLOCK_LABEL} hide fieldValue" {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if} {if $FIELD_MODEL->get('uitype') eq '20'} colspan="3"{/if}>
									{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
								</td>
							{/if}
						{/foreach}
						</tbody>
					</table>
					{if $BLOCK_LABEL eq 'FOOTER'}
						<table class="table table-bordered blockContainer showInlineTable">	
							<tbody>
							<td id="DOC_CONDITIONS" class="hide fieldValue">
								{include file=vtemplate_path('ConditionsEdit.tpl',$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS }
							</td>
							</tbody>
						</table>
					{/if}
				{else}
				{if $BLOCK_FIELDS|@count lte 0 && $BLOCK_LABEL neq 'LBL_FOOTER_HEADER'}{continue}{/if}
				<table class="table table-bordered blockContainer showInlineTable" >
					<tr>
						{if $BLOCK_LABEL eq 'LBL_FOOTER_HEADER'}
							<th class="blockHeader" colspan="8">{vtranslate($BLOCK_LABEL, $MODULE)}</th>
							{else}
							<th class="blockHeader" colspan="4">{vtranslate($BLOCK_LABEL, $MODULE)}</th>
							{/if}
					</tr>
					<tr>
						{assign var=COUNTER value=0}
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}

							{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
							{if $FIELD_MODEL->get('uitype') eq "20" || $FIELD_MODEL->get('uitype') eq "19" || $FIELD_MODEL->get('uitype') eq '300' || $FIELD_MODEL->getName() eq 'filename'}
								{if $COUNTER eq '1'}
									<td class="{$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td>
								</tr>
								<tr>
									{assign var=COUNTER value=0}
								{/if}
							{/if}
							{if $COUNTER eq 2}
							</tr>
							<tr>
								{assign var=COUNTER value=1}
							{else}
								{assign var=COUNTER value=$COUNTER+1}
							{/if}

							{if $FIELD_MODEL->getName() neq 'footer_content' AND $FIELD_MODEL->getName() neq 'content' AND $FIELD_MODEL->getName() neq 'header_content' AND $FIELD_MODEL->getName() neq 'constraints'}
								<td class="fieldLabel">
									<label class="muted pull-right marginRight10px">
										{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
										{if $isReferenceField eq "reference"}
											{assign var="REFERENCE_LIST" value=$FIELD_MODEL->getReferenceList()}
											{assign var="REFERENCE_LIST_COUNT" value=count($REFERENCE_LIST)}
											{if $REFERENCE_LIST_COUNT > 1}
												{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
												{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
												{if !empty($REFERENCED_MODULE_STRUCT)}
													{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
												{/if}
												<select id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}_dropDown" class="chzn-select referenceModulesList streched" style="width:140px;">
													<optgroup>
														{foreach key=index item=value from=$REFERENCE_LIST}
															<option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if}>{vtranslate($value, $MODULE)}</option>
														{/foreach}
													</optgroup>
												</select>
											{else}
												{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
											{/if}
										{else if $FIELD_MODEL->get('uitype') eq "83"}
											{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) COUNTER=$COUNTER MODULE=$MODULE}

										{else}
											{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
										{/if}
									</label>
								</td>

							{/if}

							{if $FIELD_MODEL->get('uitype') neq "83"}
								<td class="fieldValue" {if $FIELD_MODEL->getName() eq 'filename' || $FIELD_MODEL->get('uitype') eq '19' || $FIELD_MODEL->get('uitype') eq '20'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
									<div class="row">
										<div class="col-md-10">
											{if $FIELD_MODEL->getName() eq 'filename'}
												<div class="row">
													<div class="col-md-8">
														{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
													</div>
													<div class="col-md-4">
														<select id="addFieldName" class="form-control" title="{vtranslate('LBL_SELECT_DATE_TYPE', $MODULE)}">
															<option value="">{vtranslate('LBL_SELECT_OPTION', $MODULE)}</option>
															<option value="#dd-mm-yyyy#">{vtranslate('Current date (dd-mm-yyyy)', $MODULE)}</option>
															<option value="#mm-dd-yyyy#">{vtranslate('Current date (mm-dd-yyyy)', $MODULE)}</option>
															<option value="#yyyy-mm-dd#">{vtranslate('Current date (yyyy-mm-dd)', $MODULE)}</option>
														</select>
													</div>
												</div>
											{else}
												{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
											{/if}
										</div>
									</div>
								</td>
							{/if}

							{if $BLOCK_FIELDS|@count eq 1 and $FIELD_MODEL->get('uitype') neq "19" and $FIELD_MODEL->get('uitype') neq "20" and $FIELD_MODEL->get('uitype') neq "30" and $FIELD_MODEL->get('name') neq "recurringtype" && $FIELD_MODEL->getName() neq 'filename' && $BLOCK_LABEL neq 'LBL_FOOTER_HEADER'}<td></td><td></td>{/if}
							{* Additional field *}
							{if $BLOCK_LABEL eq 'LBL_FOOTER_HEADER'}
								<td class="fieldLabel wideWidthType"><label class="muted pull-right marginRight10px">{vtranslate('LBL_DEFAULT_FIELDS', 'OSSPdf')}</label></td>
								<td class="fieldValue wideWidthType">
									<div class="col-md-8 row">
										<select title="{vtranslate('LBL_DEFAULT_FIELDS', 'OSSPdf')}" id='select_default_field' class="form-control">	
											{foreach key=name item=single_field from=$DEFAULT_FIELDS}
												<optgroup label="{$name}">
													{foreach item=field from=$single_field}
														<option value="{$field.name}">{vtranslate($field.label, 'OSSPdf')}</option>
													{/foreach}
												</optgroup>
											{/foreach}
										</select>
									</div>
									<div class="col-md-4 input-group">
										<input type="hidden" value="" id="id1"/><button class="btn btn-info pull-right marginRight10px" data-clipboard-target="id1" id="copy-1"  title="{vtranslate('Field', 'OSSPdf')}"><span class="glyphicon glyphicon-download-alt"></span> </button>
										<input type="hidden" value="" id="id2" /><button class="btn btn-warning pull-right marginRight10px" data-clipboard-target="id2" id="copy-2"  title="{vtranslate('Label', 'OSSPdf')}"><span class="glyphicon glyphicon-download-alt"></span> </button>
									</div>
								</td>
							{/if}
						{/foreach}
						{if $BLOCK_LABEL eq 'LBL_FOOTER_HEADER'}
						<tr id="div_2"></tr>
						<tr id="test"></tr>
					{/if}
				</table>
				<br>
			{/if}		
		{/foreach}
	{/strip}
