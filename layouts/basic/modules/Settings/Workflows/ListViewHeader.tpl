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
	<div class="listViewPageDiv">
		<div class="listViewTopMenuDiv">
			<div class="widget_header row">
				<div class="col-md-6">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
				</div>
				<div class="col-md-6">
					<b class="float-right pt-2">
					{if $CRON_RECORD_MODEL->isDisabled() }{\App\Language::translate('LBL_DISABLED',$QUALIFIED_MODULE)}{/if}
				{if $CRON_RECORD_MODEL->isRunning() }{\App\Language::translate('LBL_RUNNING',$QUALIFIED_MODULE)}{/if}
				{if $CRON_RECORD_MODEL->isEnabled()}
					{if $CRON_RECORD_MODEL->hadTimedout}
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
	<div class="listViewActionsDi form-row my-2">
		<div class="col-md-4 btn-toolbar">
			<button class="btn btn-success addButton" {if stripos($MODULE_MODEL->getCreateViewUrl(), 'javascript:')===0} onclick="{$MODULE_MODEL->getCreateViewUrl()|substr:strlen('javascript:')};"
					{else} onclick='window.location.href = "{$MODULE_MODEL->getCreateViewUrl()}"' {/if}>
							<i class="fas fa-plus"></i>&nbsp;
							<strong>{\App\Language::translate('LBL_NEW', $QUALIFIED_MODULE)} {\App\Language::translate('LBL_WORKFLOW',$QUALIFIED_MODULE)}</strong>
						</button>
						<button class="btn btn-outline-secondary ml-1 importButton" id="importButton" data-url="{Settings_Workflows_Module_Model::getImportViewUrl()}" title="{\App\Language::translate('LBL_IMPORT_TEMPLATE', $QUALIFIED_MODULE)}">
							<i class="fas fa-download"></i>
						</button>
					</div>
					<div class="col-md-3 btn-toolbar ml-0">
						<select class="select2 form-control" id="moduleFilter" >
							<option value="">{\App\Language::translate('LBL_ALL', $QUALIFIED_MODULE)}</option>
							{foreach item=MODULE_MODEL key=TAB_ID from=$SUPPORTED_MODULE_MODELS}
								<option {if $SOURCE_MODULE eq $MODULE_MODEL->getName()} selected="" {/if} value="{$MODULE_MODEL->getName()}">
									{if $MODULE_MODEL->getName() eq 'Calendar'}
										{\App\Language::translate('LBL_TASK', $MODULE_MODEL->getName())}
									{else}
										{\App\Language::translate($MODULE_MODEL->getName(),$MODULE_MODEL->getName())}
									{/if}
								</option>
							{/foreach}
						</select>
					</div>
					<div class="col-md-5">
						<div class="float-right">
							{include file=\App\Layout::getTemplatePath('ListViewActions.tpl')}
						</div>
					</div>
			</div>
		</div>
		<div class="listViewContentDiv" id="listViewContents">
			{/strip}
