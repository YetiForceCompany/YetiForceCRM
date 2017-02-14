{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    <div class="">
		<input type="hidden" name="date_filters" data-value='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($DATE_FILTERS))}' />
		<div class="widget_header row marginBottom10px">
			<div class="col-sm-8">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			</div>
			<div class="col-sm-4">
				<div class="btn-toolbar pull-right">
					{if $REPORT_MODEL->isEditable() eq true}
						<div class="btn-group">
							<button onclick='window.location.href = "{$REPORT_MODEL->getEditViewUrl()}"' type="button" class="cursorPointer btn btn-primary">
								<strong>{vtranslate('LBL_CUSTOMIZE',$MODULE)}</strong>&nbsp;
								<span class="glyphicon glyphicon-pencil"></span>
							</button>
						</div>
					{/if}
					<div class="btn-group">
						<button onclick='window.location.href = "{$REPORT_MODEL->getDuplicateRecordUrl()}"' type="button" class="cursorPointer btn btn-success">
							<strong>{vtranslate('LBL_DUPLICATE',$MODULE)}</strong>
						</button>
					</div>
				</div>
			</div>
		</div>
        <div class="reportsDetailHeader">
			<div class="reportHeader">
				<h4>{$REPORT_MODEL->getName()}</h4>
            </div>
			<div class="well">
				<form name='chartDetailForm' id='chartDetailForm'>
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="action" value="ChartSave" />
					<input type="hidden" name="recordId" id="recordId" value="{$RECORD}" />
					<input type="hidden" name="reportname" value="{$REPORT_MODEL->get('reportname')}" />
					<input type="hidden" name="folderid" value="{$REPORT_MODEL->get('folderid')}" />
					<input type="hidden" name="reports_description" value="{$REPORT_MODEL->get('reports_description')}" />
					<input type="hidden" name="primary_module" value="{$PRIMARY_MODULE}" />
					<input type="hidden" name="secondary_modules" value="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($SECONDARY_MODULES))}" />
					<input type="hidden" name="advanced_filter" id="advanced_filter" value="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($ADVANCED_FILTERS))}" />
					<input type="hidden" name='groupbyfield' value={$CHART_MODEL->getGroupByField()} />
					<input type="hidden" name='datafields' value={Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($CHART_MODEL->getDataFields()))} />
					<input type="hidden" name='charttype' value="{$CHART_MODEL->getChartType()}" />

					{assign var=RECORD_STRUCTURE value=array()}
					{assign var=PRIMARY_MODULE_LABEL value=vtranslate($PRIMARY_MODULE, $PRIMARY_MODULE)}
					{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$PRIMARY_MODULE_RECORD_STRUCTURE}
						{assign var=PRIMARY_MODULE_BLOCK_LABEL value=vtranslate($BLOCK_LABEL, $PRIMARY_MODULE)}
						{assign var=key value="$PRIMARY_MODULE_LABEL $PRIMARY_MODULE_BLOCK_LABEL"}
						{if $LINEITEM_FIELD_IN_CALCULATION eq false && $BLOCK_LABEL eq 'LBL_ITEM_DETAILS'}
							{* dont show the line item fields block when Inventory fields are selected for calculations *}
						{else}
							{$RECORD_STRUCTURE[$key] = $BLOCK_FIELDS}
						{/if}
					{/foreach}
					{foreach key=MODULE_LABEL item=SECONDARY_MODULE_RECORD_STRUCTURE from=$SECONDARY_MODULE_RECORD_STRUCTURES}
						{assign var=SECONDARY_MODULE_LABEL value=vtranslate($MODULE_LABEL, $MODULE_LABEL)}
						{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$SECONDARY_MODULE_RECORD_STRUCTURE}
							{assign var=SECONDARY_MODULE_BLOCK_LABEL value=vtranslate($BLOCK_LABEL, $MODULE_LABEL)}
							{assign var=key value="$SECONDARY_MODULE_LABEL $SECONDARY_MODULE_BLOCK_LABEL"}
							{$RECORD_STRUCTURE[$key] = $BLOCK_FIELDS}
						{/foreach}
					{/foreach}
					<div>
						<div class="row">
							<div class='form-inline'>
								<div class="form-group col-xs-6">
									<label>{vtranslate('LBL_SELECT_GROUP_BY_FIELD', $MODULE)}<span class="redColor">*</span></label>
									<div class="col-md-12 paddingLRZero">
										<select id='groupbyfield' name='groupbyfieldSelect' class="form-control" data-validation-engine="validate[required]"></select>
									</div>
								</div>
								<div class="form-group col-xs-6">
									<label>{vtranslate('LBL_SELECT_DATA_FIELD', $MODULE)}<span class="redColor">*</span></label>
									<div class="col-md-12 paddingLRZero">
										<select id='datafields' name='datafields[]' class="form-control" data-validation-engine="validate[required]">
										</select></div>
								</div>
							</div>
							<br>

							<div class='hide'>
								{include file="chartReportHiddenContents.tpl"|vtemplate_path:$MODULE}
							</div>
						</div>
						<div class="clearfix">
							<div class='h3'>
								{assign var=filterConditionNotExists value=(count($SELECTED_ADVANCED_FILTER_FIELDS[1]['columns']) eq 0 and count($SELECTED_ADVANCED_FILTER_FIELDS[2]['columns']) eq 0)}
								<button type="button" class="btn btn-default" name="modify_condition" data-val="{$filterConditionNotExists}">
									<strong>{vtranslate('LBL_MODIFY_CONDITION', $MODULE)}</strong>&nbsp;&nbsp;
									<span class="{if $filterConditionNotExists eq true} glyphicon glyphicon-chevron-right {else} glyphicon glyphicon-chevron-down {/if}"></span>
								</button>
							</div>
							<div id='filterContainer' class='form-group '{if $filterConditionNotExists eq true} style="display: none"{/if}>
								{include file='AdvanceFilter.tpl'|@vtemplate_path RECORD_STRUCTURE=$RECORD_STRUCTURE ADVANCE_CRITERIA=$SELECTED_ADVANCED_FILTER_FIELDS COLUMNNAME_API=getReportFilterColumnName}
							</div>
						</div>
					</div>
					<div class="row textAlignCenter">
						<button type="button" class="btn btn-success generateReport" data-mode="save" value="{vtranslate('LBL_SAVE',$MODULE)}"/>
						<strong>{vtranslate('LBL_SAVE',$MODULE)}</strong>
						</button>
					</div>
			</div>
			</form>
		</div>
	</div>
	<div id="reportContentsDiv" class="row">
	{/strip}
