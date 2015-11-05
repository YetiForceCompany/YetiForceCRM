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
</style>
{strip}
	{$i=0}
	{foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
	{if $FIELD_MODEL_LIST|@count lte 0}{continue}{/if}
	{assign var=IS_HIDDEN value=$BLOCK_LIST[$BLOCK_LABEL_KEY]->isHidden()}
	{if $BLOCK_LABEL_KEY eq 'HEADER'}
		<div class="">
			<div class="btn-group" data-toggle="buttons">
				<label class="btn btn-primary" data-block="{$BLOCK_LABEL_KEY}">
					<input type="checkbox" text="{vtranslate({$BLOCK_LABEL_KEY}, {$MODULE_NAME})}">{vtranslate({$BLOCK_LABEL_KEY}, {$MODULE_NAME})}
				</label>
				<label class="btn btn-primary" data-block="CONTENT">
					<input type="checkbox" text="{vtranslate('CONTENT', {$MODULE_NAME})}">{vtranslate('CONTENT', {$MODULE_NAME})} 
				</label>
				<label class="btn btn-primary" data-block="FOOTER">
					<input type="checkbox" text="{vtranslate('FOOTER', {$MODULE_NAME})}">{vtranslate('FOOTER', {$MODULE_NAME})}
				</label>
				<label class="btn btn-primary" data-block="CONDITIONS">
					<input type="checkbox" text="{vtranslate('CONDITIONS', {$MODULE_NAME})}">{vtranslate('CONDITIONS', {$MODULE_NAME})}
				</label>
			</div>
		</div>
		<table class="table table-bordered">	
			<tbody>
			{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELD_MODEL_LIST}
				<tr>
				<td id="DOC_{$BLOCK_LABEL_KEY}" class="hide" colspan="12" >
					<iframe class="pdfPreview_Content" style="width: 100%;height: 300px;" src="{$URL_{$BLOCK_LABEL_KEY}}" frameborder="0"></iframe>
				</td>
				</tr>
				<tr>
				<td id="DOC_CONTENT" class="hide" colspan="12" >
					<iframe class="pdfPreview_Content" style="width: 100%; height: 600px;" src="{$URL_CONTENT}" frameborder="0"></iframe>
				</td>
				</tr>
				<tr>
				<td id="DOC_FOOTER" class="hide" colspan="12">
					<iframe class="pdfPreview_Content" style="width: 100%;height: 300px;" src="{$URL_FOOTER}" frameborder="0"></iframe>
				</td>
				</tr>
				<tr>		
				<td id="DOC_CONDITIONS" class="hide" colspan="12">
					{include file=vtemplate_path('ConditionsView.tpl',$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS }
				</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	{elseif $BLOCK_LABEL_KEY eq 'CONTENT' OR $BLOCK_LABEL_KEY eq 'FOOTER'}
	
	{else}
	<table class="table table-bordered equalSplit detailview-table">
				<thead>
				<tr>
				<th class="blockHeader" colspan="4">
						<img class="cursorPointer alignMiddle blockToggle {if !($IS_HIDDEN)} hide {/if} " alt="{vtranslate('LBL_EXPAND_BLOCK')}" src="{vimage_path('arrowRight.png')}" data-mode="hide" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}>
						<img class="cursorPointer alignMiddle blockToggle {if ($IS_HIDDEN)} hide {/if}" alt="{vtranslate('LBL_COLLAPSE_BLOCK')}"  src="{vimage_path('arrowDown.png')}" data-mode="show" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}>
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
					<td class="fieldLabel">
					<label class='muted pull-right marginRight10px'>{vtranslate($tax.taxlabel, $MODULE)}(%)</label>
					</td>
					 <td class="fieldValue">
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
					{else}
						<td></td><td></td></tr><tr>
						{assign var=COUNTER value=0}
					{/if}
				{/if}
				<td class="fieldLabel" colspan="2"><label class="muted pull-right marginRight10px">{vtranslate({$FIELD_MODEL->get('label')},{$MODULE_NAME})}</label></td>
				<td class="fieldValue" colspan="2">
					{foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
						<div class="imageContainer">
							{if !empty($IMAGE_INFO.path) && !empty({$IMAGE_INFO.orgname})}
								<img src="../{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}">&nbsp;&nbsp;
							{/if}
						</div>
					{/foreach}
				</td>
				</tr><tr>
			{else}
				 {if $COUNTER eq 2}
					 </tr><tr>
					{assign var=COUNTER value=1}
				{else}
					{assign var=COUNTER value=$COUNTER+1}
				 {/if}
				 <td class="fieldLabel" id="{$MODULE}_detailView_fieldLabel_{$FIELD_MODEL->getName()}"><label class="muted pull-right marginRight10px">{vtranslate({$FIELD_MODEL->get('label')},{$MODULE_NAME})}</label></td>
				 <td class="fieldValue" id="{$MODULE}_detailView_fieldValue_{$FIELD_MODEL->getName()}">
					 <span class="value">
					 
						{if $FIELD_MODEL->getName() eq 'moduleid'}
							{$MODULEID_NAME}
						{else}
							 {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
						{/if}
					 </span>
				 </td>
			 {/if}

		{if $FIELD_MODEL_LIST|@count eq 1 and $FIELD_MODEL->get('uitype') neq "19" and $FIELD_MODEL->get('uitype') neq "20" and $FIELD_MODEL->get('uitype') neq "30" and $FIELD_MODEL->get('name') neq "recurringtype" and $FIELD_MODEL->get('uitype') neq "69" and $FIELD_MODEL->get('uitype') neq "105"}<td></td><td></td>{/if}
		{/foreach}
		</tr>
		</tbody>
	</table>
	<br />
	{/if}
	{/foreach}
{/strip}
