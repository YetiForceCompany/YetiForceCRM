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
	{foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
	{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
	{if $BLOCK eq null or $FIELD_MODEL_LIST|@count lte 0}{continue}{/if}
	{assign var=BLOCKS_HIDE value=$BLOCK->isHideBlock($RECORD,$VIEW)}
	{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	{if $BLOCKS_HIDE}
	<div class="detailViewTable">
		<div class="panel panel-default row no-margin" data-label="{$BLOCK_LABEL}">					
			<div class="row blockHeader panel-heading no-margin">
				<div class="iconCollapse">
					<span class="cursorPointer blockToggle glyphicon glyphicon-menu-right {if !($IS_HIDDEN)}hide{/if}" alt="{vtranslate('LBL_EXPAND_BLOCK')}" data-mode="hide" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}></span>
					<span class="cursorPointer blockToggle glyphicon glyphicon glyphicon-menu-down {if $IS_HIDDEN}hide{/if}" alt="{vtranslate('LBL_COLLAPSE_BLOCK')}" data-mode="show" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}></span>
					<h4>{vtranslate({$BLOCK_LABEL_KEY},{$MODULE_NAME})}</h4>
				</div>
			</div>
		<div class="col-xs-12 noSpaces panel-body blockContent {if $IS_HIDDEN} hide{/if}">
		{assign var=COUNTER value=0}
		<div class="col-xs-12 paddingLRZero fieldRow">
		{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELD_MODEL_LIST}
			{if !$FIELD_MODEL->isViewableInDetailView()}
				 {continue}
			 {/if}
			{if $FIELD_MODEL->get('uitype') eq "83"}
				{foreach item=tax key=count from=$TAXCLASS_DETAILS}
					{if $tax.check_value eq 1}
						{if $COUNTER eq 2}
						</div><div class="col-xs-12 paddingLRZero fieldRow">
							{assign var="COUNTER" value=1}
						{else}
							{assign var="COUNTER" value=$COUNTER+1}
						{/if}
						<div class="col-md-6 col-xs-12 fieldsLabelValue paddingLRZero">
							<div class="fieldLabel col-sm-5 col-xs-12 {$WIDTHTYPE}">
								<label class="muted pull-left-xs pull-right-sm pull-right-md pull-right-lg">{vtranslate($tax.taxlabel, $MODULE)}&nbsp;(%)</label>
							</div>
							<div class="fieldValue col-sm-7 col-xs-12 {$WIDTHTYPE}">
								<span class="value">
									{$tax.percentage}
								</span>
							</div>
						</div>
					{/if}
				{/foreach}
			{else if $FIELD_MODEL->get('uitype') eq "69" || $FIELD_MODEL->get('uitype') eq "105"}
				{if $COUNTER neq 0}
					{if $COUNTER eq 2}
						</div><div class="col-xs-12 paddingLRZero fieldRow">
						{assign var=COUNTER value=0}
					{/if}
				{/if}
				<div class="col-md-6 col-xs-12 fieldsLabelValue paddingLRZero"
					<div class="fieldLabel col-sm-5 col-xs-12 {$WIDTHTYPE}">
						<label class="muted pull-left-xs pull-right-sm pull-right-md pull-right-lg">{vtranslate({$FIELD_MODEL->get('label')},{$MODULE_NAME})}</label>
					</div>
					<div class="fieldValue col-sm-7 col-xs-12 {$WIDTHTYPE}">
						<div id="imageContainer">
							{foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
								{if !empty($IMAGE_INFO.path) && !empty({$IMAGE_INFO.orgname})}
									<img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" width="300" height="200">
								{/if}
							{/foreach}
						</div>
					</div>
				</div>
				{assign var=COUNTER value=$COUNTER+1}
			{else}
				{if $FIELD_MODEL->get('uitype') eq "20" or $FIELD_MODEL->get('uitype') eq "19"}
					{if $COUNTER eq '1'}
						{assign var=COUNTER value=0}
					{/if}
				{/if}
				 {if $COUNTER eq 2}
					</div><div class="col-xs-12 paddingLRZero fieldRow">
					{assign var=COUNTER value=1}
				{else}
					{assign var=COUNTER value=$COUNTER+1}
				 {/if}
				 <div class="col-md-6 col-xs-12 fieldsLabelValue paddingLRZero">
					<div class="fieldLabel col-sm-5 col-xs-12 {$WIDTHTYPE}" id="{$MODULE}_detailView_fieldLabel_{$FIELD_MODEL->getName()}">
						<label class="muted pull-left-xs pull-right-sm pull-right-md pull-right-lg">
							{vtranslate({$FIELD_MODEL->get('label')},{$MODULE_NAME})}
						</label>
					</div>
					<div class="fieldValue col-sm-7 col-xs-12 {$WIDTHTYPE}" id="{$MODULE}_detailView_fieldValue_{$FIELD_MODEL->getName()}" {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '20'} {assign var=COUNTER value=$COUNTER+1} {/if} {if $FIELD_MODEL->get('name') eq 'password'}onclick="showPasswordQuickEdit('{$smarty.get.record}');" {/if}>
						<span class="value" data-field-type="{$FIELD_MODEL->getFieldDataType()}" {if $FIELD_MODEL->get('name') eq 'password'}id="detailPassword" {/if}>
						   {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
						</span>
						{if $FIELD_MODEL->isEditable() eq 'true' && ($FIELD_MODEL->getFieldDataType()!=Vtiger_Field_Model::REFERENCE_TYPE) && $IS_AJAX_ENABLED && $FIELD_MODEL->isAjaxEditable() eq 'true'}
							<span class="hide edit">
								{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME}
								{if $FIELD_MODEL->getFieldDataType() eq 'multipicklist' || $FIELD_MODEL->getFieldDataType() eq 'taxes' }
									<input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}[]' data-type="{$FIELD_MODEL->getFieldDataType()}" data-prev-value='{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD, true)}' />
								{elseif $FIELD_MODEL->getFieldDataType() eq 'boolean' || $FIELD_MODEL->getFieldDataType() eq 'picklist'}
									<input type="hidden" class="fieldname" data-type="{$FIELD_MODEL->getFieldDataType()}" value='{$FIELD_MODEL->get('name')}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />		
								{else}
									{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId())}
									{if $FIELD_VALUE|is_array}
										{assign var=FIELD_VALUE value=\includes\utils\Json::encode($FIELD_VALUE)}
									{/if}
									<input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}' data-type="{$FIELD_MODEL->getFieldDataType()}" data-prev-value='{Vtiger_Util_Helper::toSafeHTML($FIELD_VALUE)}' />
								{/if}
							</span>
						{/if}
					</div>
				 </div>
			 {/if}
		{/foreach}
		</div>
		</div>
	</div>
	{/if}
	<br>
	{/foreach}    
    
    <div class="contentHeader row no-margin">
        <div class="pull-right">
            <button class="btn btn-success hide" data-clipboard-target="detailPassword" id="copy-button" title="{vtranslate('LBL_CopyToClipboardTitle', $MODULE_NAME)}"><span class="glyphicon glyphicon-download-alt"></span> {vtranslate('LBL_CopyToClipboard', $MODULE_NAME)}</button>&nbsp;&nbsp;
            <button class="btn btn-warning" onclick="showDetailsPassword('{$smarty.get.record}');return false;" id="show-btn">{vtranslate('LBL_ShowPassword', $MODULE_NAME)}</button>
        </div>
        <div class="clearfix"></div>
    </div>
{/strip}
