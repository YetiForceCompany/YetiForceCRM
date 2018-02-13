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
	<div class="">
		{assign var=MODULE_MODEL value=Settings_PBXManager_Module_Model::getCleanInstance()}
		<form id="MyModal" class="form-horizontal" data-detail-url="{$MODULE_MODEL->getDetailViewUrl()}">
			<div class="widget_header row">
				<div class="col-md-8">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
				</div>
				<div class="col-md-4 btn-toolbar">
					<div class="float-right">
						<button class="btn btn-success saveButton" type="submit" title="{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}"><strong>{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
						<button type="reset" class="cancelLink btn btn-warning" title="{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}">{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</button>
					</div>
				</div>
			</div>
			<div class="contents">
				<table class="table table-bordered table-condensed themeTableColor">
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
							<tr><td width="25%"><label class="muted float-right marginRight10px"><span class="redColor">*</span>{\App\Language::translate($FIELD_NAME,$QUALIFIED_MODULE)}</label></td>
								<td style="border-left: none;"><input type="{$FIELD_TYPE}" class="form-control" name="{$FIELD_NAME}" data-validation-engine='validate[required]' value="{$RECORD_MODEL->get($FIELD_NAME)}" /></td></tr>
								{/foreach}
					<input type="hidden" name="module" value="PBXManager" />
					<input type="hidden" name="action" value="SaveAjax" />
					<input type="hidden" name="parent" value="Settings" />
					<input type="hidden" name="id" value="{$RECORD_ID}">
					</tbody>
				</table>
			</div>
		</form>
	</div><br />
	<div class="col-md-5 paddingLRZero">
		<div class="alert alert-info">
			{\App\Language::translate('LBL_NOTE', $QUALIFIED_MODULE)}<br />
			{\App\Language::translate('LBL_INFO_WEBAPP_URL', $QUALIFIED_MODULE)}<br />
			{\App\Language::translate('LBL_FORMAT_WEBAPP_URL', $QUALIFIED_MODULE)}<br />
			{\App\Language::translate('LBL_FORMAT_INFO_WEBAPP_URL', $QUALIFIED_MODULE)}
		</div>
	</div>
{/strip}
