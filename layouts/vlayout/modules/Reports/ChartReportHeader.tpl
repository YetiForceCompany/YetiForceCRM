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
	<div id="toggleButton" class="toggleButton" title="{vtranslate('LBL_LEFT_PANEL_SHOW_HIDE', 'Vtiger')}">
		<i id="tButtonImage" class="{if $LEFTPANELHIDE neq '1'}icon-chevron-left{else}icon-chevron-right{/if}"></i>
	</div>
    <div class="container-fluid">
        <div class="row-fluid reportsDetailHeader">
            <input type="hidden" name="date_filters" data-value='{ZEND_JSON::encode($DATE_FILTERS)}' />
            <div class="reportHeader row-fluid">
                <div class='span8 btn-toolbar'>
                    <h3>{$REPORT_MODEL->getName()}</h3>
                </div>

                <div class='span4'>
                    <div class="btn-toolbar pull-right">
                        {if $REPORT_MODEL->isEditable() eq true}
                            <div class="btn-group">
                                <button onclick='window.location.href="{$REPORT_MODEL->getEditViewUrl()}"' type="button" class="cursorPointer btn">
                                    <strong>{vtranslate('LBL_CUSTOMIZE',$MODULE)}</strong>&nbsp;
                                    <i class="icon-pencil"></i>
                                </button>
                            </div>
                        {/if}
                        <div class="btn-group">
                            <button onclick='window.location.href="{$REPORT_MODEL->getDuplicateRecordUrl()}"' type="button" class="cursorPointer btn">
                                <strong>{vtranslate('LBL_DUPLICATE',$MODULE)}</strong>
                            </button>
                        </div>
                    </div>
                </div>
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
					<input type="hidden" name="secondary_modules" value={ZEND_JSON::encode($SECONDARY_MODULES)} />
					<input type="hidden" name="advanced_filter" id="advanced_filter" value={ZEND_JSON::encode($ADVANCED_FILTERS)} />
					<input type="hidden" name='groupbyfield' value={$CHART_MODEL->getGroupByField()} />
					<input type="hidden" name='datafields' value={Zend_JSON::encode($CHART_MODEL->getDataFields())} />
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
						<div>
							<div class='row-fluid'>
								<span class="span4">
									<div><span>{vtranslate('LBL_SELECT_GROUP_BY_FIELD', $MODULE)}</span><span class="redColor">*</span></div><br>
									<div class="row-fluid">
										<select id='groupbyfield' name='groupbyfield' class="span10" data-validation-engine="validate[required]" style='min-width:300px;'></select>
									</div>
								</span>
								<span class="span2">&nbsp;</span>
								<span class="span4">
									<div><span>{vtranslate('LBL_SELECT_DATA_FIELD', $MODULE)}</span><span class="redColor">*</span></div><br>
									<div class="row-fluid">
										<select id='datafields' name='datafields[]' class="span10" data-validation-engine="validate[required]" style='min-width:300px;'>
									</select></div>
								</span>
							</div>
							<br>

							<div class='hide'>
								{include file="chartReportHiddenContents.tpl"|vtemplate_path:$MODULE}
							</div>
						</div>
						<br>
						<div class='row-fluid'>
							{assign var=filterConditionNotExists value=(count($SELECTED_ADVANCED_FILTER_FIELDS[1]['columns']) eq 0 and count($SELECTED_ADVANCED_FILTER_FIELDS[2]['columns']) eq 0)}
							<button class="btn" name="modify_condition" data-val="{$filterConditionNotExists}">
								<strong>{vtranslate('LBL_MODIFY_CONDITION', $MODULE)}</strong>&nbsp;&nbsp;
								<i class="{if $filterConditionNotExists eq true} icon-chevron-right {else} icon-chevron-down {/if}"></i>
							</button>
						</div>
						<br>
						<div id='filterContainer' class='{if $filterConditionNotExists eq true} hide {/if}'>
							{include file='AdvanceFilter.tpl'|@vtemplate_path RECORD_STRUCTURE=$RECORD_STRUCTURE ADVANCE_CRITERIA=$SELECTED_ADVANCED_FILTER_FIELDS COLUMNNAME_API=getReportFilterColumnName}
						</div>
					</div>
					<div class="row-fluid textAlignCenter">
						<button class="btn btn-success generateReport" data-mode="save" value="{vtranslate('LBL_SAVE',$MODULE)}"/>
							<strong>{vtranslate('LBL_SAVE',$MODULE)}</strong>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div id="reportContentsDiv">
{/strip}