{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	<div class='tpl-CustomView-EditView modal fade js-filter-modal__container' tabindex="-1" data-js="container">
		<div class="modal-dialog modal-fullscreen">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<span class="fas fa-plus-circle fa-sm mr-1"></span>
						{\App\Language::translate('LBL_CREATE_NEW_FILTER')}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form class="form-horizontal" id="CustomView" name="CustomView" method="post" action="index.php">
					{if $RECORD_ID}
						<input type="hidden" name="record" id="record" value="{$RECORD_ID}"/>
					{/if}
					<input type="hidden" name="module" value="{$MODULE}"/>
					<input type="hidden" name="action" value="Save"/>
					<input type="hidden" name="source_module" value="{$SOURCE_MODULE}"/>
					<input type="hidden" id="stdfilterlist" name="stdfilterlist" value=""/>
					<input type="hidden" id="advfilterlist" name="advfilterlist" value=""/>
					<input type="hidden" id="status" name="status" value="{$CV_PRIVATE_VALUE}"/>
					<input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}"/>
					<input type="hidden" name="date_filters"
						   data-value='{\App\Purifier::encodeHtml(\App\Json::encode($DATE_FILTERS))}'/>
					{assign var=SELECTED_FIELDS value=$CUSTOMVIEW_MODEL->getSelectedFields()}
					<div class="childrenMarginTopX">
						<div class="js-toggle-panel c-panel" data-js="click">
							<div class="blockHeader  c-panel__header">
					<span class="iconToggle fas fa-chevron-down small m-1 mt-2" data-hide="fas fa-chevron-right"
						  data-show="fas fa-chevron-down"></span>
								<h5 class="">{\App\Language::translate('LBL_BASIC_DETAILS',$MODULE)}</h5>
							</div>
							<div class="c-panel__body py-1">
								<div class="form-group">
									<div class="row col-md-5">
										<label class="float-left col-form-label "><span
													class="redColor">*</span> {\App\Language::translate('LBL_VIEW_NAME',$MODULE)}
											:</label>
										<div class="col-md-7">
											<input type="text" id="viewname" class="form-control"
												   data-validation-engine="validate[required]" name="viewname"
												   value="{$CUSTOMVIEW_MODEL->get('viewname')}"/>
										</div>
									</div>
								</div>
								<div class="form-group">
									<label class=" col-form-label"><span
												class="redColor">*</span> {\App\Language::translate('LBL_CHOOSE_COLUMNS',$MODULE)}
										({\App\Language::translate('LBL_MAX_NUMBER_FILTER_COLUMNS')}):</label>
									<div class="columnsSelectDiv col-md-12">
										{assign var=MANDATORY_FIELDS value=[]}
										<div class="">
											<select data-placeholder="{\App\Language::translate('LBL_ADD_MORE_COLUMNS',$MODULE)}"
													multiple class="columnsSelect form-control js-select2-sortable"
													id="viewColumnsSelect">
												{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
													<optgroup label='{\App\Language::translate($BLOCK_LABEL, $SOURCE_MODULE)}'>
														{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
															{if $FIELD_MODEL->isMandatory()}
																{array_push($MANDATORY_FIELDS, $FIELD_MODEL->getCustomViewColumnName())}
															{/if}
															<option value="{$FIELD_MODEL->getCustomViewColumnName()}"
																	data-field-name="{$FIELD_NAME}"
																	{if in_array($FIELD_MODEL->getCustomViewColumnName(), $SELECTED_FIELDS)}
																		selected
																	{/if}
															>{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
																{if $FIELD_MODEL->isMandatory() eq true}
																	<span>*</span>
																{/if}
															</option>
														{/foreach}
													</optgroup>
												{/foreach}
												{*Required to include event fields for columns in calendar module advanced filter*}
												{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$EVENT_RECORD_STRUCTURE}
													<optgroup label='{\App\Language::translate($BLOCK_LABEL, 'Events')}'>
														{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
															{if $FIELD_MODEL->isMandatory()}
																{array_push($MANDATORY_FIELDS, $FIELD_MODEL->getCustomViewColumnName())}
															{/if}
															<option value="{$FIELD_MODEL->getCustomViewColumnName()}"
																	data-field-name="{$FIELD_NAME}"
																	{if in_array($FIELD_MODEL->getCustomViewColumnName(), $SELECTED_FIELDS)}
																		selected
																	{/if}
															>{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
																{if $FIELD_MODEL->isMandatory() eq true}
																	<span>*</span>
																{/if}
															</option>
														{/foreach}
													</optgroup>
												{/foreach}
											</select>
										</div>
										<input type="hidden" name="columnslist" value='{\App\Json::encode($SELECTED_FIELDS)}'/>
										<input id="mandatoryFieldsList" type="hidden"
											   value='{\App\Json::encode($MANDATORY_FIELDS)}'/>
									</div>
								</div>
								<div class="form-group marginbottomZero">
									<div class="row col-md-5">
										<label class="float-left col-form-label ">{\App\Language::translate('LBL_COLOR_VIEW',$MODULE)}
											:</label>
										<div class="col-md-7">
											<div class="input-group js-color-picker" data-js="color-picker">
												<input type="text" class="form-control" name="color"
													   value="{$CUSTOMVIEW_MODEL->get('color')}"/>
												<div class="input-group-append">
													<div class="input-group-text colorpicker-input-addon"><i></i></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="btn-group js-filter-preferences btn-group-toggle mt-3 flex-wrap"
									 data-toggle="buttons" data-js="change">
									<label class="c-btn-block-sm-down btn btn-outline-dark{if $CUSTOMVIEW_MODEL->isDefault()} active{/if}"
										   title="{\App\Language::translate('LBL_SET_AS_DEFAULT',$MODULE)}">
										<input name="setdefault" value="1" type="checkbox" class="js-filter-preference"
											   data-js="change"
											   {if $CUSTOMVIEW_MODEL->isDefault()}checked="checked"{/if}
											   id="setdefault"
											   autocomplete="off"/>
										<span class="{if $CUSTOMVIEW_MODEL->isDefault()}fas{else}far{/if} fa-heart mr-1"
											  data-check="fas fa-heart" data-unchecked="far fa-heart"
											  data-fa-transform="grow-2"></span>
										{\App\Language::translate('LBL_SET_AS_DEFAULT',$MODULE)}

									</label>
									<label class="c-btn-block-sm-down mt-1 mt-sm-0 btn btn-outline-dark{if $CUSTOMVIEW_MODEL->isSetPublic()} active{/if}"
										   title="{\App\Language::translate('LBL_SET_AS_PUBLIC',$MODULE)}">
										<input name="status" {if $CUSTOMVIEW_MODEL->isSetPublic()} value="{$CUSTOMVIEW_MODEL->get('status')}" checked="checked" {else} value="{$CV_PENDING_VALUE}" {/if}
											   type="checkbox" class="js-filter-preference" data-js="change"
											   id="status"
											   autocomplete="off"/>
										<span class="far {if $CUSTOMVIEW_MODEL->isSetPublic()}fa-eye{else}fa-eye-slash{/if} mr-1"
											  data-check="fa-eye" data-unchecked="fa-eye-slash"
											  data-fa-transform="grow-2"></span>
										{\App\Language::translate('LBL_SET_AS_PUBLIC',$MODULE)}
									</label>
									<label class="c-btn-block-sm-down mt-1 mt-sm-0 btn btn-outline-dark{if $CUSTOMVIEW_MODEL->isFeatured(true)} active{/if}"
										   title="{\App\Language::translate('LBL_FEATURED',$MODULE)}">
										<input name="featured" value="1" type="checkbox" class="js-filter-preference"
											   data-js="change" id="featured"
												{if $CUSTOMVIEW_MODEL->isFeatured(true)} checked="checked"{/if}
											   autocomplete="off"/>
										<span class="{if $CUSTOMVIEW_MODEL->isFeatured(true)}fas{else}far{/if} fa-star mr-1"
											  data-check="fas" data-unchecked="far"
											  data-fa-transform="grow-2"></span>
										{\App\Language::translate('LBL_FEATURED',$MODULE)}
									</label>
									<label class="c-btn-block-sm-down mt-1 mt-sm-0 btn btn-outline-dark{if $CUSTOMVIEW_MODEL->get('setmetrics')} active{/if}"
										   title="{\App\Language::translate('LBL_LIST_IN_METRICS',$MODULE)}">
										<input name="setmetrics" value="1" type="checkbox" class="js-filter-preference"
											   data-js="change"
											   {if $CUSTOMVIEW_MODEL->get('setmetrics') eq '1'}checked="checked"{/if}
											   id="setmetrics" autocomplete="off"/>
										<span class="fa-layers fa-fw mr-2">
								<span class="fas fa-chart-pie" data-fa-transform="shrink-5 up-6"></span>
								<span class="fas fa-chart-line" data-fa-transform="shrink-5 right-7 down-6"></span>
								<span class="fas fa-chart-area" data-fa-transform="shrink-5 left-7 down-6"></span>
							</span>
										{\App\Language::translate('LBL_LIST_IN_METRICS',$MODULE)}
									</label>
								</div>
							</div>
						</div>
						<div class="js-toggle-panel c-panel" data-js="click">
							<div class="blockHeader c-panel__header">
					<span class="iconToggle fas fa-chevron-right small m-1 mt-2" data-hide="fas fa-chevron-right"
						  data-show="fas fa-chevron-down"></span>
								<h5 class="">{\App\Language::translate('LBL_DESCRIPTION_INFORMATION',$MODULE)}</h5>
							</div>
							<div class="c-panel__body py-1 d-none">
								<textarea name="description" id="description" class="js-editor"
										  data-js="ckeditor">{$CUSTOMVIEW_MODEL->get('description')}</textarea>
							</div>
						</div>
						<div class="js-toggle-panel c-panel" data-js="click">
							<div class="blockHeader c-panel__header">
					<span class="iconToggle fas fa-chevron-down small m-1 mt-2" data-hide="fas fa-chevron-right"
						  data-show="fas fa-chevron-down"></span>
								<h5 class="">{\App\Language::translate('LBL_CHOOSE_FILTER_CONDITIONS', $MODULE)}:</h5>
							</div>
							<div class="c-panel__body py-1">
								<div class="filterConditionsDiv">
									<div class="row">
							<span class="col-md-12">
								{include file=\App\Layout::getTemplatePath('AdvanceFilter.tpl')}
							</span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-danger" type="reset" data-dismiss="modal">
							<span class="fa fa-times u-mr-5px"></span>{\App\Language::translate('LBL_CANCEL', $MODULE)}
						</button>
						<button class="btn btn-success mr-1" type="submit">
							<span class="fa fa-check u-mr-5px"></span>{\App\Language::translate('LBL_SAVE', $MODULE)}
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}
