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
	{assign var=ALL_CONDITION_CRITERIA value=$ADVANCE_CRITERIA[1] }
	{assign var=ANY_CONDITION_CRITERIA value=$ADVANCE_CRITERIA[2] }
	{if empty($ALL_CONDITION_CRITERIA) }
		{assign var=ALL_CONDITION_CRITERIA value=[]}
	{/if}
	{if empty($ANY_CONDITION_CRITERIA) }
		{assign var=ANY_CONDITION_CRITERIA value=[]}
	{/if}
	<div class="filterContainer">
		<input type="hidden" name="date_filters" data-value='{\App\Purifier::encodeHtml(\App\Json::encode($DATE_FILTERS))}' />
		<input type="hidden" name="advanceFilterOpsByFieldType" data-value='{\App\Json::encode($ADVANCED_FILTER_OPTIONS_BY_TYPE)}' />
		{foreach key=ADVANCE_FILTER_OPTION_KEY item=ADVANCE_FILTER_OPTION from=$ADVANCED_FILTER_OPTIONS}
			{$ADVANCED_FILTER_OPTIONS[$ADVANCE_FILTER_OPTION_KEY] = {\App\Language::translate($ADVANCE_FILTER_OPTION, $MODULE)}|escape}
		{/foreach}
		<input type="hidden" name="advanceFilterOptions" data-value='{\App\Json::encode($ADVANCED_FILTER_OPTIONS)}' />
		<div class="allConditionContainer mb-3 conditionGroup contentsBackground well">
			<div class="header">
				<span><strong>{\App\Language::translate('LBL_ALL_CONDITIONS',$MODULE)}</strong></span>
				&nbsp;
				<span>({\App\Language::translate('LBL_ALL_CONDITIONS_DESC',$MODULE)})</span>
			</div>
			<div class="contents">
				<div class="conditionList">
					{foreach item=CONDITION_INFO from=$ALL_CONDITION_CRITERIA['columns']}
						{include file=\App\Layout::getTemplatePath('AdvanceFilterCondition.tpl', $QUALIFIED_MODULE) RECORD_STRUCTURE=$RECORD_STRUCTURE CONDITION_INFO=$CONDITION_INFO MODULE=$MODULE}
					{/foreach}
				</div>
				<div class="d-none basic">
					{include file=\App\Layout::getTemplatePath('AdvanceFilterCondition.tpl', $QUALIFIED_MODULE) RECORD_STRUCTURE=$RECORD_STRUCTURE CONDITION_INFO=[] MODULE=$MODULE NOCHOSEN=true}
				</div>
				<div class="addCondition">
					<button type="button" class="btn btn-light pushDown">
						<strong>
							<span class="fas fa-plus mr-1"></span>
							{\App\Language::translate('LBL_ADD_CONDITION',$MODULE)}
						</strong>
					</button>
				</div>
				<div class="groupCondition">
					{assign var=GROUP_CONDITION value=$ALL_CONDITION_CRITERIA['condition']}
					{if empty($GROUP_CONDITION)}
						{assign var=GROUP_CONDITION value="and"}
					{/if}
					<input type="hidden" name="condition" value="{$GROUP_CONDITION}" />
				</div>
			</div>
		</div>
		<div class="anyConditionContainer conditionGroup contentsBackground mb-3 well">
			<div class="header">
				<span><strong>{\App\Language::translate('LBL_ANY_CONDITIONS',$MODULE)}</strong></span>
				&nbsp;
				<span>({\App\Language::translate('LBL_ANY_CONDITIONS_DESC',$MODULE)})</span>
			</div>
			<div class="contents">
				<div class="conditionList">
					{foreach item=CONDITION_INFO from=$ANY_CONDITION_CRITERIA['columns']}
						{include file=\App\Layout::getTemplatePath('AdvanceFilterCondition.tpl', $QUALIFIED_MODULE) RECORD_STRUCTURE=$RECORD_STRUCTURE CONDITION_INFO=$CONDITION_INFO MODULE=$MODULE CONDITION="or"}
					{/foreach}
				</div>
				<div class="d-none basic">
					{include file=\App\Layout::getTemplatePath('AdvanceFilterCondition.tpl', $QUALIFIED_MODULE) RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE=$MODULE CONDITION_INFO=[] CONDITION="or" NOCHOSEN=true}
				</div>
				<div class="addCondition">
					<button type="button" class="btn btn-light pushDown">
						<strong>
							<span class="fas fa-plus mr-1"></span>
							{\App\Language::translate('LBL_ADD_CONDITION',$MODULE)}
						</strong>
					</button>
				</div>
			</div>
		</div>
	</div>
{/strip}
