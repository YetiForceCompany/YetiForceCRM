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
<style>
ul > li.blockHeader {
  padding:5px; 
  cursor: pointer; 
  text-align: center; 
  font-weight: bold; 
  float:left;
  -webkit-border-radius: 10px;
  border-radius: 10px;
}
 li.blockHeader:hover{
  background: white;
  color:black;
}
.blockHeader.active{
  background: white;
  color:black;
}
.active img{
  background: #0065a6;
}
</style>
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
		<div class="contentHeader row-fluid">
		{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
		{if $RECORD_ID neq ''}
			<span class="span8 font-x-x-large textOverflowEllipsis" title="{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} {$RECORD_STRUCTURE_MODEL->getRecordName()}">{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} - {$RECORD_STRUCTURE_MODEL->getRecordName()}</span>
		{else}
			<span class="span8 font-x-x-large textOverflowEllipsis">{vtranslate('LBL_CREATING_NEW', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)}</span>
		{/if}
			<span class="pull-right">
				<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
				<a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
			</span>
		</div>
		{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name="EditViewBlockLevelLoop"}
	{if $BLOCK_LABEL eq 'HEADER' OR $BLOCK_LABEL eq 'CONTENT' OR $BLOCK_LABEL eq 'FOOTER'}
		{if $BLOCK_LABEL eq 'HEADER'}
		<div class="">
			<ul id="tabs" class="nav" style="list-style-type: none;border-radius: 10px;">
				<li class="{$BLOCK_LABEL} blockHeader font" style=" margin:0px 10px 0px 10px;" >
					<img class="cursorPointer alignMiddle blockToggle pngh" alt="{vtranslate('LBL_EXPAND_BLOCK')}" src="{vimage_path('arrowRight.png')}" >
					<img class="cursorPointer alignMiddle blockToggle hide pngs "  alt="{vtranslate('LBL_COLLAPSE_BLOCK')}" src="{vimage_path('arrowDown.png')}" >
					&nbsp;&nbsp;
					{vtranslate({$BLOCK_LABEL}, {$MODULE})} &nbsp;&nbsp;
				</li>
				<li class="CONTENT blockHeader font" style=" margin-right:10px;" >
					<img class="cursorPointer alignMiddle blockToggle pngh" alt="{vtranslate('LBL_EXPAND_BLOCK')}" src="{vimage_path('arrowRight.png')}" >
					<img class="cursorPointer alignMiddle blockToggle hide pngs "  alt="{vtranslate('LBL_COLLAPSE_BLOCK')}"  src="{vimage_path('arrowDown.png')}" >&nbsp;&nbsp;
					{vtranslate('CONTENT', {$MODULE})} &nbsp;&nbsp;
				</li>
				<li class="FOOTER blockHeader font" style=" margin-right:10px;" >
					<img class="cursorPointer alignMiddle blockToggle pngh" alt="{vtranslate('LBL_EXPAND_BLOCK')}" src="{vimage_path('arrowRight.png')}" >
					<img class="cursorPointer alignMiddle blockToggle hide pngs "  alt="{vtranslate('LBL_COLLAPSE_BLOCK')}"  src="{vimage_path('arrowDown.png')}" >
					&nbsp;&nbsp;
					{vtranslate('FOOTER', {$MODULE})} &nbsp;&nbsp;
				</li>
				<li class="CONDITIONS blockHeader font" style="" >
					<img class="cursorPointer alignMiddle blockToggle pngh" alt="{vtranslate('LBL_EXPAND_BLOCK')}" src="{vimage_path('arrowRight.png')}" >
					<img class="cursorPointer alignMiddle blockToggle hide pngs "  alt="{vtranslate('LBL_COLLAPSE_BLOCK')}"  src="{vimage_path('arrowDown.png')}" >
					&nbsp;&nbsp;
					{vtranslate('CONDITIONS', {$MODULE})} &nbsp;&nbsp;
				</li>
			</ul>
		</div>
		{/if}
		<table class="table table-bordered blockContainer showInlineTable">	
			<tbody>
			{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
				{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
				{if $FIELD_MODEL->get('uitype') neq "83"}
					<td id="DOC_{$BLOCK_LABEL}" style="display: none;" class="{$BLOCK_LABEL} fieldValue" {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if} {if $FIELD_MODEL->get('uitype') eq '20'} colspan="3"{/if}>
						{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
					</td>
				{/if}
			{/foreach}
			</tbody>
		</table>
		{if $BLOCK_LABEL eq 'FOOTER'}
			<table class="table table-bordered blockContainer showInlineTable">	
				<tbody>
					<td id="DOC_CONDITIONS" style="display: none;" class=" fieldValue">
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
				{if $FIELD_MODEL->get('uitype') eq "20" || $FIELD_MODEL->get('uitype') eq "19" || $FIELD_MODEL->getName() eq 'filename'}
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
						{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
					{if $FIELD_MODEL->getName() eq 'filename'}
								<select id="addFieldName" style="width:180px;  padding-left:0px;margin-left: 10px;" title="{vtranslate('LBL_SELECT_DATE_TYPE', $MODULE)}">
									<option value="">{vtranslate('LBL_SELECT_OPTION', $MODULE)}</option>
									<option value="#dd-mm-yyyy#">{vtranslate('Current date (dd-mm-yyyy)', $MODULE)}</option>
									<option value="#mm-dd-yyyy#">{vtranslate('Current date (mm-dd-yyyy)', $MODULE)}</option>
									<option value="#yyyy-mm-dd#">{vtranslate('Current date (yyyy-mm-dd)', $MODULE)}</option>
								</select>
					{/if}
					</td>
				{/if}

				{if $BLOCK_FIELDS|@count eq 1 and $FIELD_MODEL->get('uitype') neq "19" and $FIELD_MODEL->get('uitype') neq "20" and $FIELD_MODEL->get('uitype') neq "30" and $FIELD_MODEL->get('name') neq "recurringtype" && $FIELD_MODEL->getName() neq 'filename' && $BLOCK_LABEL neq 'LBL_FOOTER_HEADER'}<td></td><td></td>{/if}
				{if $MODULE eq 'Events' && $BLOCK_LABEL eq 'LBL_EVENT_INFORMATION' && $smarty.foreach.blockfields.last }	
					{include file=vtemplate_path('uitypes/FollowUp.tpl',$MODULE) COUNTER=$COUNTER}
				{/if}
			{* Additional field *}
			{if $BLOCK_LABEL eq 'LBL_FOOTER_HEADER'}
			<td class="fieldLabel wideWidthType"><label class="muted pull-right marginRight10px">{vtranslate('LBL_DEFAULT_FIELDS', 'OSSPdf')}</label></td>
			<td class="fieldValue wideWidthType">
				<select title="{vtranslate('LBL_DEFAULT_FIELDS', 'OSSPdf')}" id='select_default_field' style="width: 200px;">	
					{foreach key=name item=single_field from=$DEFAULT_FIELDS}
							<optgroup label="{$name}">
						{foreach item=field from=$single_field}
							<option value="{$field.name}">{vtranslate($field.label, 'OSSPdf')}</option>
						{/foreach}
							</optgroup>
					{/foreach}
				</select>
				<input type="hidden" value="" id="id1" /><button class="btn btn-info pull-right marginRight10px" data-clipboard-target="id1" id="copy-1"  title="{vtranslate('Field', 'OSSPdf')}"><span class="icon-download-alt"></span> </button>&nbsp;
				<input type="hidden" value="" id="id2" /><button class="btn btn-warning pull-right marginRight10px" data-clipboard-target="id2" id="copy-2"  title="{vtranslate('Label', 'OSSPdf')}"><span class="icon-download-alt"></span> </button>
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
<script type="text/javascript">
    jQuery(function(){
        $('#OSSPdf_editView_fieldName_filename').css('width','400px');
        
        jQuery('#addFieldName').change(function(){
            var fieldName = $('#OSSPdf_editView_fieldName_filename').val();
            $('#OSSPdf_editView_fieldName_filename').val(fieldName+jQuery(this).val());
            
        });

        $('.CONTENT.blockHeader').toggle(function(){
            $('#DOC_CONTENT').show();
            $('li.CONTENT').addClass('active');
            $('li.CONTENT .pngs').show();
            $('li.CONTENT .pngh').hide();
        },function(){
            $('#DOC_CONTENT').hide();
            $('li.CONTENT').removeClass('active');
            $('li.CONTENT .pngs').hide();
            $('li.CONTENT .pngh').show();
        });	
        $('.FOOTER.blockHeader').toggle(function(){
            $('#DOC_FOOTER').show();
            $('li.FOOTER').addClass('active');
            $('li.FOOTER .pngs').show();
            $('li.FOOTER .pngh').hide();
        },function(){
            $('#DOC_FOOTER').hide();
            $('li.FOOTER').removeClass('active');
            $('li.FOOTER .pngs').hide();
            $('li.FOOTER .pngh').show();
        });	
        $('.HEADER.blockHeader').toggle(function(){
            $('#DOC_HEADER').show();
            $('li.HEADER').addClass('active');
            $('li.HEADER .pngs').show();
            $('li.HEADER .pngh').hide();
        },function(){
            $('#DOC_HEADER').hide();
            $('li.HEADER').removeClass('active');
            $('li.HEADER .pngs').hide();
            $('li.HEADER .pngh').show();
            
        });
        $('.CONDITIONS.blockHeader').toggle(function(){
            $('#DOC_CONDITIONS').show();
            $('li.CONDITIONS').addClass('active');
            $('li.CONDITIONS .pngs').show();
            $('li.CONDITIONS .pngh').hide();
        },function(){
            $('#DOC_CONDITIONS').hide();
            $('li.CONDITIONS').removeClass('active');
            $('li.CONDITIONS .pngs').hide();
            $('li.CONDITIONS .pngh').show();
            
        });	
    });
</script>

