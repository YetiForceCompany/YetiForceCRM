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

        <form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
            {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
            {if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
                <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
            {/if}

			{foreach from=$APIADDRESS item=item key=key}
				{if !empty($item['nominatim'])}
					<input type="hidden" name="apiAddress" value='{$item['key']}' data-max-num="{$APIADDRESS['global']['result_num']}" data-api-name="{$key}" data-url="{$item['source']}" data-lenght="{$APIADDRESS['global']['min_lenght']}"/>
				{/if}
			{/foreach}

            {if !empty($MAPPING_RELATED_FIELD)}
                <input type="hidden" name="mappingRelatedField" value='{Vtiger_Util_Helper::toSafeHTML($MAPPING_RELATED_FIELD)}' />
            {/if}
            {assign var=QUALIFIED_MODULE_NAME value={$QUALIFIED_MODULE}}
            {assign var=IS_PARENT_EXISTS value=strpos($MODULE,":")}
            {if $PARENT_MODULE neq ''}
                <input type="hidden" name="module" value="{$MODULE}" />
                <input type="hidden" name="parent" value="{$PARENT_MODULE}" />
				<input type="hidden" value="{$VIEW}" name="view"/>
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
			<div class='widget_header row'>
				<div class="col-md-8">
					{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
				</div>
				<div class="col-md-4">
					<div class="contentHeader">
						{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
						<span class="pull-right">
							<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE_NAME)}</strong></button>&nbsp;&nbsp;
							<button class="btn btn-warning" type="reset" onclick="javascript:window.history.back();"><strong>{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE_NAME)}</strong></button>
						</span>
						<span class="pull-right">
							{foreach item=LINK from=$EDITVIEW_LINKS['EDIT_VIEW_HEADER']}
								{include file='ButtonLink.tpl'|@vtemplate_path:$MODULE BUTTON_VIEW='editViewHeader'}
								&nbsp;&nbsp;
							{/foreach}
						</span>
						<div class="clearfix"></div>
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
						{if $APIADDRESS_ACTIVE eq true && ($BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_MAILING_INFORMATION' || $BLOCK_LABEL eq 'LBL_ADDRESS_DELIVERY_INFORMATION')}
							{assign var=APIADDRESFIELD value=TRUE}
						{else}
							{assign var=APIADDRESFIELD value=FALSE}
						{/if}
						<div class="iconCollapse">
							<span class="cursorPointer blockToggle glyphicon glyphicon-menu-right {if !($IS_HIDDEN)}hide{/if}" data-mode="hide" data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
							<span class="cursorPointer blockToggle glyphicon glyphicon glyphicon-menu-down {if ($IS_HIDDEN)}hide{/if}" data-mode="show" data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
							<h4>{vtranslate($BLOCK_LABEL, $QUALIFIED_MODULE_NAME)}</h4>
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
										<label class="muted">
											{if $FIELD_MODEL->isMandatory() eq true}<span class="redColor">*</span>{/if}
											{if in_array($VIEW,$HELPINFO) && vtranslate($HELPINFO_LABEL, 'HelpInfo') neq $HELPINFO_LABEL}
												<a href="#" class="HelpInfoPopover pull-right" title="" data-placement="auto top" data-content="{htmlspecialchars(vtranslate($MODULE|cat:'|'|cat:$FIELD_MODEL->get('label'), 'HelpInfo'))}" data-original-title='{vtranslate($FIELD_MODEL->get("label"), $MODULE)}'><span class="glyphicon glyphicon-info-sign"></span></a>
											{/if}
											{vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE_NAME)}
										</label>
									</div>
									<div class="{$WIDTHTYPE} {if $FIELD_MODEL->get('uitype') neq "300"}col-md-9{/if} fieldValue" {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '20'} colspan="3" {assign var=COUNTER value=$COUNTER+1}{elseif $FIELD_MODEL->get('uitype') eq '300'} colspan="4" {assign var=COUNTER value=$COUNTER+1} {/if}>
										<div class="row">
											<div class="">						    
												{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
											</div>
										</div>
									</div>
								</div>
							{/foreach}
						</div>
					</div>
				</div>
			{/if}
		{/foreach}
	{/strip}
