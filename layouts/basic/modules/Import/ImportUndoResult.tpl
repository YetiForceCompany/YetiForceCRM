{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	<div class='widget_header row '>
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
	</div>
	<div class="col-md-3 col-sm-2"></div>
	<div class="col-md-6 col-sm-8 col-xs-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4 class="panel-title">{'LBL_IMPORT'|@vtranslate:$MODULE} {$FOR_MODULE|@vtranslate:$FOR_MODULE} - {'LBL_UNDO_RESULT'|@vtranslate:$MODULE}</h4>
			</div>
			<div class="panel-body form-horizontal font-larger">
				<input type="hidden" name="module" value="{$FOR_MODULE}" />
				{if $ERROR_MESSAGE neq ''}
					<div class="alert alert-warning">
						{$ERROR_MESSAGE}rewtwerterte ert ewrtewrgetr
					</div>
				{/if}
				<div class="form-group">
					<div class="col-md-7 col-sm-6 col-xs-8 textAlignRight fontBold">{'LBL_TOTAL_RECORDS'|@vtranslate:$MODULE}:</div>
					<div class="col-md-5 col-sm-6 col-xs-4">
						{$TOTAL_RECORDS}
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-7 col-sm-6 col-xs-8 textAlignRight fontBold">{'LBL_NUMBER_OF_RECORDS_DELETED'|@vtranslate:$MODULE}:</div>
					<div class="col-md-5 col-sm-6 col-xs-4">
						{$DELETED_RECORDS_COUNT}
					</div>
				</div>
			</div>
			<div class="modal-footer">
				{include file='Import_Done_Buttons.tpl'|@vtemplate_path:'Import'}
			</div>
		</div>
	</div>
	<div class="col-md-3 col-sm-2"></div>
{/strip}
