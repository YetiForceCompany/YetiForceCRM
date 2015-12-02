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
		<div class='col-md-8 row'>
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
		<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
			{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
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
				<span class="pull-right">
					<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
					<a class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
				</span>
				<div class="clearfix"></div>
			</div>
			{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name="EditViewBlockLevelLoop"}
			{if $BLOCK_FIELDS|@count lte 0}{continue}{/if}
			{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL]}
			{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
			{assign var=BLOCKS_HIDE value=$BLOCK->isHideBlock($RECORD,$VIEW)}
			{if $BLOCKS_HIDE}
				<div class="panel panel-default row marginLeftZero marginRightZero blockContainer" data-label="{$BLOCK_LABEL}">
					<div class="row blockHeader panel-heading marginLeftZero marginRightZero">
						{if $APIADDRESS_ACTIVE eq true && ($BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_MAILING_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_DELIVERY_INFORMATION')}
							{assign var=APIADDRESFIELD value=TRUE}
						{else}
							{assign var=APIADDRESFIELD value=FALSE}
						{/if}
						<div class="iconCollapse">
							<span class="cursorPointer alignMiddle blockToggle glyphicon glyphicon-menu-right {if !($IS_HIDDEN)}hide{/if}" data-mode="hide" data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
							<span class="cursorPointer alignMiddle blockToggle glyphicon glyphicon glyphicon-menu-down {if ($IS_HIDDEN)}hide{/if}" data-mode="show" data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
						</div>
						<div>
							<h4>{vtranslate($BLOCK_LABEL, $QUALIFIED_MODULE_NAME)}</h4>
						</div>
					</div>
					<div class="col-md-12 paddingLRZero panel-body blockContent {if $IS_HIDDEN}hide{/if}">
						<div class="col-md-12 paddingLRZero">
							{assign var=COUNTER value=0}
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
								{if $FIELD_MODEL->get('uitype') eq '20' || $FIELD_MODEL->get('uitype') eq '19' || $FIELD_MODEL->get('uitype') eq '300'}
									{if $COUNTER eq '1'}
										<td class="{$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td></tr><tr>
											{assign var=COUNTER value=0}
										{/if}
									{/if}
									{if $COUNTER eq 2}
								</div><div class="col-md-12 paddingLRZero">
									{assign var=COUNTER value=1}
								{else}
									{assign var=COUNTER value=$COUNTER+1}
								{/if}
								<div class="{if $FIELD_MODEL->get('uitype') eq '300'}col-md-12 {else} col-md-6{/if} fieldRow">
									<div class="col-md-3 fieldLabel paddingLeft5px {$WIDTHTYPE}">
										<label class="muted pull-right marginRight10px">
											{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span>{/if}
											{if $FIELD_MODEL->get('uitype') eq "83"}
												{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) COUNTER=$COUNTER MODULE=$MODULE}
											{else}
												{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
											{/if}
										</label>
									</div>
									{if $FIELD_MODEL->get('uitype') neq "83"}
										<div class="col-md-9 fieldValue {$WIDTHTYPE}" {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '20'} colspan="3" {elseif $FIELD_MODEL->get('uitype') eq '300'} colspan="4" {assign var=COUNTER value=$COUNTER+1} {/if}>
											<div class="row">
												<div class="col-md-10">
													{if $FIELD_MODEL->get('uitype') eq "300"}
														<label class="muted">{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}{vtranslate($FIELD_MODEL->get('label'), $MODULE)}</label>
													{/if}
													{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
												</div>
											</div>
										</div>
									{/if}
								</div>
							{/foreach}
						</div>
					</div>
				</div>
			{/if}
		{/foreach}
	{/strip}
