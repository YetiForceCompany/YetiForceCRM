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
		<input type="hidden" name="record" id="recordId" value="{$RECORD_ID}" />
		<input type="hidden" name="isPreference" value="{$IS_PREFERENCE}" />
		<input type="hidden" name="timeFormatOptions" data-value='{$DAY_STARTS}' />
		{if $IS_RELATION_OPERATION }
			<input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}" />
			<input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}" />
			<input type="hidden" name="relationOperation" value="{$IS_RELATION_OPERATION}" />
		{/if}
		<input type="hidden" name="mappingRelatedField" value="{Vtiger_Util_Helper::toSafeHTML($MAPPING_RELATED_FIELD)}" />
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		<div class="widget_header row">
			<div class="col-md-8">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			</div>
			<div class="col-md-4">
				<span class="pull-right" style="padding-right: 15px">
					<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
					<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</button>
				</span>
			</div>
		</div>
			{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
				{if $BLOCK_FIELDS|@count gt 0}
				<div class="panel panel-default row marginLeftZero marginRightZero blockContainer" data-label="{vtranslate($BLOCK_LABEL, $MODULE)}">
					<div class="row blockHeader panel-heading marginLeftZero marginRightZero">
						<div class="iconCollapse">
							<span class="cursorPointer blockToggle glyphicon glyphicon-menu-right {if !($IS_HIDDEN)}hide{/if}" data-mode="hide" data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
							<span class="cursorPointer blockToggle glyphicon glyphicon glyphicon-menu-down {if ($IS_HIDDEN)}hide{/if}" data-mode="show" data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
							<h4>{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
						</div>
					</div>
				<div class="col-md-12 paddingLRZero panel-body blockContent {if $IS_HIDDEN}hide{/if}">	
					<div class="col-md-12 paddingLRZero">
				{assign var=COUNTER value=0}
				{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
					{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
					{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
					{assign var="refrenceListCount" value=count($refrenceList)}
					{if $COUNTER eq 2}
						</div><div class="col-md-12 paddingLRZero">
						{assign var=COUNTER value=1}
					{else}
						{assign var=COUNTER value=$COUNTER+1}
					{/if}
					<div class="{if $FIELD_MODEL->get('uitype') neq "300"}col-md-6{/if} fieldRow">
						<div class="col-md-6 fieldLabel {$WIDTHTYPE}">
						{if {$isReferenceField} eq "reference"}
							{if $refrenceListCount > 1}
								<select style="width: 150px;" class="chzn-select form-control" id="referenceModulesList">
									<optgroup>
										{foreach key=index item=value from=$refrenceList}
											<option value="{$value}">{$value}</option>
										{/foreach}
									</optgroup>
								</select>
							{/if}
							{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
						{else}
							<label>
								{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
							</label>
						{/if}
						{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
						</div>
						<div class=" col-md-6 fieldValue {$WIDTHTYPE}" {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
							{include file=$FIELD_MODEL->getUITypeModel()->getTemplateName()|@vtemplate_path:$MODULE}
						</div>	
					</div>
				{/foreach}
					</div>
					</div>
					</div>
				{/if}
				<div class="clearfix"></div>
			{/foreach}
			<div class='pull-right'>
				<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
				<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</button>
			</div>
		</div>
    </form>
</div>
{/strip}
