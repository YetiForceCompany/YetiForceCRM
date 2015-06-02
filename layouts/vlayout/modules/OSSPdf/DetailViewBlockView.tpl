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
	{$i=0}
	{foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
	{if $FIELD_MODEL_LIST|@count lte 0}{continue}{/if}
	{assign var=IS_HIDDEN value=$BLOCK_LIST[$BLOCK_LABEL_KEY]->isHidden()}
	{if $BLOCK_LABEL_KEY eq 'HEADER'}
		<div class="">
			<ul id="tabs" class="nav" style="list-style-type: none;border-radius: 10px;">
				<li class="{$BLOCK_LABEL_KEY} blockHeader font" style=" margin:0px 10px 0px 10px;" >
					<img class="cursorPointer alignMiddle blockToggle pngh" alt="{vtranslate('LBL_EXPAND_BLOCK')}" src="{vimage_path('arrowRight.png')}" >
					<img class="cursorPointer alignMiddle blockToggle hide pngs " alt="{vtranslate('LBL_COLLAPSE_BLOCK')}"  src="{vimage_path('arrowDown.png')}" >
					&nbsp;&nbsp;
					{vtranslate({$BLOCK_LABEL_KEY}, {$MODULE_NAME})} &nbsp;&nbsp;
				</li>
				<li class="CONTENT blockHeader font" style=" margin-right:10px;" >
					<img class="cursorPointer alignMiddle blockToggle pngh" alt="{vtranslate('LBL_EXPAND_BLOCK')}" src="{vimage_path('arrowRight.png')}" >
					<img class="cursorPointer alignMiddle blockToggle hide pngs " alt="{vtranslate('LBL_COLLAPSE_BLOCK')}"  src="{vimage_path('arrowDown.png')}" >&nbsp;&nbsp;
					{vtranslate('CONTENT', {$MODULE_NAME})} &nbsp;&nbsp;
				</li>
				<li class="FOOTER blockHeader font" style=" margin-right:10px;" >
					<img class="cursorPointer alignMiddle blockToggle pngh" alt="{vtranslate('LBL_EXPAND_BLOCK')}" src="{vimage_path('arrowRight.png')}" >
					<img class="cursorPointer alignMiddle blockToggle hide pngs " alt="{vtranslate('LBL_COLLAPSE_BLOCK')}"  src="{vimage_path('arrowDown.png')}" >
					&nbsp;&nbsp;
					{vtranslate('FOOTER', {$MODULE_NAME})} &nbsp;&nbsp;
				</li>
				<li class="CONDITIONS blockHeader font" style="" >
					<img class="cursorPointer alignMiddle blockToggle pngh" alt="{vtranslate('LBL_EXPAND_BLOCK')}" src="{vimage_path('arrowRight.png')}" >
					<img class="cursorPointer alignMiddle blockToggle hide pngs " alt="{vtranslate('LBL_COLLAPSE_BLOCK')}"  src="{vimage_path('arrowDown.png')}" >
					&nbsp;&nbsp;
					{vtranslate('CONDITIONS', {$MODULE})} &nbsp;&nbsp;
				</li>
			</ul>
		</div>
		<table class="table table-bordered">	
			<tbody>
			{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELD_MODEL_LIST}
				<tr>
				<td id="DOC_{$BLOCK_LABEL_KEY}" style="display: none;" colspan="12" >
					<iframe id="emailPreview_Content" style="width: 100%;height: 300px;" src="{$URL_{$BLOCK_LABEL_KEY}}" frameborder="0"></iframe>
				</td>
				</tr>
				<tr>
				<td id="DOC_CONTENT" style="display: none;" colspan="12" >
					<iframe id="emailPreview_Content" style="width: 100%; height: 600px;" src="{$URL_CONTENT}" frameborder="0"></iframe>
				</td>
				</tr>
				<tr>
				<td id="DOC_FOOTER" style="display: none;" colspan="12">
					<iframe id="emailPreview_Content" style="width: 100%;height: 300px;" src="{$URL_FOOTER}" frameborder="0"></iframe>
				</td>
				</tr>
				<tr>		
				<td id="DOC_CONDITIONS" style="display: none;" colspan="12">
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

<script type="text/javascript">


jQuery(function(){


	$('.CONTENT').toggle(function(){
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
	$('.FOOTER').toggle(function(){
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
	$('.HEADER').toggle(function(){
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
	$('.CONDITIONS').toggle(function(){
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
	$('.summaryView').hide();
	
});



</script>
