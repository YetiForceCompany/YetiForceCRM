{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	{foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
		{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
	{if $BLOCK eq null or $FIELD_MODEL_LIST|@count lte 0}{continue}{/if}
	{assign var=BLOCKS_HIDE value=$BLOCK->isHideBlock($RECORD,$VIEW)}
	{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	{if $BLOCKS_HIDE}
	<div class="table-responsive">
		<table class="table table-bordered equalSplit detailview-table">
			<thead>
				<tr>
					<th class="blockHeader" colspan="4">
						<img class="cursorPointer alignMiddle blockToggle {if !($IS_HIDDEN)} hide {/if} " alt="{vtranslate('LBL_EXPAND_BLOCK')}"  src="{vimage_path('arrowRight.png')}" data-mode="hide" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}>
						<img class="cursorPointer alignMiddle blockToggle {if ($IS_HIDDEN)} hide {/if}" alt="{vtranslate('LBL_COLLAPSE_BLOCK')}" src="{vimage_path('arrowDown.png')}" data-mode="show" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}>
						&nbsp;&nbsp;{vtranslate({$BLOCK_LABEL_KEY},{$MODULE_NAME})}
					</th>
				</tr>
			</thead>
			<tbody {if $IS_HIDDEN} class="hide" {/if}>
				{assign var=COUNTER value=0}
				<tr>
					{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELD_MODEL_LIST}
						{if !$FIELD_MODEL->isViewableInDetailView()}
							{continue}
						{/if}
						{if $FIELD_MODEL->get('uitype') eq "83"}
							{foreach item=tax key=count from=$TAXCLASS_DETAILS}
								{if $tax.check_value eq 1}
									{if $COUNTER eq 2}
									</tr><tr>
										{assign var="COUNTER" value=1}
									{else}
										{assign var="COUNTER" value=$COUNTER+1}
									{/if}
									<td class="fieldLabel {$WIDTHTYPE}">
										<label class='muted pull-right marginRight10px'>{vtranslate($tax.taxlabel, $MODULE)}&nbsp;(%)</label>
									</td>
									<td class="fieldValue {$WIDTHTYPE}">
										<span class="value">
											{$tax.percentage}
										</span>
									</td>
								{/if}
							{/foreach}
						{else if $FIELD_MODEL->get('uitype') eq "69" || $FIELD_MODEL->get('uitype') eq "105"}
							{if $COUNTER neq 0}
								{if $COUNTER eq 2}
								</tr><tr>
									{assign var=COUNTER value=0}
								{/if}
							{/if}
							<td class="fieldLabel {$WIDTHTYPE}"><label class="muted pull-right marginRight10px">{vtranslate({$FIELD_MODEL->get('label')},{$MODULE_NAME})}</label></td>
							<td class="fieldValue {$WIDTHTYPE}">
								<div id="imageContainer" width="300" height="200">
									{foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
										{if !empty($IMAGE_INFO.path) && !empty({$IMAGE_INFO.orgname})}
											<img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" width="300" height="200">
										{/if}
									{/foreach}
								</div>
							</td>
							{assign var=COUNTER value=$COUNTER+1}
						{else}
							{if $FIELD_MODEL->get('uitype') eq "20" or $FIELD_MODEL->get('uitype') eq "19" or $FIELD_MODEL->get('uitype') eq '300'}
								{if $COUNTER eq '1'}
									<td class="{$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td></tr><tr>
										{assign var=COUNTER value=0}
									{/if}
								{/if}
								{if $COUNTER eq 2}
							</tr><tr>
								{assign var=COUNTER value=1}
							{else}
								{assign var=COUNTER value=$COUNTER+1}
							{/if}
							<td class="fieldLabel {$WIDTHTYPE}" id="{$MODULE_NAME}_detailView_fieldLabel_{$FIELD_MODEL->getName()}" {if $FIELD_MODEL->getName() eq 'description' or $FIELD_MODEL->get('uitype') eq '69' or $FIELD_MODEL->get('uitype') eq '300'} style='width:8%'{/if}>
								{assign var=HELPINFO value=explode(',',$FIELD_MODEL->get('helpinfo'))}
								{assign var=HELPINFO_LABEL value=$MODULE_NAME|cat:'|'|cat:$FIELD_MODEL->get('label')}
								{if in_array($VIEW,$HELPINFO) && vtranslate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
									<a style="margin-left: 5px;margin-top: 2px;" href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{htmlspecialchars(vtranslate($MODULE_NAME|cat:'|'|cat:$FIELD_MODEL->get('label'), 'HelpInfo'))}" data-original-title='{vtranslate($FIELD_MODEL->get("label"), $MODULE_NAME)}'><i class="glyphicon glyphicon-info-sign"></i></a>
									{/if}
								<label class="muted pull-right marginRight10px">
									{vtranslate({$FIELD_MODEL->get('label')},{$MODULE_NAME})}
								</label>
							</td>
							<td class="fieldValue {$WIDTHTYPE}" id="{$MODULE_NAME}_detailView_fieldValue_{$FIELD_MODEL->getName()}" {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '20' or $FIELD_MODEL->get('uitype') eq '300'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
								<span class="value" data-field-type="{$FIELD_MODEL->getFieldDataType()}" {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '20' or $FIELD_MODEL->get('uitype') eq '21' or $FIELD_MODEL->get('uitype') eq '300'} style="white-space:normal;" {/if}>
									{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
								</span>
								{assign var=EDIT value=false}
								{if in_array($FIELD_MODEL->getName(),['date_start','due_date']) && $MODULE eq 'Calendar'}
									{assign var=EDIT value=true}
								{/if}
								{if $IS_AJAX_ENABLED && $FIELD_MODEL->isEditable() eq 'true' && $FIELD_MODEL->isAjaxEditable() eq 'true' && !$EDIT}
									<span class="summaryViewEdit cursorPointer pull-right">
										&nbsp;<i class="glyphicon glyphicon-pencil" title="{vtranslate('LBL_EDIT',$MODULE_NAME)}"></i>
									</span>
									<span class="hide edit">
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME}
										{if $FIELD_MODEL->getFieldDataType() eq 'multipicklist' || $FIELD_MODEL->getFieldDataType() eq 'sharedOwner' }
											<input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}[]' data-prev-value='{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}' />
										{elseif $FIELD_MODEL->getFieldDataType() eq 'boolean' || $FIELD_MODEL->getFieldDataType() eq 'picklist'}
											<input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />		
										{else}
											<input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}' data-prev-value='{Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue')))}' />
										{*{if in_array($FIELD_MODEL->getName(),['date_start','due_date']) && $MODULE eq 'Calendar'}
											{assign var=TIME_FIELDS value=['due_date'=>'time_end','date_start'=>'time_start']}
											{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
											{assign var=TIME_FIELD value=$RECORD_STRUCTURE_MODEL->getModule()->getField($TIME_FIELDS.$FIELD_NAME)}
											<input type="hidden" class="fieldname" value='{$TIME_FIELDS.$FIELD_NAME}' data-prev-value='{$TIME_FIELD->getEditViewDisplayValue($RECORD->get($TIME_FIELDS.$FIELD_NAME))}' />
										{/if}*}
										{/if}
									</span>
								{/if}
							</td>
						{/if}

						{if $FIELD_MODEL_LIST|@count eq 1 and $FIELD_MODEL->get('uitype') neq "19" and $FIELD_MODEL->get('uitype') neq "20" and $FIELD_MODEL->get('uitype') neq '300' and $FIELD_MODEL->get('uitype') neq "30" and $FIELD_MODEL->get('name') neq "recurringtype" and $FIELD_MODEL->get('uitype') neq "69" and $FIELD_MODEL->get('uitype') neq "105"}
							<td class="fieldLabel {$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td>
							{/if}
						{/foreach}
						{* adding additional column for odd number of fields in a block *}
						{if $FIELD_MODEL_LIST|@end eq true and $FIELD_MODEL_LIST|@count neq 1 and $COUNTER eq 1}
						<td class="fieldLabel {$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td>
						{/if}
				</tr>
			</tbody>
		</table>
	</div>
	<br>
	{/if}
{/foreach}
{/strip}
