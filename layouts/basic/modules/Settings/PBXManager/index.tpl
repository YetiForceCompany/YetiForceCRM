{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
	<div class="tpl-Settings-PBXManager-index" id="AsteriskServerDetails">
		<div class="widget_header row align-items-center">
			<div class="col-md-8">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
			</div>
			{assign var=MODULE_MODEL value=Settings_PBXManager_Module_Model::getCleanInstance()}
			<div class="col-md-4 pr-3">
				<div class="float-right">
					<button class="btn btn-info editButton" data-url='{$MODULE_MODEL->getEditViewUrl()}&mode=showpopup&id={$RECORD_ID}' type="button" title="{\App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}">
						<span class="fa fa-edit u-mr-5px"></span><strong>{\App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}</strong>
					</button>
				</div>
			</div>
		</div>
		<div class='clearfix'></div>
		<div class="contents mt-2">
			<table class="table table-bordered table-sm themeTableColor">
				<thead>
					<tr class="blockHeader">
						<th colspan="2" class="mediumWidthType">
							<span class="alignMiddle">{\App\Language::translate('LBL_PBXMANAGER_CONFIG', $QUALIFIED_MODULE)}</span>
						</th>
					</tr>
				</thead>
				<tbody>
					{assign var=FIELDS value=PBXManager_PBXManager_Connector::getSettingsParameters()}
					{foreach item=FIELD_TYPE key=FIELD_NAME from=$FIELDS}
						<tr><td class="u-w-30per align-middle pr-2"><label class="muted float-right u-text-small-bold align-middle mb-0">{\App\Language::translate($FIELD_NAME,$QUALIFIED_MODULE)}</label></td>
							<td class="border-left-0"><span>{$RECORD_MODEL->get($FIELD_NAME)}</span></td></tr>
								{/foreach}
				<input type="hidden" name="module" value="PBXManager" />
				<input type="hidden" name="action" value="SaveAjax" />
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" class="recordid" name="id" value="{$RECORD_ID}">
				</tbody>
			</table>
		</div>
		<br />
		<div class="col-md-8 alert alert-danger container form-row m-0">
			{\App\Language::translate('LBL_NOTE', $QUALIFIED_MODULE)}<br />
			{\App\Language::translate('LBL_PBXMANAGER_INFO', $QUALIFIED_MODULE)}
		</div>	
	</div>
{/strip}
