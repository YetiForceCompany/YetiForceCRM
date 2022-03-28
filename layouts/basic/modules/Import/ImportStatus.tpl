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
	<!-- tpl-Import-ImportStatus -->
	<div class='widget_header row '>
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
		</div>
	</div>
	{literal}
		<script type="text/javascript">
			jQuery(document).ready(function() {
				setTimeout(function() {
					jQuery("[name=importStatusForm]").get(0).submit();
				}, 2000);
			});
		</script>
	{/literal}
	<div>
		<form onsubmit="VtigerJS_DialogBox.block();" action="index.php" enctype="multipart/form-data" method="POST"
			name="importStatusForm">
			<input type="hidden" name="module" value="{$FOR_MODULE}" />
			<input type="hidden" name="view" value="Import" />
			{if $CONTINUE_IMPORT eq 'true'}
				<input type="hidden" name="mode" value="continueImport" />
			{else}
				<input type="hidden" name="mode" value="" />
			{/if}
		</form>
		<table class="u-w-90per m-auto searchUIBasic well">
			<tr>
				<td class="font-x-large text-center">
					<h3>
						<strong>
							{\App\Language::translate('LBL_IMPORT', $MODULE_NAME)} {\App\Language::translate($FOR_MODULE, $FOR_MODULE)}
						</strong>
					</h3>
				</td>
			</tr>
			<tr>
				<td class="font-x-large text-center">
					<span class="redColor">{\App\Language::translate('LBL_RUNNING', $MODULE_NAME)} ... </span>
				</td>
			</tr>
			{if !empty($ERROR_MESSAGE)}
				<tr>
					<td class="text-center">
						{$ERROR_MESSAGE}
					</td>
				</tr>
			{/if}
			<tr>
				<td>
					<table class="w-100 dvtSelectedCell thickBorder importContents">
						<tr>
							<td class="pl-3">{\App\Language::translate('LBL_TOTAL_RECORDS_IMPORTED', $MODULE_NAME)}</td>
							<td class="u-w-10per">:</td>
							<td class="u-w-30per pr-3">{$IMPORT_RESULT.IMPORTED} / {$IMPORT_RESULT.TOTAL}</td>
						</tr>
						<tr>
							<td class="pl-3">{\App\Language::translate('LBL_NUMBER_OF_RECORDS_CREATED', $MODULE_NAME)}</td>
							<td class="u-w-10per">:</td>
							<td class="u-w-10per pr-3">{$IMPORT_RESULT.CREATED}</td>
						</tr>
						<tr>
							<td class="pl-3">{\App\Language::translate('LBL_NUMBER_OF_RECORDS_UPDATED', $MODULE_NAME)}</td>
							<td class="u-w-10per">:</td>
							<td class="u-w-10per pr-3">{$IMPORT_RESULT.UPDATED}</td>
						</tr>
						<tr>
							<td class="pl-3">{\App\Language::translate('LBL_NUMBER_OF_RECORDS_SKIPPED', $MODULE_NAME)}</td>
							<td class="u-w-10per">:</td>
							<td class="u-w-10per pr-3">{$IMPORT_RESULT.SKIPPED}</td>
						</tr>
						<tr>
							<td class="pl-3">{\App\Language::translate('LBL_NUMBER_OF_RECORDS_MERGED', $MODULE_NAME)}</td>
							<td class="u-w-10per">:</td>
							<td class="u-w-10per pr-3">{$IMPORT_RESULT.MERGED}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="float-right">
					<button class="delete btn btn-danger btn-sm" name="cancel"
						onclick="location.href = 'index.php?module={$FOR_MODULE}&view=Import&mode=cancelImport&import_id={$IMPORT_ID}'">
						<strong>
							{\App\Language::translate('LBL_CANCEL_IMPORT', $MODULE_NAME)}
						</strong>
					</button>
				</td>
			</tr>
		</table>
	</div>
	<!-- /tpl-Import-ImportStatus -->
{/strip}
