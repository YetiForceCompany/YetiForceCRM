{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Settings-Worklfows-ListViewHeader -->
	<div class="listViewPageDiv">
		<div class="listViewTopMenuDiv">
			<div class="o-breadcrumb widget_header row">
				<div class="col-md-6">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
				</div>
				<div class="col-md-6">
					<b class="float-right pt-2">
						{if $CRON_RECORD_MODEL->isDisabled() }{\App\Language::translate('LBL_DISABLED',$QUALIFIED_MODULE)}{/if}
						{if $CRON_RECORD_MODEL->isRunning() }{\App\Language::translate('LBL_RUNNING',$QUALIFIED_MODULE)}{/if}
						{if $CRON_RECORD_MODEL->isEnabled()}
							{if !empty($CRON_RECORD_MODEL->hadTimedout)}
								{\App\Language::translate('LBL_LAST_SCAN_TIMED_OUT',$QUALIFIED_MODULE)}.
							{elseif $CRON_RECORD_MODEL->getLastEndDateTime() neq ''}
								{\App\Language::translate('LBL_LAST_SCAN_AT',$QUALIFIED_MODULE)}
								{$CRON_RECORD_MODEL->getLastEndDateTime()}
								&nbsp;&
								{\App\Language::translate('LBL_TIME_TAKEN',$QUALIFIED_MODULE)}:&nbsp;
								{$CRON_RECORD_MODEL->getTimeDiff()}&nbsp;
								{\App\Language::translate('LBL_SHORT_SECONDS',$QUALIFIED_MODULE)}
							{else}

							{/if}
						{/if}
					</b>
				</div>
			</div>
			<div class="listViewActionsDi row my-2">
				<div class="col-lg-4 btn-toolbar d-flex justify-content-between justify-content-lg-start">
					<button class="btn btn-success addButton" {if stripos($MODULE_MODEL->getCreateViewUrl(), 'javascript:')===0} onclick="{$MODULE_MODEL->getCreateViewUrl()|substr:strlen('javascript:')};"
						{else} onclick='window.location.href = "{$MODULE_MODEL->getCreateViewUrl()}"'
						{/if}>
						<i class="fas fa-plus"></i>&nbsp;
						<strong>{\App\Language::translate('LBL_NEW', $QUALIFIED_MODULE)} {\App\Language::translate('LBL_WORKFLOW',$QUALIFIED_MODULE)}</strong>
					</button>
					<button class="btn btn-outline-secondary ml-1 importButton" id="importButton"
						data-url="{Settings_Workflows_Module_Model::getImportViewUrl()}"
						title="{\App\Language::translate('LBL_IMPORT_TEMPLATE', $QUALIFIED_MODULE)}">
						<i class="fas fa-download"></i>
					</button>
					<button class="btn btn-info ml-1 js-workflow-sort-button {if empty($SOURCE_MODULE)}d-none{/if}"
						title="{\App\Language::translate('LBL_SORTING_SETTINGS', $QUALIFIED_MODULE)}">
						<i class="fas fa-sort"></i>
					</button>
				</div>
				<div class="col-lg-3 btn-toolbar ml-0 mt-1 mt-lg-0">
					<select class="select2 form-control js-workflow-module-filter" id="moduleFilter">
						<option value="">{\App\Language::translate('LBL_ALL', $QUALIFIED_MODULE)}</option>
						{foreach item=MODULE_MODEL key=TAB_ID from=$SUPPORTED_MODULE_MODELS}
							<option {if !empty($SOURCE_MODULE) && $SOURCE_MODULE eq $MODULE_MODEL->getName()} selected="" {/if}
								value="{$MODULE_MODEL->getName()}">
								{\App\Language::translate($MODULE_MODEL->getName(),$MODULE_MODEL->getName())}
							</option>
						{/foreach}
					</select>
				</div>
				<div class="col-lg-5 mt-1 mt-lg-0 d-flex justify-content-center justify-content-lg-end">
					{include file=\App\Layout::getTemplatePath('ListViewActions.tpl')}
				</div>
			</div>
		</div>
		<div class="listViewContentDiv js-workflows-container" id="listViewContents">
			<!-- /tpl-Settings-Worklfows-ListViewHeader -->
{/strip}
