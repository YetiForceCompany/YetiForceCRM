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
            {assign var=QUALIFIED_MODULE_NAME value={$QUALIFIED_MODULE}}
            {assign var=IS_PARENT_EXISTS value=strpos($MODULE,":")}
            {if $PARENT_MODULE neq ''}
                <input type="hidden" id="module" name="module" value="{$MODULE}" />
                <input type="hidden" id="parent" name="parent" value="{$PARENT_MODULE}" />
				<input type='hidden' value="{$VIEW}" id='view' name='view'/>
            {else}
                <input type="hidden" id="module" name="module" value="{$MODULE}" />
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
				{assign var=IMAGE value=$MODULE|cat:'48.png'}
				{if file_exists( vimage_path($IMAGE) )}
					<span class="pull-left moduleIcon{$MODULE_NAME}">
						<span class="moduleIcon">
							<img src="{vimage_path($IMAGE)}" class="summaryImg" alt="{vtranslate($MODULE, $QUALIFIED_MODULE_NAME)}"/>
						</span>
					</span>
				{/if}
                {assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
                <span class="pull-right">
                    <button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE_NAME)}</strong></button>&nbsp;&nbsp;
                    <button class="btn btn-warning" type="reset" onclick="javascript:window.history.back();"><strong>{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE_NAME)}</strong></button>
				</span>
				<div class="clearfix"></div>
            </div>
			<hr>
            {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name="EditViewBlockLevelLoop"}
            {if $BLOCK_FIELDS|@count lte 0}{continue}{/if}
			{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL]}
			{assign var=BLOCKS_HIDE value=$BLOCK->isHideBlock($RECORD,$VIEW)}
			{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
			{if $BLOCKS_HIDE}
				<div class="blockContainer showInlineTable equalSplit" data-label="{$BLOCK_LABEL}">					
					<div class="row">
						<div class="col-md-12">
							{if $APIADDRESS_ACTIVE eq true && ($BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_MAILING_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_DELIVERY_INFORMATION')}
								{assign var=APIADDRESFIELD value=TRUE}
							{else}
								{assign var=APIADDRESFIELD value=FALSE}
							{/if}
							<div class="row">
								<div class=" {if $APIADDRESFIELD}col-md-7 {else}col-md-12{/if}">
									<h4>{vtranslate($BLOCK_LABEL, $QUALIFIED_MODULE_NAME)}</h4>
								</div>
							</div>
						</div>
					</div>
					<div {if $IS_HIDDEN} class="hide" {/if}>
						{if $BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_MAILING_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_DELIVERY_INFORMATION'}
							<div class="col-md-12">
								{if $APIADDRESFIELD}
									<div class="col-md-4">
										<input value="" title="{vtranslate('LBL_ADDRESS_INFORMATION')}" type="text" class="api_address_autocomplete form-control pull-right input " placeholder="{vtranslate('LBL_ENTER_SEARCHED_ADDRESS')}" />
									</div>
								{/if}
								<div class="{if $APIADDRESFIELD}col-md-8{else}col-md-8 {/if} paddingLRZero marginBottom10px text-center">
									{include file=vtemplate_path('BlockHeader.tpl',$MODULE)}
								</div>
							</div>
						{/if}
						<div class="col-md-12">
							{assign var=COUNTER value=0}
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}

								{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
								{if $FIELD_MODEL->get('uitype') eq '20' || $FIELD_MODEL->get('uitype') eq '19' || $FIELD_MODEL->get('uitype') eq '300'}
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
								<div class="{if $FIELD_MODEL->get('uitype') neq "300"}row col-md-6 {/if}marginBottom10px">
									<div class="col-md-3 row fieldLabel ">
										{assign var=HELPINFO value=explode(',',$FIELD_MODEL->get('helpinfo'))}
										{assign var=HELPINFO_LABEL value=$MODULE|cat:'|'|cat:$FIELD_MODEL->get('label')}
										{if in_array($VIEW,$HELPINFO) && vtranslate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
											<a style="margin-left: 5px;margin-top: 2px;" href="#" class="HelpInfoPopover pull-right" title="" data-placement="top" data-content="{htmlspecialchars(vtranslate($MODULE|cat:'|'|cat:$FIELD_MODEL->get('label'), 'HelpInfo'))}" data-original-title='{vtranslate($FIELD_MODEL->get("label"), $MODULE)}'><i class="glyphicon glyphicon-info-sign"></i></a>
											{/if}
											{if $isReferenceField neq "reference"}<label class="muted ">{/if}
											{if $FIELD_MODEL->isMandatory() eq true && $isReferenceField neq "reference"} <span class="redColor">*</span> {/if}
											{if $isReferenceField eq "reference"}
												{assign var="REFERENCE_LIST" value=$FIELD_MODEL->getReferenceList()}
												{assign var="REFERENCE_LIST_COUNT" value=count($REFERENCE_LIST)}
												{if $REFERENCE_LIST_COUNT > 1}
													{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
													{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
													{if !empty($REFERENCED_MODULE_STRUCT)}
														{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
													{/if}
													{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor referenceMandatory">*</span> {/if}
													<span class="paddingRightZero pull-left">
														<select id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}_dropDown" class="chzn-select referenceModulesList streched" title="{vtranslate('LBL_RELATED_MODULE_TYPE')}" >
															<optgroup>
																{foreach key=index item=value from=$REFERENCE_LIST}
																	<option value="{$value}" title="{vtranslate($value, $QUALIFIED_MODULE_NAME)}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if}>{vtranslate($value, $QUALIFIED_MODULE_NAME)}</option>
																{/foreach}
															</optgroup>
														</select>
													</span>
												{else}
													<label class="muted pull-left marginRight10px">{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}{vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE_NAME)}</label>
												{/if}
											{else if $FIELD_MODEL->get('uitype') eq "83"}
												{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) COUNTER=$COUNTER MODULE=$MODULE}
											{else}
												{vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE_NAME)}
											{/if}
											{if $isReferenceField neq "reference"}</label>{/if}
									</div>
									{if $FIELD_MODEL->get('uitype') neq "83"}
										<div class="{if $FIELD_MODEL->get('uitype') neq "300"}col-md-9{/if} fieldValue" {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '20'} colspan="3" {assign var=COUNTER value=$COUNTER+1}{elseif $FIELD_MODEL->get('uitype') eq '300'} colspan="4" {assign var=COUNTER value=$COUNTER+1} {/if}>
											<div class="row">
												<div class="">						    
													{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
												</div>
											</div>
										</div>
									{/if}
									{if $BLOCK_FIELDS|@count eq 1 and $FIELD_MODEL->get('uitype') neq "19" and $FIELD_MODEL->get('uitype') neq "20" and $FIELD_MODEL->get('uitype') neq "30" and $FIELD_MODEL->get('uitype') neq '300' and $FIELD_MODEL->get('name') neq "recurringtype"}
										<td class="{$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td>
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
				<br>
			{/if}
		{/foreach}
	{/strip}
