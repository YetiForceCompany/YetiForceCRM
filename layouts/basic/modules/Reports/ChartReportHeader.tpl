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
		<input type="hidden" name="date_filters" data-value='{\App\Purifier::encodeHtml(\App\Json::encode($DATE_FILTERS))}' />
		<div class="widget_header row marginBottom10px">
			<div class="col-sm-8">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
			</div>
			<div class="col-sm-4">
				<div class="btn-toolbar float-right">
					{if $REPORT_MODEL->isEditable() eq true}
						<div class="btn-group">
							<button onclick='window.location.href = "{$REPORT_MODEL->getEditViewUrl()}"' type="button" class="cursorPointer btn btn-primary">
								<strong>{\App\Language::translate('LBL_CUSTOMIZE',$MODULE)}</strong>&nbsp;
								<span class="fas fa-edit"></span>
							</button>
						</div>
					{/if}
					<div class="btn-group">
						<button onclick='window.location.href = "{$REPORT_MODEL->getDuplicateRecordUrl()}"' type="button" class="cursorPointer btn btn-success">
							<strong>{\App\Language::translate('LBL_DUPLICATE',$MODULE)}</strong>
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
					<input type="hidden" name="secondary_modules" value="{\App\Purifier::encodeHtml(\App\Json::encode($SECONDARY_MODULES))}" />
					<input type="hidden" name="advanced_filter" id="advanced_filter" value="{\App\Purifier::encodeHtml(\App\Json::encode($ADVANCED_FILTERS))}" />
					<input type="hidden" name='groupbyfield' value={$CHART_MODEL->getGroupByField()} />
					<input type="hidden" name='datafields' value={\App\Purifier::encodeHtml(\App\Json::encode($CHART_MODEL->getDataFields()))} />
					<input type="hidden" name='charttype' value="{$CHART_MODEL->getChartType()}" />

					{assign var=RECORD_STRUCTURE value=[]}
					{assign var=PRIMARY_MODULE_LABEL value=\App\Language::translate($PRIMARY_MODULE, $PRIMARY_MODULE)}
					{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$PRIMARY_MODULE_RECORD_STRUCTURE}
						{assign var=PRIMARY_MODULE_BLOCK_LABEL value=\App\Language::translate($BLOCK_LABEL, $PRIMARY_MODULE)}
						{assign var=key value="$PRIMARY_MODULE_LABEL $PRIMARY_MODULE_BLOCK_LABEL"}
						{if $LINEITEM_FIELD_IN_CALCULATION eq false && $BLOCK_LABEL eq 'LBL_ITEM_DETAILS'}
							{* dont show the line item fields block when Inventory fields are selected for calculations *}
						{else}
							{$RECORD_STRUCTURE[$key] = $BLOCK_FIELDS}
						{/if}
					{/foreach}
					{foreach key=MODULE_LABEL item=SECONDARY_MODULE_RECORD_STRUCTURE from=$SECONDARY_MODULE_RECORD_STRUCTURES}
						{assign var=SECONDARY_MODULE_LABEL value=\App\Language::translate($MODULE_LABEL, $MODULE_LABEL)}
						{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$SECONDARY_MODULE_RECORD_STRUCTURE}
							{assign var=SECONDARY_MODULE_BLOCK_LABEL value=\App\Language::translate($BLOCK_LABEL, $MODULE_LABEL)}
							{assign var=key value="$SECONDARY_MODULE_LABEL $SECONDARY_MODULE_BLOCK_LABEL"}
							{$RECORD_STRUCTURE[$key] = $BLOCK_FIELDS}
						{/foreach}
					{/foreach}
					<div>
						<div class="row">
							<div class='form-inline'>
								<div class="form-group col-6">
									<label>{\App\Language::translate('LBL_SELECT_GROUP_BY_FIELD', $MODULE)}<span class="redColor">*</span></label>
									<div class="col-md-12 paddingLRZero">
										<select id='groupbyfield' name='groupbyfieldSelect' class="form-control" data-validation-engine="validate[required]"></select>
									</div>
								</div>
								<div class="form-group col-6">
									<label>{\App\Language::translate('LBL_SELECT_DATA_FIELD', $MODULE)}<span class="redColor">*</span></label>
									<div class="col-md-12 paddingLRZero">
										<select id='datafields' name='datafields[]' class="form-control" data-validation-engine="validate[required]">
										</select></div>
								</div>
							</div>
							<br />

							<div class='d-none'>
								{include file=\App\Layout::getTemplatePath('chartReportHiddenContents.tpl', $MODULE)}
							</div>
						</div>
						<div class="clearfix">
							<div class='h3'>
								{assign var=filterConditionNotExists value=(count($SELECTED_ADVANCED_FILTER_FIELDS[1]['columns']) eq 0 and count($SELECTED_ADVANCED_FILTER_FIELDS[2]['columns']) eq 0)}
								<button type="button" class="btn btn-light" name="modify_condition" data-val="{$filterConditionNotExists}">
									<strong>{\App\Language::translate('LBL_MODIFY_CONDITION', $MODULE)}</strong>&nbsp;&nbsp;
									<span class="{if $filterConditionNotExists eq true} fas fa-chevron-right {else} fas fa-chevron-down {/if}"></span>
								</button>
							</div>
							<div id='filterContainer' class='form-group '{if $filterConditionNotExists eq true} style="display: none"{/if}>
								{include file=\App\Layout::getTemplatePath('AdvanceFilter.tpl') RECORD_STRUCTURE=$RECORD_STRUCTURE ADVANCE_CRITERIA=$SELECTED_ADVANCED_FILTER_FIELDS COLUMNNAME_API=getReportFilterColumnName}
							</div>
						</div>
					</div>
					<div class="row textAlignCenter">
						<button type="button" class="btn btn-success generateReport" data-mode="save" value="{\App\Language::translate('LBL_SAVE',$MODULE)}" />
						<strong>{\App\Language::translate('LBL_SAVE',$MODULE)}</strong>
						</button>
					</div>
			</div>
			</form>
		</div>
	</div>
	<div id="reportContentsDiv" class="row">
	{/strip}
