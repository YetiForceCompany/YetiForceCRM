{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<div class='verticalScroll'>
		<div class='editViewContainer'>
			<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
				{assign var=QUALIFIED_MODULE_NAME value={$QUALIFIED_MODULE}}
				{assign var=IS_PARENT_EXISTS value=strpos($MODULE,":")}
				{if $PARENT_MODULE neq ''}
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="parent" value="{$PARENT_MODULE}" />
					<input type="hidden" value="{$VIEW}" name="view" />
				{else}
					<input type="hidden" name="module" value="{$MODULE}" />
				{/if}
				{if !empty($RECORD_ID)}
					<input type="hidden" name="record" id="recordId" value="{$RECORD_ID}" />
					<input type="hidden" name="fromView" value="Edit" />
					{assign var="FROM_VIEW" value='Edit'}
					{if $RECORD_ID && !empty($RECORD_ACTIVITY_NOTIFIER)}
						<input type="hidden" id="recordActivityNotifier" data-interval="{App\Config::performance('recordActivityNotifierInterval', 10)}" data-record="{$RECORD_ID}" data-module="{$MODULE}" />
					{/if}
				{else}
					<input type="hidden" name="fromView" value="Create" />
					{assign var="FROM_VIEW" value='Create'}
				{/if}
				<input type="hidden" name="action" value="Save" />
				{if $IS_RELATION_OPERATION }
					<input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}" />
					<input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}" />
					<input type="hidden" name="relationOperation" value="{$IS_RELATION_OPERATION}" />
				{/if}
				<input type="hidden" id="preSaveValidation" value="{!empty(\App\EventHandler::getByType(\App\EventHandler::EDIT_VIEW_PRE_SAVE, $MODULE_NAME))}" />
				<input type="hidden" class="js-change-value-event" value="{\App\EventHandler::getVarsByType(\App\EventHandler::EDIT_VIEW_CHANGE_VALUE, $MODULE_NAME, [$RECORD, $FROM_VIEW])}" />
				{if !empty($MAPPING_RELATED_FIELD)}
					<input type="hidden" name="mappingRelatedField" value='{\App\Purifier::encodeHtml($MAPPING_RELATED_FIELD)}' />
				{/if}
				{if !empty($LIST_FILTER_FIELDS)}
					<input type="hidden" name="listFilterFields" value='{\App\Purifier::encodeHtml($LIST_FILTER_FIELDS)}' />
				{/if}
				<input name="defaultOtherEventDuration" value="{\App\Purifier::encodeHtml($USER_MODEL->get('othereventduration'))}" type="hidden" />
				{if $MODE === 'duplicate'}
					<input type="hidden" name="_isDuplicateRecord" value="true" />
					<input type="hidden" name="_duplicateRecord" value="{\App\Request::_get('record')}" />
				{/if}
				{if !empty($RECORD_CONVERTER)}
					<input type="hidden" name="recordConverter" value="{$RECORD_CONVERTER}" />
					<input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}" />
				{/if}
				{foreach from=$RECORD->getModule()->getFieldsByDisplayType(9) item=FIELD key=FIELD_NAME}
					<input type="hidden" name="{$FIELD_NAME}" value="{$FIELD->getEditViewValue($RECORD->get($FIELD_NAME),$RECORD)}" />
				{/foreach}
				{assign var="BREADCRUMBS_ACTIVE" value=App\Config::layout('breadcrumbs')}
				{if $BREADCRUMBS_ACTIVE}
					<div class='o-breadcrumb widget_header row mb-3'>
						<div class="col-md-8">
							{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
						</div>
					</div>
				{/if}
				<div class="row mb-3 mx-0 {if !$BREADCRUMBS_ACTIVE}mt-3{/if}">
					{if $EDIT_VIEW_LAYOUT}
						{assign var=COLUMNS_SIZES value=['col-xl-4', 'col-xl-8']}
					{else}
						{assign var=COLUMNS_SIZES value=['col-md-12']}
					{/if}
					{foreach item=COLUMN_SIZE from=$COLUMNS_SIZES}
						<div class="{$COLUMN_SIZE} px-2">
							{if $EDIT_VIEW_LAYOUT && 'col-xl-8' === $COLUMN_SIZE}
								{if 1 === $MODULE_TYPE}
									{include file=\App\Layout::getTemplatePath('Edit/Inventory.tpl', $MODULE)}
								{/if}
								{assign var=RECORD_STRUCTURE value=$RECORD_STRUCTURE_RIGHT}
							{else}
								{assign var=RECORD_STRUCTURE value=$RECORD_STRUCTURE}
							{/if}
							{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name="EditViewBlockLevelLoop"}
								{if $BLOCK_FIELDS|@count lte 0}{continue}{/if}
								{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL]}
								{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
								{assign var=IS_DYNAMIC value=$BLOCK->isDynamic()}
								{assign var=BLOCK_ICON value=$BLOCK->get('icon')}
								<div class="js-toggle-panel c-panel c-panel--edit mb-3"
									data-js="click|data-dynamic" {if $IS_DYNAMIC} data-dynamic="true" {/if}
									data-label="{$BLOCK_LABEL}">
									<div class="blockHeader c-panel__header align-items-center">
										{if in_array($BLOCK_LABEL, $ADDRESS_BLOCK_LABELS)}
											{assign var=SEARCH_ADDRESS value=TRUE}
										{else}
											{assign var=SEARCH_ADDRESS value=FALSE}
										{/if}
										<span class="u-cursor-pointer js-block-toggle fas fa-angle-right m-2 {if !($IS_HIDDEN)}d-none{/if}"
											data-js="click" data-mode="hide"
											data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
										<span class="u-cursor-pointer js-block-toggle fas fa-angle-down m-2 {if ($IS_HIDDEN)}d-none{/if}"
											data-js="click" data-mode="show"
											data-id={$BLOCK_LIST[$BLOCK_LABEL]->get('id')}></span>
										<h5>{if !empty($BLOCK_ICON)}<span class="{$BLOCK_ICON} mr-2"></span>{/if}{\App\Language::translate($BLOCK_LABEL, $QUALIFIED_MODULE_NAME)}</h5>
									</div>
									<div class="c-panel__body c-panel__body--edit blockContent js-block-content {if $IS_HIDDEN}d-none{/if}"
										data-js="display">
										{assign var=PROVIDER value=\App\Map\Address::getActiveProviders()}
										{if in_array($BLOCK_LABEL, $ADDRESS_BLOCK_LABELS)}
											<div class="{if $SEARCH_ADDRESS && $PROVIDER && ($WIDTHTYPE eq 'narrow')} pb-1 {else} pb-2 {/if} pt-2 adressAction row justify-content-center">
												{include file=\App\Layout::getTemplatePath('BlockHeader.tpl', $MODULE) PROVIDER=$PROVIDER}
											</div>
										{/if}
										<div class="row">
											{assign var=COUNTER value=0}
											{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
												{if ($FIELD_NAME === 'time_start' || $FIELD_NAME === 'time_end') && ($MODULE === 'OSSTimeControl' || $MODULE === 'Reservations')}{continue}{/if}
												{if $FIELD_MODEL->getUIType() eq '20' || $FIELD_MODEL->getUIType() eq '300'}
													{if $COUNTER eq '1'}
													</div>
													<div class="row">
														{assign var=COUNTER value=0}
													{/if}
												{/if}
												{if $COUNTER eq 2}
												</div>
												<div class="row">
													{assign var=FIELD_PARAMS value=$FIELD_MODEL->getFieldParams()}
													{if !empty($FIELD_PARAMS['editWidth'])}
														{assign var=EDIT_WIDTH value=$FIELD_PARAMS['editWidth']}
													{else}
														{assign var=EDIT_WIDTH value=''}
													{/if}
													{assign var=COUNTER value=1}
												{else}
													{assign var=COUNTER value=$COUNTER+1}
												{/if}
												{if isset($RECORD_STRUCTURE_RIGHT)}
													<div class="col-sm-12 fieldRow row form-group align-items-center my-1 js-field-block-column{if $FIELD_MODEL->get('hideField')} d-none{/if}" data-field="{$FIELD_MODEL->getFieldName()}" data-js="container">
													{else}
														<div class="{if $FIELD_MODEL->getUIType() eq "300"} col-md-12 m-auto
												{elseif !empty($EDIT_WIDTH)} {$EDIT_WIDTH}
												{else} col-sm-6 {/if} fieldRow row form-group align-items-center my-1 js-field-block-column {if $FIELD_MODEL->get('hideField')} d-none {/if}" data-field="{$FIELD_MODEL->getFieldName()}" data-js="container">
														{/if}
														{assign var=HELPINFO_LABEL value=\App\Language::getTranslateHelpInfo($FIELD_MODEL, $VIEW)}
														<label class="flCT_{$MODULE_NAME}_{$FIELD_MODEL->getFieldName()} my-0 {if !empty($EDIT_WIDTH) && ($EDIT_WIDTH eq 'col-md-12')} {$EDIT_WIDTH} mr-auto pl-2 {else} col-lg-12 col-xl-3 text-lg-left text-xl-right {/if}  fieldLabel u-text-small-bold">
															{if $FIELD_MODEL->isMandatory() eq true}
																<span class="redColor">*</span>
															{/if}
															{if $HELPINFO_LABEL}
																<a href="#" class="js-help-info float-right u-cursor-pointer" title="" data-placement="top" data-content="{$HELPINFO_LABEL}"
																	data-original-title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModuleName())}">
																	<span class="fas fa-info-circle"></span>
																</a>
															{/if}
															{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE_NAME)}
														</label>
														<div class="{$WIDTHTYPE} {$WIDTHTYPE_GROUP} w-100 fieldValue {if !empty($EDIT_WIDTH)} {$EDIT_WIDTH} {elseif ($FIELD_MODEL->getUIType() neq "300") && empty($EDIT_WIDTH)} col-lg-12 col-xl-9 {/if}" {if $FIELD_MODEL->getUIType() eq '20'} colspan="3" {assign var=COUNTER value=$COUNTER+1}{elseif $FIELD_MODEL->getUIType() eq '300'} colspan="4" {assign var=COUNTER value=$COUNTER+1} {/if}>
															{if $FIELD_MODEL->getUIType() eq "300"}
																<label class="u-text-small-bold">{if $FIELD_MODEL->isMandatory() eq true}<span class="redColor">*</span>{/if}
																	{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}
																</label>
															{/if}
															{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
														</div>
													</div>
												{/foreach}
											</div>
										</div>
									</div>
								{/foreach}
							</div>
						{/foreach}
					</div>
					{if 1 === $MODULE_TYPE && !isset($RECORD_STRUCTURE_RIGHT)}
						{include file=\App\Layout::getTemplatePath('Edit/Inventory.tpl', $MODULE)}
					{/if}
{/strip}
