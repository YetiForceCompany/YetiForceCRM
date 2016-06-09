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
            <input type="hidden" name="record" id="recordId" value="{$RECORD_ID}" />
            <input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" />
            <input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" />
            {if $IS_RELATION_OPERATION }
                <input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}" />
                <input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}" />
                <input type="hidden" name="relationOperation" value="{$IS_RELATION_OPERATION}" />
            {/if}
			{foreach from=$RECORD->getModule()->getFieldsByDisplayType(9) item=FIELD key=FIELD_NAME}
				<input type="hidden" name="{$FIELD_NAME}" value="{$RECORD->get($FIELD_NAME)}" />
			{/foreach}
            <div class="widget_header row">
				<div class="col-md-8">
					{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
				</div>
				<div class="col-md-4">
					<div class="contentHeader">
						<span class="pull-right">
							<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
							<button class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</button>
						</span>
					</div>
				</div>
            </div>
            {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name="EditViewBlockLevelLoop"}
            {if $BLOCK_FIELDS|@count lte 0}{continue}{/if}
			{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL]}
			{assign var=BLOCKS_HIDE value=$BLOCK->isHideBlock($RECORD,$VIEW)}
			{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
			{if $BLOCKS_HIDE}
				<div class="panel panel-default row marginLeftZero marginRightZero blockContainer" data-label="{$BLOCK_LABEL}">	
					<div class="row blockHeader panel-heading marginLeftZero marginRightZero">
						<div class="iconCollapse">
							<span class="cursorPointer blockToggle glyphicon glyphicon-menu-right {if !($IS_HIDDEN)}hide{/if}" data-mode="hide" data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
							<span class="cursorPointer blockToggle glyphicon glyphicon glyphicon-menu-down {if ($IS_HIDDEN)}hide{/if}" data-mode="show" data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
							<h4>{vtranslate($BLOCK_LABEL, $QUALIFIED_MODULE_NAME)}</h4>
						</div>
						<div class="col-md-8">
							{if $BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_MAILING_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_DELIVERY_INFORMATION'}
								{include file=vtemplate_path('BlockHeader.tpl',$MODULE)}
							{/if}
						</div>
					</div>
					<div class="col-md-12 paddingLRZero panel-body blockContent {if $IS_HIDDEN}hide{/if}">
						<div class="col-md-12 paddingLRZero">
							{assign var=COUNTER value=0}
							{assign var=MAILTEMPLATES_TYPE value=FALSE}
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
								{if $FIELD_NAME eq 'ossmailtemplates_type' && $FIELD_MODEL->get('fieldvalue') eq 'PLL_MODULE'}
									{assign var=MAILTEMPLATES_TYPE value=TRUE}
								{/if}
								{if $FIELD_MODEL->get('uitype') eq '20' || $FIELD_MODEL->get('uitype') eq '19' || $FIELD_MODEL->get('uitype') eq '300'}
									{if $COUNTER eq '1'}
										</div>
										<div class="col-md-12 paddingLRZero">
											{assign var=COUNTER value=0}
										{/if}
									{/if}
									{if $COUNTER eq 2}
								</div>
								<div class="col-md-12 paddingLRZero">
									{assign var=COUNTER value=1}
								{else}
									{assign var=COUNTER value=$COUNTER+1}
								{/if}
								<div class="{if $FIELD_MODEL->get('uitype') neq "300"}col-md-6{/if} fieldRow">
									<div class="col-md-3 fieldLabel paddingLeft5px {$WIDTHTYPE}">
									{assign var=HELPINFO value=explode(',',$FIELD_MODEL->get('helpinfo'))}
									{assign var=HELPINFO_LABEL value=$MODULE|cat:'|'|cat:$FIELD_MODEL->get('label')}
									{if in_array($VIEW,$HELPINFO) && vtranslate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
										<a style="margin-left: 5px;margin-top: 2px;" href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{htmlspecialchars(vtranslate($MODULE|cat:'|'|cat:$FIELD_MODEL->get('label'), 'HelpInfo'))}" data-original-title='{vtranslate($FIELD_MODEL->get("label"), $MODULE)}'><i class="glyphicon glyphicon-info-sign"></i></a>
									{/if}
									<label class="muted pull-right marginRight10px">
										{if $FIELD_MODEL->isMandatory() eq true && $isReferenceField neq "reference"} <span class="redColor">*</span> {/if}
										{if $FIELD_MODEL->get('uitype') eq "83"}
											{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) COUNTER=$COUNTER MODULE=$MODULE}
										{else}
											{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
										{/if}
									</label>
								</div>
								{if $FIELD_MODEL->get('uitype') neq "83"}
									<div class="fieldValue {if $FIELD_MODEL->get('uitype') eq "300"}col-md-12 {else} col-md-9{/if}  {$WIDTHTYPE}" >
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
									</div>
								{/if}
								</div>
								{if $BLOCK_FIELDS|@count eq 1 and $FIELD_MODEL->get('uitype') neq "19" and $FIELD_MODEL->get('uitype') neq "20" and $FIELD_MODEL->get('uitype') neq "30" and $FIELD_MODEL->get('uitype') neq '300' and $FIELD_MODEL->get('name') neq "recurringtype"}
									</div>
									<div class="col-md-12 paddingLRZero">
									{/if}
								{/foreach}
								{if $smarty.foreach.blockfields.last and $smarty.foreach.EditViewBlockLevelLoop.iteration eq 1}
									{if $COUNTER eq 2}
								</div>
									<div class="col-md-12 paddingLRZero">
									{assign var=COUNTER value=0}
								{/if}
								{assign var=COUNTER value=$COUNTER+1}
								<div class="{if $FIELD_MODEL->get('uitype') neq "300"}col-md-6{/if} fieldRow">
									<div class="fieldLabel col-md-3 paddingLeft5px {$WIDTHTYPE}"><label class="muted pull-right marginRight10px">{vtranslate('MODULE_FIELD', $MODULE)}</label></div>
									<div class="fieldValue col-md-9 {$WIDTHTYPE}">
										
											<div class="col-md-8 paddingLRZero">
												<select class="chzn-select form-control" name="oss_fields_list" title="{vtranslate('LBL_CHOOSE_FIELD')}" data-placeholder="{vtranslate('LBL_SELECT_OPTION')}" {if $MAILTEMPLATES_TYPE } disabled {/if}>
												</select>
											</div>
											<div class="col-md-4 paddingLRZero">
												<button type="button" aria-hidden="true" class="btn btn-success muted pull-right marginRight10px toText copy-button {if $MAILTEMPLATES_TYPE } hide {/if}" data-prefix="a" data-select="oss_fields_list" title="{vtranslate('LBL_COPY_TO_CLIPBOARD_TITLE', $MODULE)} - {vtranslate('LBL_VALUE', $MODULE)}">
													<span class="glyphicon glyphicon-arrow-down icon-black"></span>
												</button>
												<button type="button" class="btn btn-info muted pull-right marginRight10px toText copy-button {if $MAILTEMPLATES_TYPE } hide {/if}" data-prefix="b" data-select="oss_fields_list" title="{vtranslate('LBL_COPY_TO_CLIPBOARD_TITLE', $MODULE)}  - {vtranslate('LBL_LABEL', $MODULE)}">
													<span class="glyphicon glyphicon-arrow-down"></span>
												</button>
											</div>	
										
									</div>
								</div>
							{/if}

							{if $COUNTER eq 2}
							</div>
									<div class="col-md-12 paddingLRZero">
								{assign var=COUNTER value=0}
							{/if}
							{if $smarty.foreach.EditViewBlockLevelLoop.iteration eq 1}
								<div class="{if $FIELD_MODEL->get('uitype') neq "300"}col-md-6{/if} fieldRow">
									<div class="fieldLabel col-md-3 {$WIDTHTYPE}"><label class="muted pull-right marginRight10px">{vtranslate('RELATED_MODULE_FIELD', $MODULE)}</label></div>
									<div class="fieldValue col-md-9 {$WIDTHTYPE}">
										
											<div class="col-md-8 paddingLRZero">
												<select class="chzn-select" name="oss_related_fields_list" title="{vtranslate('LBL_SELECT_RELATED_FIELD')}" data-placeholder="{vtranslate('LBL_SELECT_OPTION')}" {if $MAILTEMPLATES_TYPE } disabled {/if}>
												</select>
											</div>
											<div class="col-md-4 paddingLRZero">
												<button type="button" class="btn btn-success muted pull-right marginRight10px toText copy-button {if $MAILTEMPLATES_TYPE } hide {/if}" data-prefix="c" data-select="oss_related_fields_list" title="{vtranslate('LBL_COPY_TO_CLIPBOARD_TITLE', $MODULE)}  - {vtranslate('LBL_VALUE', $MODULE)}">
													<span class="glyphicon glyphicon-arrow-down"></span>
												</button>
												<button type="button" class="btn btn-info muted pull-right marginRight10px toText copy-button {if $MAILTEMPLATES_TYPE } hide {/if}" data-prefix="d" data-select="oss_related_fields_list" title="{vtranslate('LBL_COPY_TO_CLIPBOARD_TITLE', $MODULE)} - {vtranslate('LBL_LABEL', $MODULE)}">
													<span class="glyphicon glyphicon-arrow-down"></span>
												</button>
											</div>
									
									</div>
								</div>
								{assign var=COUNTER value=$COUNTER+1}

								{if $COUNTER eq 2}
								</div>
									<div class="col-md-12 paddingLRZero">
									{assign var=COUNTER value=0}
								{/if}
								<div class="{if $FIELD_MODEL->get('uitype') neq "300"}col-md-6{/if} fieldRow">
								
									<div class="fieldLabel col-md-3 {$WIDTHTYPE}" ><label class="muted pull-right marginRight10px">{vtranslate('SEPCIAL_FUNCTION', $MODULE)}</label></div>
									<div class="fieldValue col-md-9 {$WIDTHTYPE}">
									
											<div class="col-md-8 paddingLRZero">
												<select class="chzn-select" name="oss_special_function_list" title="{vtranslate('SEPCIAL_FUNCTION', $MODULE)}" style="width: 190px;">
												</select>
											</div>
											<div class="col-md-4 paddingLRZero">
												<button type="button" class="btn btn-success muted pull-right marginRight10px toText copy-button" data-prefix="s" data-select="oss_special_function_list" title="{vtranslate('LBL_COPY_TO_CLIPBOARD_TITLE', $MODULE)}">
													<span class="glyphicon glyphicon-arrow-down"></span>
												</button>
											</div>
										
									</div>
								</div>
								{assign var=COUNTER value=$COUNTER+1}
								{if $COUNTER eq '1'}
									</div>
									<div class="col-md-12 paddingLRZero">
										{assign var=COUNTER value=0}
									{/if}
							</div>
						{/if}
					</div>
				</div>
			{/if}
		{/foreach}
</div>
	{/strip}
