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
	<div class='editViewContainer'>
		<div class='col-md-8 row'>
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
		<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
			{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
			{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
				<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
			{/if}

			{foreach from=$APIADDRESS item=item key=key}
				{if $item['nominatim']}
					<input type="hidden" name="apiAddress" value='{$item['key']}' data-max-num="{$APIADDRESS['global']['result_num']}" data-api-name="{$key}" data-url="{$item['source']}" data-lenght="{$APIADDRESS['global']['min_lenght']}"/>
				{/if}
			{/foreach}

			{if !empty($MAPPING_RELATED_FIELD)}
				<input type="hidden" name="mappingRelatedField" value='{Vtiger_Util_Helper::toSafeHTML($MAPPING_RELATED_FIELD)}' />
			{/if}
			<input type="hidden" name="module" value="{$MODULE}" />
			<input type="hidden" name="action" value="Save" />
			<input type="hidden" name="record" id="recordId" value="{$RECORD_ID}" />
			<input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" />
			<input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" />
			{if $IS_RELATION_OPERATION }
				<input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}" />
				<input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}" />
				<input type="hidden" name="relationOperation" value="{$IS_RELATION_OPERATION}" />
			{/if}
			<div class="contentHeader row">
				<div class="pull-right">
					<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
					<a class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
				</div>
			</div>
			{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name="EditViewBlockLevelLoop"}
			{if $BLOCK_FIELDS|@count lte 0}{continue}{/if}
			{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL]}
			{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
			{assign var=BLOCKS_HIDE value=$BLOCK->isHideBlock($RECORD,$VIEW)}
			{if $BLOCKS_HIDE}
				<div class="panel panel-default row marginLeftZero marginRightZero blockContainer" data-label="{$BLOCK_LABEL}">
					<div class="row blockHeader panel-heading marginLeftZero marginRightZero">
						<div class="iconCollapse">
							<img class="cursorPointer alignMiddle blockToggle {if !($IS_HIDDEN)}hide{/if}" alt="{vtranslate('LBL_EXPAND_BLOCK')}"  src="{vimage_path('arrowRight.png')}" data-mode="hide" data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}>
							<img class="cursorPointer alignMiddle blockToggle {if ($IS_HIDDEN)}hide{/if}"  alt="{vtranslate('LBL_COLLAPSE_BLOCK')}" src="{vimage_path('arrowDown.png')}" data-mode="show" data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}>
						</div>
						<div>
							<h4>{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
						</div>
					</div>
					<div class="col-md-12 paddingLRZero panel-body blockContent {if $IS_HIDDEN}hide{/if}">
						{if $BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_MAILING_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_DELIVERY_INFORMATION'}
							<div class="col-md-12 adressAction">
								{if $APIADDRESFIELD}
									<div class="col-md-4">
										<input value="" title="{vtranslate('LBL_ADDRESS_INFORMATION')}" type="text" class="api_address_autocomplete form-control pull-right input " placeholder="{vtranslate('LBL_ENTER_SEARCHED_ADDRESS')}" />
									</div>
								{/if}
								<div class="{if $APIADDRESFIELD}col-md-8{else}col-md-12{/if} text-center">
									{include file=vtemplate_path('BlockHeader.tpl',$MODULE)}
								</div>
							</div>
						{/if}
						<div class="col-md-12 paddingLRZero">
							{assign var=COUNTER value=0}
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
								{if $COUNTER eq 2}
									</div><div class="col-md-12 paddingLRZero">
									{assign var=COUNTER value=1}
								{else}
									{assign var=COUNTER value=$COUNTER+1}
								{/if}
								<div class="{if $FIELD_MODEL->get('uitype') neq "300"}col-md-6{/if} fieldRow">
									<div class="col-md-3 fieldLabel textAlignRight {$WIDTHTYPE}">
										<label class="muted pull-right marginRight10px">
											{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
											{if $FIELD_MODEL->get('uitype') eq "83"}
												{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) COUNTER=$COUNTER}
											{else}
												{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
											{/if}
										</label>
									</div>
									{if $FIELD_MODEL->get('uitype') neq "83"}
										<div class="col-md-9 fieldValue {$WIDTHTYPE}" {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '20'} colspan="3" {elseif $FIELD_MODEL->get('uitype') eq '300'} colspan="4" {assign var=COUNTER value=$COUNTER+1} {/if}>
											<div class="row">
												<span class="col-md-10">
													{if $FIELD_MODEL->get('uitype') eq "300"}
														<label class="muted">{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}{vtranslate($FIELD_MODEL->get('label'), $MODULE)}</label>
													{/if}
													{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
												</span>
											</div>
										</div>
									{/if}
								</div>
								{/foreach}
								{* adding additional column for odd number of fields in a block *}
								{if $BLOCK_FIELDS|@end eq true and $BLOCK_FIELDS|@count neq 1 and $COUNTER eq 1}
								<td class="fieldLabel {$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td>
								{/if}
						</div>
					</div>
				</div>
			{/if}
		{/foreach}
		<br>
	{/strip}
