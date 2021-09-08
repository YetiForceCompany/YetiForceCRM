{*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
	<form name="EditWorkflow" action="index.php" method="post" id="workflow_step2"
		  class="tpl-Settings-Workflows-Step2 form-horizontal">
		<input type="hidden" name="module" value="Workflows"/>
		<input type="hidden" name="action" value="Save"/>
		<input type="hidden" name="parent" value="Settings"/>
		<input type="hidden" class="step" value="2"/>
		<input type="hidden" name="summary" value="{$WORKFLOW_MODEL->get('summary')}"/>
		<input type="hidden" name="record" value="{$WORKFLOW_MODEL->get('record')}"/>
		<input type="hidden" name="module_name" value="{$WORKFLOW_MODEL->get('module_name')}"/>
		<input type="hidden" name="execution_condition" value="{$WORKFLOW_MODEL->get('execution_condition')}"/>
		<input type="hidden" name="conditions" id="advanced_filter" value=''/>
		<input type="hidden" id="olderConditions"
			   value="{\App\Purifier::encodeHtml(\App\Json::encode($WORKFLOW_MODEL->get('conditions')))}"/>
		<input type="hidden" name="filtersavedinnew" value="{$WORKFLOW_MODEL->get('filtersavedinnew')}"/>
		<input type="hidden" name="schtypeid" value="{$WORKFLOW_MODEL->get('schtypeid')}"/>
		<input type="hidden" name="schtime" value="{$WORKFLOW_MODEL->get('schtime')}"/>
		<input type="hidden" name="schdate" value="{$WORKFLOW_MODEL->get('schdate')}"/>
		<input type="hidden" name="params" value="{\App\Purifier::encodeHtml($WORKFLOW_MODEL->get('params'))}"/>
		<input type="hidden" name="schdayofweek"
			   value="{\App\Purifier::encodeHtml(\App\Json::encode($WORKFLOW_MODEL->get('schdayofweek')))}"/>
		<input type="hidden" name="schdayofmonth"
			   value="{\App\Purifier::encodeHtml(\App\Json::encode($WORKFLOW_MODEL->get('schdayofmonth')))}"/>
		<input type="hidden" name="schannualdates"
			   value="{\App\Purifier::encodeHtml($WORKFLOW_MODEL->get('schannualdates'))}"/>
		{if $WORKFLOW_MODEL->get('execution_condition') eq \VTWorkflowManager::$ON_SCHEDULE && $WORKFLOW_MODEL->getParams('iterationOff')}
			<div class="alert alert-info">
				{\App\Language::translate('LBL_WORKFLOW_RESTRICTION_OFF_ALERT',$QUALIFIED_MODULE)}
			</div>
		{else}
			<div class="" style="border:1px solid #ccc;">
				{if $IS_FILTER_SAVED_NEW == false}
					<div class="alert alert-info">
						{\App\Language::translate('LBL_CREATED_IN_OLD_LOOK_CANNOT_BE_EDITED',$QUALIFIED_MODULE)}
					</div>
					<div class="">
						<span class="col-md-6"><input type="radio" name="conditionstype" class="alignMiddle" checked=""/>&nbsp;&nbsp;<span
									class="alignMiddle">{\App\Language::translate('LBL_USE_EXISTING_CONDITIONS',$QUALIFIED_MODULE)}</span></span>
						<span class="col-md-6"><input type="radio" id="enableAdvanceFilters" name="conditionstype"
													class="alignMiddle recreate"/>&nbsp;&nbsp;<span
									class="alignMiddle">{\App\Language::translate('LBL_RECREATE_CONDITIONS',$QUALIFIED_MODULE)}</span></span>
					</div>
					<br/>
				{/if}
				<div id="advanceFilterContainer" {if $IS_FILTER_SAVED_NEW == false} class="zeroOpacity js-conditions-container padding1per" {else} class="row js-conditions-container padding1per" {/if}
					data-js="container">
					<h5 class="padding-bottom1per col-md-10">
						<strong>{\App\Language::translate('LBL_CHOOSE_FILTER_CONDITIONS',$MODULE)}</strong></h5>
					<div class="col-md-10">
						{include file=\App\Layout::getTemplatePath('AdvanceFilter.tpl') RECORD_STRUCTURE=$RECORD_STRUCTURE}
					</div>
					{include file=\App\Layout::getTemplatePath('FieldExpressions.tpl', $QUALIFIED_MODULE) EXECUTION_CONDITION=$WORKFLOW_MODEL->get('execution_condition')}
				</div>
			</div>
		{/if}
		<br/>
		<div class="float-right">
			<button class="btn btn-secondary backStep mr-1" type="button">
				<strong>
					<span class="fas fa-caret-left mr-1"></span>
					{\App\Language::translate('LBL_BACK', $QUALIFIED_MODULE)}
				</strong>
			</button>
			<button class="btn btn-success mr-1" type="submit">
				<strong>
					<span class="fas fa-caret-right mr-1"></span>
					{\App\Language::translate('LBL_NEXT', $QUALIFIED_MODULE)}
				</strong>
			</button>
			<button class="btn btn-danger cancelLink" type="reset" onclick="javascript:window.history.back();">
				<strong>
					<span class="fas fa-times mr-1"></span>
					{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
				</strong>
			</button>
		</div>
		<br/><br/>
	</form>
{/strip}
