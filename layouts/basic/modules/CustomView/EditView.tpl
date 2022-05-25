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
	<div class="tpl-CustomView-EditView modal fade js-filter-modal__container" data-js="container">
		<div class="modal-dialog modal-fullscreen">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<span class="fas fa-filter fa-sm mr-1"></span>
						{\App\Language::translate('LBL_CREATE_NEW_FILTER')}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form class="form-horizontal" id="CustomView" name="CustomView" method="post" action="index.php">
					{if !empty($RECORD_ID)}
						<input type="hidden" name="record" id="record" value="{$RECORD_ID}" />
					{/if}
					{if !empty($MID)}
						<input type="hidden" name="mid" value="{$MID}" />
					{/if}
					<input type="hidden" name="module" value="{$MODULE_NAME}" />
					<input type="hidden" name="action" value="Save" />
					<input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
					<input type="hidden" id="stdfilterlist" name="stdfilterlist" value="" />
					<input type="hidden" id="advfilterlist" name="advfilterlist" value="" />
					<input type="hidden" id="advancedConditions" name="advanced_conditions" value="" />
					<input type="hidden" id="status" name="status" value="{$CV_PRIVATE_VALUE}" />
					<input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}" />
					{assign var=CV_SELECTED_FIELDS value=$CUSTOMVIEW_MODEL->getSelectedFields()}
					{assign var=SELECTED_FIELDS value=array_keys($CV_SELECTED_FIELDS)}
					<div class="modal-body">
						<div class="js-toggle-panel c-panel" data-js="click">
							<div class="blockHeader c-panel__header py-2 js-toggle-block" data-js="click">
								<span class="js-toggle-icon fas fa-chevron-down fa-xs m-1 mt-2 mr-3" data-hide="fas fa-chevron-right" data-show="fas fa-chevron-down" data-js="container"></span>
								<h5>
									<span class="fas fa-columns mr-2" aria-hidden="true"></span>
									{\App\Language::translate('LBL_BASIC_DETAILS',$MODULE_NAME)}
								</h5>
							</div>
							<div class="c-panel__body py-1">
								<div class="form-row">
									<div class="d-flex col-md-5">
										<label class="float-left col-form-label ">
											<span class="redColor">*</span> {\App\Language::translate('LBL_VIEW_NAME',$MODULE_NAME)}
											:</label>
										<div class="col-md-7">
											<input type="text" id="viewname" class="form-control" data-validation-engine="validate[required]" name="viewname" value="{$CUSTOMVIEW_MODEL->get('viewname')}" />
										</div>
									</div>
									<div class="d-flex col-md-5">
										<label class="float-left col-form-label ">{\App\Language::translate('LBL_COLOR_VIEW',$MODULE_NAME)}
											:</label>
										<div class="col-md-7">
											{assign var=COLOR value=$CUSTOMVIEW_MODEL->get('color')}
											<div class="input-group js-color-picker" data-js="color-picker">
												<input type="text" class="form-control js-color-picker__field" name="color"
													value="{$COLOR}" />
												<div class="input-group-append">
													<div class="input-group-text">
														<span class="c-circle c-circle--small js-color-picker__color" style="background-color: {$COLOR}"></span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group">
									<label class=" col-form-label"><span class="redColor">*</span> {\App\Language::translate('LBL_CHOOSE_COLUMNS',$MODULE_NAME)}</label>
									<div class="columnsSelectDiv col-md-12">
										{assign var=MANDATORY_FIELDS value=[]}
										<div class="">
											<select data-placeholder="{\App\Language::translate('LBL_ADD_MORE_COLUMNS',$MODULE_NAME)}"
												multiple="multiple"
												class="select2 form-control js-view-columns-select"
												data-select-cb="registerSelectSortable"
												id="viewColumnsSelect"
												data-js="appendTo | select2 | sortable">
												{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
													<optgroup
														label="{\App\Language::translate($BLOCK_LABEL, $SOURCE_MODULE)}">
														{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
															{assign var=CUSTOM_VIEW_COLUMN_NAME value=$FIELD_MODEL->getCustomViewSelectColumnName()}
															{if $FIELD_MODEL->isMandatory()}
																{append var="MANDATORY_FIELDS" value=$CUSTOM_VIEW_COLUMN_NAME}
															{/if}
															{assign var=ELEMENT_POSITION_IN_ARRAY value=array_search($CUSTOM_VIEW_COLUMN_NAME, $SELECTED_FIELDS)}
															<option value="{$CUSTOM_VIEW_COLUMN_NAME}"
																data-field-name="{$FIELD_NAME}"
																data-field-label="{{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}}"
																data-custom-label="{if isset($CV_SELECTED_FIELDS[$CUSTOM_VIEW_COLUMN_NAME])}
																{\App\Purifier::encodeHtml($CV_SELECTED_FIELDS[$CUSTOM_VIEW_COLUMN_NAME])}{/if}"
																{if $ELEMENT_POSITION_IN_ARRAY !== false}
																	data-sort-index="{$ELEMENT_POSITION_IN_ARRAY}" selected="selected"
																{/if}
																data-js="data-sort-index|data-field-name">
																{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
																{if $FIELD_MODEL->isMandatory() eq true}
																	<span>*</span>
																{/if}
															</option>
														{/foreach}
													</optgroup>
												{/foreach}
												{foreach key=MODULE_KEY item=RECORD_STRUCTURE_FIELD from=$RECORD_STRUCTURE_RELATED_MODULES}
													{foreach key=RELATED_FIELD_NAME item=RECORD_STRUCTURE from=$RECORD_STRUCTURE_FIELD}
														{assign var=RELATED_FIELD_LABEL value=Vtiger_Module_Model::getInstance($SOURCE_MODULE)->getFieldByName($RELATED_FIELD_NAME)->getFieldLabel()}
														{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
															<optgroup
																label="{\App\Language::translate($RELATED_FIELD_LABEL, $SOURCE_MODULE)}&nbsp;-&nbsp;{\App\Language::translate($MODULE_KEY, $MODULE_KEY)}&nbsp;-&nbsp;{\App\Language::translate($BLOCK_LABEL, $MODULE_KEY)}">
																{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
																	{assign var=CUSTOM_VIEW_COLUMN_NAME value=$FIELD_MODEL->getCustomViewSelectColumnName($RELATED_FIELD_NAME)}
																	{assign var=ELEMENT_POSITION_IN_ARRAY value=array_search($CUSTOM_VIEW_COLUMN_NAME, $SELECTED_FIELDS)}
																	<option value="{$CUSTOM_VIEW_COLUMN_NAME}"
																		data-field-name="{$FIELD_NAME}"
																		data-field-label="{\App\Language::translate($RELATED_FIELD_LABEL, $SOURCE_MODULE)}
																		&nbsp;-&nbsp;{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_KEY)}"
																		data-custom-label="{if isset($CV_SELECTED_FIELDS[$CUSTOM_VIEW_COLUMN_NAME])}
																		{\App\Purifier::encodeHtml($CV_SELECTED_FIELDS[$CUSTOM_VIEW_COLUMN_NAME])}{/if}"
																		{if $ELEMENT_POSITION_IN_ARRAY !== false}
																			data-sort-index="{$ELEMENT_POSITION_IN_ARRAY}" selected="selected"
																		{/if}
																		data-js="data-sort-index|data-field-name">
																		{\App\Language::translate($RELATED_FIELD_LABEL, $SOURCE_MODULE)}
																		&nbsp;-&nbsp;{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_KEY)}
																	</option>
																{/foreach}
															</optgroup>
														{/foreach}
													{/foreach}
												{/foreach}
											</select>
										</div>
										<input type="hidden" name="columnslist"
											value="{\App\Purifier::encodeHtml(\App\Json::encode($SELECTED_FIELDS))}"
											class="js-columnslist"
											data-js="val" />
										<input id="mandatoryFieldsList" type="hidden"
											value="{\App\Purifier::encodeHtml(\App\Json::encode($MANDATORY_FIELDS))}" />
									</div>
								</div>
							</div>
						</div>
						<div class="js-toggle-panel c-panel" data-js="click">
							<div class="blockHeader c-panel__header py-2 js-toggle-block" data-js="click">
								<span class="js-toggle-icon fas fa-chevron-right fa-xs m-1 mt-2 mr-3" data-hide="fas fa-chevron-right" data-show="fas fa-chevron-down" data-js="container"></span>
								<h5>
									<span class="yfi-company-detlis mr-2" aria-hidden="true"></span>
									{\App\Language::translate('LBL_SET_CUSTOM_COLUMNS_LABEL',$MODULE_NAME)}
								</h5>
							</div>
							<div class="c-panel__body py-1 d-none">
								<input type="hidden" name="customFieldNames" value="" class="js-custom-field-names" data-js="val">
								<div class="js-custom-name-fields">
								</div>
							</div>
						</div>
						<div class="js-toggle-panel c-panel" data-js="click">
							<div class="blockHeader c-panel__header py-2 js-toggle-block" data-js="click">
								<span class="js-toggle-icon fas fa-chevron-right fa-xs m-1 mt-2 mr-3" data-hide="fas fa-chevron-right" data-show="fas fa-chevron-down" data-js="container"></span>
								<h5>
									<span class="yfi-company-detlis mr-2" aria-hidden="true"></span>
									{\App\Language::translate('LBL_DESCRIPTION_INFORMATION',$MODULE_NAME)}
								</h5>
							</div>
							<div class="c-panel__body py-1 d-none">
								<textarea name="description" id="description" class="js-editor" data-purify-mode="Html" data-js="ckeditor">{$CUSTOMVIEW_MODEL->get('description')}</textarea>
							</div>
						</div>
						<div class="js-toggle-panel c-panel" data-js="click">
							<div class="blockHeader c-panel__header py-2 js-toggle-block" data-js="click">
								<span class="js-toggle-icon fas fa-chevron-right fa-xs m-1 mt-2 mr-3" data-hide="fas fa-chevron-right" data-show="fas fa-chevron-down" data-js="container"></span>
								<h5>
									<span class="mdi mdi-content-duplicate mr-2" aria-hidden="true"></span>
									{\App\Language::translate('LBL_FIND_DUPLICATES',$MODULE_NAME)}
								</h5>

							</div>
							<div class="c-panel__body py-1 d-none">
								<input type="hidden" name="duplicatefields" value="">
								<button type="button" class="btn btn-success btn-sm js-duplicate-add-field mb-1" data-js="click">
									<span class="fa fa-plus mr-1"></span>{\App\Language::translate('LBL_ADD_FIELD',$MODULE_NAME)}
								</button>
								<div class="js-duplicates-field-template js-duplicates-row d-none" data-js="container|clone">
									{include file=\App\Layout::getTemplatePath('DuplicateRow.tpl', $MODULE_NAME)}
								</div>
								<div class="js-duplicates-container" data-js="container">
									{foreach from=$DUPLICATE_FIELDS item=FIELD}
										<div class="js-duplicates-row my-1" data-js="container">
											{include file=\App\Layout::getTemplatePath('DuplicateRow.tpl', $MODULE_NAME)}
										</div>
									{/foreach}
								</div>
							</div>
						</div>
						{include file=\App\Layout::getTemplatePath('CustomView/AdvCondBody.tpl', $MODULE_NAME) HIDDE_BLOCKS=true}
						<div class="js-toggle-panel c-panel" data-js="click">
							<div class="blockHeader c-panel__header py-2 js-toggle-block" data-js="click">
								<span class="js-toggle-icon fas fa-chevron-down fa-xs m-1 mt-2 mr-3" data-hide="fas fa-chevron-right" data-show="fas fa-chevron-down" data-js="container"></span>
								<h5>
									<span class="yfi yfi-users-2 mr-2"></span>
									{\App\Language::translate('LBL_CHOOSE_FILTER_CONDITIONS', $MODULE_NAME)}:
								</h5>
							</div>
							<div class="c-panel__body py-1">
								<div class="pb-0 js-condition-builder-view" data-js="container">
									<div class="row">
										<span class="col-md-12">
											{include file=\App\Layout::getTemplatePath('ConditionBuilder.tpl') MODULE_NAME=$SOURCE_MODULE}
										</span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer d-flex flex-md-row flex-column justify-content-start">
						<div class="w-75 btn-group js-filter-preferences btn-group-toggle flex-wrap align-items-stretch m-0 mt-1 c-btn-block-sm-down pl-1 flex-xl-row flex-column"
							data-toggle="buttons" data-js="change">
							<label class="c-btn-block-sm-down btn btn-outline-dark{if $CUSTOMVIEW_MODEL->isDefault()} active{/if}" title="{\App\Language::translate('LBL_SET_AS_DEFAULT',$MODULE_NAME)}">
								<input name="setdefault" value="1" type="checkbox"
									class="js-filter-preference"
									data-js="change"
									{if $CUSTOMVIEW_MODEL->isDefault()}checked="checked" {/if}
									id="setdefault"
									autocomplete="off" />
								<span class="{if $CUSTOMVIEW_MODEL->isDefault()}fas{else}far{/if} fa-heart mr-1"
									data-check="fas fa-heart" data-unchecked="far fa-heart"></span>
								{\App\Language::translate('LBL_SET_AS_DEFAULT',$MODULE_NAME)}

							</label>
							<label class="c-btn-block-sm-down mt-1 mt-sm-0 btn btn-outline-dark{if $CUSTOMVIEW_MODEL->isSetPublic()} active{/if}"
								title="{\App\Language::translate('LBL_SET_AS_PUBLIC',$MODULE_NAME)}">
								<input name="status" {if $CUSTOMVIEW_MODEL->isSetPublic()} value="{$CUSTOMVIEW_MODEL->get('status')}" checked="checked" {else} value="{$CV_PENDING_VALUE}" {/if}
									type="checkbox" class="js-filter-preference" data-js="change"
									id="status"
									autocomplete="off" />
								<span class="far {if $CUSTOMVIEW_MODEL->isSetPublic()}fa-eye{else}fa-eye-slash{/if} mr-1"
									data-check="fa-eye" data-unchecked="fa-eye-slash"></span>
								{\App\Language::translate('LBL_SET_AS_PUBLIC',$MODULE_NAME)}
							</label>
							<label class="c-btn-block-sm-down mt-1 mt-sm-0 btn btn-outline-dark{if $CUSTOMVIEW_MODEL->isFeatured()} active{/if}"
								title="{\App\Language::translate('LBL_FEATURED',$MODULE_NAME)}">
								<input name="featured" value="1" type="checkbox"
									class="js-filter-preference"
									data-js="change" id="featured"
									{if $CUSTOMVIEW_MODEL->isFeatured()} checked="checked" {/if}
									{if !$CUSTOMVIEW_MODEL->isFeaturedEditable()} disabled="disabled" {/if}
									autocomplete="off" />
								<span class="{if $CUSTOMVIEW_MODEL->isFeatured()}fas{else}far{/if} fa-star mr-1"
									data-check="fas" data-unchecked="far"></span>
								{\App\Language::translate('LBL_FEATURED',$MODULE_NAME)}
							</label>
							<label class="c-btn-block-sm-down mt-1 mt-sm-0 btn btn-outline-dark{if $CUSTOMVIEW_MODEL->get('setmetrics')} active{/if}"
								title="{\App\Language::translate('LBL_LIST_IN_METRICS',$MODULE_NAME)}">
								<input name="setmetrics" value="1" type="checkbox"
									class="js-filter-preference"
									data-js="change"
									{if $CUSTOMVIEW_MODEL->get('setmetrics') eq '1'}checked="checked" {/if}
									id="setmetrics" autocomplete="off" />
								<span class="c-icon--tripple mr-2">
									<span class="c-icon--tripple__top fas fa-chart-pie"></span>
									<span class="c-icon--tripple__left fas fa-chart-line"></span>
									<span class="c-icon--tripple__right fas fa-chart-area"></span>
								</span>
								{\App\Language::translate('LBL_LIST_IN_METRICS',$MODULE_NAME)}
							</label>
						</div>
						<div class="w-25 d-flex flex-wrap flex-md-nowrap justify-content-end m-0 pr-0 mt-1  c-btn-block-sm-down ml-0 pr-1 pr-md-0">
							<button class="btn btn-success mr-md-1" type="submit">
								<span class="fa fa-check u-mr-5px"></span>{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}
							</button>
							<button class="btn btn-danger mt-1 mt-md-0" type="reset" data-dismiss="modal">
								<span
									class="fa fa-times u-mr-5px"></span>{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}
