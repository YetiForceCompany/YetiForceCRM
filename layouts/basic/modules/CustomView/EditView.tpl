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
	<form class="form-horizontal" id="CustomView" name="CustomView" method="post" action="index.php">
		<input type="hidden" name="record" id="record" value="{$RECORD_ID}" />
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="action" value="Save" />
		<input type="hidden" name="source_module" value="{$SOURCE_MODULE}"/>
		<input type="hidden" id="stdfilterlist" name="stdfilterlist" value=""/>
		<input type="hidden" id="advfilterlist" name="advfilterlist" value=""/>
		<input type="hidden" id="status" name="status" value="{$CV_PRIVATE_VALUE}"/>
		<input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}">
		<input type="hidden" name="date_filters" data-value='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($DATE_FILTERS))}' />
		<div class='widget_header row customViewHeader'>
			<div class="col-sm-5 col-xs-12">
				{if !$RECORD_ID}
					{assign var=BREADCRUMB_TITLE value='LBL_VIEW_CREATE'}
				{else}
					{assign var=BREADCRUMB_TITLE value=$CUSTOMVIEW_MODEL->get('viewname')}
				{/if}
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			</div>
			<div class="col-sm-7 col-xs-12 btn-toolbar" role="toolbar">
				<div class="btn-group filterActions pull-right">
					<button class="btn btn-warning" type="reset" onClick="window.location.reload()">{vtranslate('LBL_CANCEL', $MODULE)}</button>
				</div>
				<div class="btn-group filterActions pull-right">
					<button class="btn btn-success" id="customViewSubmit" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
				</div>
				<div class="btn-group pull-right pull-left-xs iconPreferences marginRight10" data-toggle="buttons">
					<label class="btn btn-default{if $CUSTOMVIEW_MODEL->isDefault()} active  btn-primary{/if}" title="{vtranslate('LBL_SET_AS_DEFAULT',$MODULE)}" >
						<input id="setdefault" name="setdefault" type="checkbox"  {if $CUSTOMVIEW_MODEL->isDefault()}checked="checked"{/if} value="1">
						<span class="glyphicon glyphicon-heart-empty" data-check="glyphicon-heart" data-unchecked="glyphicon-heart-empty"></span>
					</label>
					<label class="btn btn-default{if $CUSTOMVIEW_MODEL->isSetPublic()} active  btn-primary{/if}" title="{vtranslate('LBL_SET_AS_PUBLIC',$MODULE)}">
						<input id="status" name="status" type="checkbox" {if $CUSTOMVIEW_MODEL->isSetPublic()} value="{$CUSTOMVIEW_MODEL->get('status')}" checked="checked" {else} value="{$CV_PENDING_VALUE}" {/if}>
						<span class="glyphicon glyphicon-eye-close" data-check="glyphicon-eye-open" data-unchecked="glyphicon-eye-close"></span>
					</label>
					<label class="btn btn-default{if $CUSTOMVIEW_MODEL->isFeatured(true)} active btn-primary{/if}" title="{vtranslate('LBL_FEATURED',$MODULE)}">
						<input id="featured" name="featured" type="checkbox"  {if $CUSTOMVIEW_MODEL->isFeatured(true)} checked="checked"{/if} value="1">
						<span class="glyphicon glyphicon-star-empty" data-check="glyphicon-star" data-unchecked="glyphicon-star-empty"></span>
					</label>
					<label class="btn btn-default{if $CUSTOMVIEW_MODEL->get('setmetrics')} active btn-primary{/if}" title="{vtranslate('LBL_LIST_IN_METRICS',$MODULE)}">
						<input id="setmetrics" name="setmetrics" type="checkbox" {if $CUSTOMVIEW_MODEL->get('setmetrics') eq '1'}checked="checked"{/if} value="1">
						<span class="glyphicon glyphicon-blackboard" data-check="glyphicon-heart" data-unchecked="glyphicon-heart-empty"></span>
					</label>
				</div>
			</div>
		</div>	
		{assign var=SELECTED_FIELDS value=$CUSTOMVIEW_MODEL->getSelectedFields()}
		<div class="">
			<div class="panel panel-default row marginLeftZero marginRightZero blockContainer">
				<div class="row blockHeader panel-heading marginLeftZero marginRightZero"><div class="iconCollapse">
						<span class="cursorPointer iconToggle glyphicon glyphicon glyphicon-menu-down" data-hide="glyphicon-menu-right" data-show="glyphicon-menu-down"></span>
						<h4 class="">{vtranslate('LBL_BASIC_DETAILS',$MODULE)}</h4></div></div>
				<div class="panel-body">
					<div class="form-group">
						<div class="row col-md-5">
							<label class="pull-left control-label paddingLeftMd"><span class="redColor">*</span> {vtranslate('LBL_VIEW_NAME',$MODULE)}:</label>
							<div class="col-md-7">
								<input type="text" id="viewname" class="form-control" data-validation-engine="validate[required]" name="viewname" value="{$CUSTOMVIEW_MODEL->get('viewname')}">
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="paddingLeftMd control-label"><span class="redColor">*</span> {vtranslate('LBL_CHOOSE_COLUMNS',$MODULE)} ({vtranslate('LBL_MAX_NUMBER_FILTER_COLUMNS')}):</label>
						<div class="columnsSelectDiv col-md-12">
							{assign var=MANDATORY_FIELDS value=array()}
							<div class="">
								<select data-placeholder="{vtranslate('LBL_ADD_MORE_COLUMNS',$MODULE)}" multiple class="columnsSelect form-control" id="viewColumnsSelect">
									{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
										<optgroup label='{vtranslate($BLOCK_LABEL, $SOURCE_MODULE)}'>
											{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
												{if $FIELD_MODEL->isMandatory()}
													{array_push($MANDATORY_FIELDS, $FIELD_MODEL->getCustomViewColumnName())}
												{/if}
												<option value="{$FIELD_MODEL->getCustomViewColumnName()}" data-field-name="{$FIELD_NAME}"
														{if in_array($FIELD_MODEL->getCustomViewColumnName(), $SELECTED_FIELDS)}
															selected
														{/if}
														>{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
											{if $FIELD_MODEL->isMandatory() eq true} <span>*</span> {/if}
											</option>
										{/foreach}
										</optgroup>
									{/foreach}
									{*Required to include event fields for columns in calendar module advanced filter*}
									{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$EVENT_RECORD_STRUCTURE}
										<optgroup label='{vtranslate($BLOCK_LABEL, 'Events')}'>
											{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
												{if $FIELD_MODEL->isMandatory()}
													{array_push($MANDATORY_FIELDS, $FIELD_MODEL->getCustomViewColumnName())}
												{/if}
												<option value="{$FIELD_MODEL->getCustomViewColumnName()}" data-field-name="{$FIELD_NAME}"
														{if in_array($FIELD_MODEL->getCustomViewColumnName(), $SELECTED_FIELDS)}
															selected
														{/if}
														>{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
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
							<label class="pull-left control-label paddingLeftMd"><span class="redColor">*</span> {vtranslate('LBL_COLOR_VIEW',$MODULE)}:</label>
							<div class="col-md-7">
								<div class="input-group">
									<input type="text" class="form-control colorPicker" name="color" value="{$CUSTOMVIEW_MODEL->get('color')}">
									<span class="input-group-addon" style="background-color: {$CUSTOMVIEW_MODEL->get('color')};">&nbsp;&nbsp;</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-default row marginLeftZero marginRightZero blockContainer">
				<div class="row blockHeader panel-heading marginLeftZero marginRightZero"><div class="iconCollapse">
						<span class="cursorPointer iconToggle glyphicon glyphicon glyphicon-menu-right" data-hide="glyphicon-menu-right" data-show="glyphicon-menu-down"></span>
						<h4 class="">{vtranslate('LBL_DESCRIPTION_INFORMATION',$MODULE)}</h4></div></div>
				<div class="panel-body padding5 hide">
					<textarea name="description" id="description" class="ckEditorSource">{$CUSTOMVIEW_MODEL->get('description')}</textarea>
				</div>	
			</div>
			<div class="panel panel-default row marginLeftZero marginRightZero blockContainer">
				<div class="row blockHeader panel-heading marginLeftZero marginRightZero"><div class="iconCollapse">
						<span class="cursorPointer iconToggle glyphicon glyphicon glyphicon-menu-down" data-hide="glyphicon-menu-right" data-show="glyphicon-menu-down"></span>
						<h4 class="">{vtranslate('LBL_CHOOSE_FILTER_CONDITIONS', $MODULE)}:</h4></div></div>
				<div class="panel-body">
					<div class="filterConditionsDiv">
						<div class="row">
							<span class="col-md-12">
								{include file='AdvanceFilter.tpl'|@vtemplate_path}
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="filterActions">
			<button class="cancelLink pull-right btn btn-warning" type="reset" onClick="window.location.reload()">{vtranslate('LBL_CANCEL', $MODULE)}</button>
			<button class="btn btn-success pull-right" id="customViewSubmit" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
		</div>
	</form>
{/strip}
