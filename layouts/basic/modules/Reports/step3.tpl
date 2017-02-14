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
	<form class="form-horizontal recordEditView" id="report_step3" method="post" action="index.php">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="action" value="Save" />
		<input type="hidden" name="record" value="{$RECORD_ID}" />
		<input type="hidden" name="reportname" value="{$REPORT_MODEL->get('reportname')}" />
		<input type="hidden" name="folderid" value="{$REPORT_MODEL->get('folderid')}" />
		<input type="hidden" name="reports_description" value="{$REPORT_MODEL->get('description')}" />
		<input type="hidden" name="primary_module" value="{$PRIMARY_MODULE}" />
		<input type="hidden" name="secondary_modules" value="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($SECONDARY_MODULES))}" />
		<input type="hidden" name="selected_fields" id="seleted_fields" value='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($REPORT_MODEL->get('selected_fields')))}' />
		<input type="hidden" name="selected_sort_fields" id="selected_sort_fields" value={Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($REPORT_MODEL->get('selected_sort_fields')))} />
		<input type="hidden" name="selected_calculation_fields" id="calculation_fields" value={Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($REPORT_MODEL->get('calculation_fields')))} />
		<input type="hidden" name="advanced_filter" id="advanced_filter" value="" />
		<input type="hidden" name="isDuplicate" value="{$IS_DUPLICATE}" />
		<input type="hidden" class="step" value="3" />
		<input type="hidden" name="enable_schedule" value="{$REPORT_MODEL->get('enable_schedule')}">
		<input type="hidden" name="schtime" value="{$REPORT_MODEL->get('schtime')}">
		<input type="hidden" name="schdate" value="{$REPORT_MODEL->get('schdate')}">
		<input type="hidden" name="schdayoftheweek" value="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($REPORT_MODEL->get('schdayoftheweek')))}">
		<input type="hidden" name="schdayofthemonth" value="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($REPORT_MODEL->get('schdayofthemonth')))}">
		<input type="hidden" name="schannualdates" value="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($REPORT_MODEL->get('schannualdates')))}">
		<input type="hidden" name="recipients" value="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($REPORT_MODEL->get('recipients')))}">
        <input type="hidden" name="specificemails" value="{$REPORT_MODEL->get('specificemails')}">
		<input type="hidden" name="schtypeid" value="{$REPORT_MODEL->get('schtypeid')}">
		<input type="hidden" name="scheduleFileType" value="{$REPORT_MODEL->get('scheduleFileType')}">
        <input type="hidden" name="date_filters" data-value='{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($DATE_FILTERS))}' />
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
		<div class="row">
			<div class="col-md-12">
				<h4><strong>{vtranslate('LBL_CHOOSE_FILTER_CONDITIONS',$MODULE)}</strong></h4>
				<br>
				<div class="col-md-10">
					{include file='AdvanceFilter.tpl'|@vtemplate_path RECORD_STRUCTURE=$RECORD_STRUCTURE ADVANCE_CRITERIA=$SELECTED_ADVANCED_FILTER_FIELDS COLUMNNAME_API=getReportFilterColumnName}
				</div>
				<div class="col-md-10">
					<div class="pull-right">
						<button type="button" class="btn btn-danger backStep"><strong>{vtranslate('LBL_BACK',$MODULE)}</strong></button>&nbsp;&nbsp;
						<button type="submit" class="btn btn-success" id="generateReport"><strong>{vtranslate('LBL_GENERATE_REPORT',$MODULE)}</strong></button>&nbsp;&nbsp;
						<button class="cancelLink btn btn-warning" onclick="window.history.back()">{vtranslate('LBL_CANCEL',$MODULE)}</a>&nbsp;&nbsp;
					</div>
				</div>
			</div>
		</div>
		<br>
	</form>
{/strip}
