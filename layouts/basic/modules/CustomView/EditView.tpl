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
	<form class="form-horizontal" id="CustomView" name="CustomView" method="post" action="index.php">
		{if $RECORD_ID}
			<input type="hidden" name="record" id="record" value="{$RECORD_ID}" />
		{/if}
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="action" value="Save" />
		<input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
		<input type="hidden" id="stdfilterlist" name="stdfilterlist" value="" />
		<input type="hidden" id="advfilterlist" name="advfilterlist" value="" />
		<input type="hidden" id="status" name="status" value="{$CV_PRIVATE_VALUE}" />
		<input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}" />
		<input type="hidden" name="date_filters" data-value='{\App\Purifier::encodeHtml(\App\Json::encode($DATE_FILTERS))}' />
		<div class='widget_header row customViewHeader'>
			<div class="col-sm-5 col-12">
				{if !$RECORD_ID}
					{assign var=BREADCRUMB_TITLE value=\App\Language::translate('LBL_VIEW_CREATE',$MODULE)}
				{else}
					{assign var=BREADCRUMB_TITLE value=$CUSTOMVIEW_MODEL->get('viewname')}
				{/if}
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
			</div>
			<div class="col-sm-7 col-12 btn-toolbar p-2 childrenMarginRight justify-content-end" role="toolbar">
				<div class="btn-group iconPreferences btn-group-toggle" data-toggle="buttons">
					<label class="btn btn-light{if $CUSTOMVIEW_MODEL->isDefault()} active btn-primary{/if}" title="{\App\Language::translate('LBL_SET_AS_DEFAULT',$MODULE)}" >
						<input id="setdefault" name="setdefault" type="checkbox" {if $CUSTOMVIEW_MODEL->isDefault()}checked="checked"{/if} value="1" />
						<span class="far fa-heart" data-check="fas fa-heart" data-unchecked="far fa-heart"></span>
					</label>
					<label class="btn btn-light{if $CUSTOMVIEW_MODEL->isSetPublic()} active btn-primary{/if}" title="{\App\Language::translate('LBL_SET_AS_PUBLIC',$MODULE)}">
						<input id="status" name="status" type="checkbox" {if $CUSTOMVIEW_MODEL->isSetPublic()} value="{$CUSTOMVIEW_MODEL->get('status')}" checked="checked" {else} value="{$CV_PENDING_VALUE}" {/if} />
						<span class="far fa-eye-slash" data-check="fas fa-eye" data-unchecked="fa-eye-slash"></span>
					</label>
					<label class="btn btn-light{if $CUSTOMVIEW_MODEL->isFeatured(true)} active btn-primary{/if}" title="{\App\Language::translate('LBL_FEATURED',$MODULE)}">
						<input id="featured" name="featured" type="checkbox" {if $CUSTOMVIEW_MODEL->isFeatured(true)} checked="checked"{/if} value="1" />
						<span class="far fa-star" data-check="fas fa-star" data-unchecked="far fa-star"></span>
					</label>
					<label class="btn btn-light{if $CUSTOMVIEW_MODEL->get('setmetrics')} active btn-primary{/if}" title="{\App\Language::translate('LBL_LIST_IN_METRICS',$MODULE)}">
						<input id="setmetrics" name="setmetrics" type="checkbox" {if $CUSTOMVIEW_MODEL->get('setmetrics') eq '1'}checked="checked"{/if} value="1" />
						<span class="fas fa-desktop" data-check="fas fa-desktop" data-unchecked="fas fa-desktop"></span>
					</label>
				</div>
				<button class="btn btn-success" id="customViewSubmit" type="submit"><span
							class="fa fa-check u-mr-5px"></span><strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong></button>
				<button class="btn btn-danger" type="reset" onClick="window.location.reload()"><span
							class="fa fa-times u-mr-5px"></span>{\App\Language::translate('LBL_CANCEL', $MODULE)}</button>
			</div>
		</div>
		{assign var=SELECTED_FIELDS value=$CUSTOMVIEW_MODEL->getSelectedFields()}
		<div class="childrenMarginTopX">
			<div class="js-toggle-panel c-panel" data-js="click">
				<div class="blockHeader  c-panel__header">
					<span class="iconToggle fas fa-chevron-down small m-1 mt-2" data-hide="fas fa-chevron-right" data-show="fas fa-chevron-down"></span>
					<h5 class="">{\App\Language::translate('LBL_BASIC_DETAILS',$MODULE)}</h5>
				</div>
				<div class="c-panel__body py-1">
					<div class="form-group">
						<div class="row col-md-5">
							<label class="float-left col-form-label "><span class="redColor">*</span> {\App\Language::translate('LBL_VIEW_NAME',$MODULE)}:</label>
							<div class="col-md-7">
								<input type="text" id="viewname" class="form-control" data-validation-engine="validate[required]" name="viewname" value="{$CUSTOMVIEW_MODEL->get('viewname')}" />
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class=" col-form-label"><span class="redColor">*</span> {\App\Language::translate('LBL_CHOOSE_COLUMNS',$MODULE)} ({\App\Language::translate('LBL_MAX_NUMBER_FILTER_COLUMNS')}):</label>
						<div class="columnsSelectDiv col-md-12">
							{assign var=MANDATORY_FIELDS value=[]}
							<div class="">
								<select data-placeholder="{\App\Language::translate('LBL_ADD_MORE_COLUMNS',$MODULE)}" multiple class="columnsSelect form-control" id="viewColumnsSelect">
									{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
										<optgroup label='{\App\Language::translate($BLOCK_LABEL, $SOURCE_MODULE)}'>
											{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
												{if $FIELD_MODEL->isMandatory()}
													{array_push($MANDATORY_FIELDS, $FIELD_MODEL->getCustomViewColumnName())}
												{/if}
												<option value="{$FIELD_MODEL->getCustomViewColumnName()}" data-field-name="{$FIELD_NAME}"
														{if in_array($FIELD_MODEL->getCustomViewColumnName(), $SELECTED_FIELDS)}
															selected
														{/if}
														>{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
											{if $FIELD_MODEL->isMandatory() eq true} <span>*</span> {/if}
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
												<option value="{$FIELD_MODEL->getCustomViewColumnName()}" data-field-name="{$FIELD_NAME}"
														{if in_array($FIELD_MODEL->getCustomViewColumnName(), $SELECTED_FIELDS)}
															selected
														{/if}
														>{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $SOURCE_MODULE)}
											{if $FIELD_MODEL->isMandatory() eq true} <span>*</span> {/if}
											</option>
										{/foreach}
										</optgroup>
									{/foreach}
								</select>
							</div>
							<input type="hidden" name="columnslist" value='{\App\Json::encode($SELECTED_FIELDS)}' />
							<input id="mandatoryFieldsList" type="hidden" value='{\App\Json::encode($MANDATORY_FIELDS)}' />
						</div>
					</div>
					<div class="form-group marginbottomZero">
						<div class="row col-md-5">
							<label class="float-left col-form-label ">{\App\Language::translate('LBL_COLOR_VIEW',$MODULE)}:</label>
							<div class="col-md-7">
								<div class="input-group">
									<input type="text" class="form-control colorPicker" name="color" value="{$CUSTOMVIEW_MODEL->get('color')}" />
									<span class="input-group-addon" style="background-color: {$CUSTOMVIEW_MODEL->get('color')};">&nbsp;&nbsp;</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="js-toggle-panel c-panel" data-js="click">
				<div class="blockHeader c-panel__header">
					<span class="iconToggle fas fa-chevron-right small m-1 mt-2" data-hide="fas fa-chevron-right" data-show="fas fa-chevron-down"></span>
					<h5 class="">{\App\Language::translate('LBL_DESCRIPTION_INFORMATION',$MODULE)}</h5>
				</div>
				<div class="c-panel__body py-1 d-none">
					<textarea name="description" id="description" class="js-editor" data-js="ckeditor">{$CUSTOMVIEW_MODEL->get('description')}</textarea>
				</div>
			</div>
			<div class="js-toggle-panel c-panel" data-js="click">
				<div class="blockHeader c-panel__header">
					<span class="iconToggle fas fa-chevron-down small m-1 mt-2" data-hide="fas fa-chevron-right" data-show="fas fa-chevron-down"></span>
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
		<div class="filterActions pt-2">
			<button class="cancelLink float-right btn btn-danger" type="reset" onClick="window.location.reload()"><span
						class="fa fa-times u-mr-5px"></span>{\App\Language::translate('LBL_CANCEL', $MODULE)}</button>
			<button class="btn btn-success float-right mr-1" id="customViewSubmit" type="submit"><strong><span
							class="fa fa-check u-mr-5px"></span>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong></button>
		</div>
	</form>
{/strip}
