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
	<div>
		<form onsubmit="" action="index.php" enctype="multipart/form-data" method="POST" name="importBasic">
			<input type="hidden" name="module" value="{$FOR_MODULE}" />
			<input type="hidden" name="view" value="Import" />
			<input type="hidden" name="mode" value="uploadAndParse" />
			<input type="hidden" name="src_record" value="{$MODULE_MODEL->get('src_record')}" />
			<input type="hidden" name="relationId" value="{$MODULE_MODEL->get('relationId')}" />
			<div class='widget_header row '>
				<div class="col-12">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
				</div>
			</div>
			<div class="col-12 searchUIBasic px-0 m-0">
				{if $ERROR_MESSAGE neq ''}
					<div class="col-12">
						<div class="style1">
							<span class="alert-warning">{$ERROR_MESSAGE}</span>
						</div>
					</div>
				{/if}
				<div class="importContents col-12 mt-2">
					{include file=\App\Layout::getTemplatePath('Import_Step1.tpl', 'Import')}
				</div>
				<div class="importContents col-12">
					{include file=\App\Layout::getTemplatePath('Import_Step2.tpl', 'Import')}
				</div>
				{if empty($DUPLICATE_HANDLING_NOT_SUPPORTED)}
					<div class="importContents col-12">
						{include file=\App\Layout::getTemplatePath('Import_Step3.tpl', 'Import')}
					</div>
				{/if}
				<div class="col-12 pb-3">
					{include file=\App\Layout::getTemplatePath('Import_Basic_Buttons.tpl', 'Import')}
				</div>
			</div>
		</form>
	</div>
{/strip}
