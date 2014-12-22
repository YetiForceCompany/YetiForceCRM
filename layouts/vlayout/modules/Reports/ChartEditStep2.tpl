{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.1
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
	<form class="form-horizontal recordEditView" id="chart_report_step2" method="post" action="index.php">
		<input type="hidden" name="module" value="{$MODULE}" >
		<input type="hidden" name="view" value="ChartEdit" >
		<input type="hidden" name="record" value="{$RECORD_ID}" >
		<input type="hidden" name="reportname" value="{$REPORT_MODEL->get('reportname')}" >
		<input type="hidden" name="folderid" value="{$REPORT_MODEL->get('folderid')}" >
		<input type="hidden" name="reports_description" value="{$REPORT_MODEL->get('description')}" >
		<input type="hidden" name="primary_module" value="{$PRIMARY_MODULE}" >
		<input type="hidden" name="secondary_modules" value={ZEND_JSON::encode($SECONDARY_MODULES)} >
		<input type="hidden" name="isDuplicate" value="{$IS_DUPLICATE}" >
		<input type="hidden" class="step" value="2" >
		<input type="hidden" name="mode" value="step3" >
		<input type="hidden" name="advanced_filter" id="advanced_filter" value="" >

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
		<div class="padding1per border1px">
			<div class="row-fluid">
				<h4><strong>{vtranslate('LBL_CHOOSE_FILTER_CONDITIONS',$MODULE)}</strong></h4><br>
				<span class="span10 well contentsBackground">
					{include file='AdvanceFilter.tpl'|@vtemplate_path RECORD_STRUCTURE=$RECORD_STRUCTURE ADVANCE_CRITERIA=$SELECTED_ADVANCED_FILTER_FIELDS COLUMNNAME_API=getReportFilterColumnName}
				</span>
			</div>
		</div>
		<br>
		<div class="pull-right block">
			<button type="button" class="btn btn-danger backStep"><strong>{vtranslate('LBL_BACK',$MODULE)}</strong></button>&nbsp;&nbsp;
			<button type="submit" class="btn btn-success nextStep"><strong>{vtranslate('LBL_NEXT',$MODULE)}</strong></button>&nbsp;&nbsp;
			<a  class="cancelLink" onclick="window.history.back()">{vtranslate('LBL_CANCEL',$MODULE)}</a>&nbsp;&nbsp;
		</div>
		<br><br>
	</form>
{/strip}