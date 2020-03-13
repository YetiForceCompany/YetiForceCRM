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
	<div id="importModules">
		<div class="o-breadcrumb widget_header row mb-2">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="contents">
			<div>
				<div id="vtlib_modulemanager_import_div">
					<form method="POST" action="index.php">
						<table class="table table-bordered">
							<thead>
							<tr class="blockHeader">
								<th colspan="2">
									<strong>{\App\Language::translate('LBL__UPDATING_MODULE',$QUALIFIED_MODULE)}</strong>
								</th>
							</tr>
							</thead>
							<tbody>
							<tr valign=top>
								<td class='cellText small'>
									{$UPDATE_MODULE_NAME} {\App\Language::translate('LBL_UPDATED_MODULE', $QUALIFIED_MODULE)}
								</td>
							</tr>
							</tbody>
						</table>
						<input type="hidden" name="module" value="ModuleManager">
						<input type="hidden" name="parent" value="Settings"/>
						<input type="hidden" name="view" value="List">
						<button class="btn btn-success float-right" type="submit">
							<span class="fas fa-check mr-1"></span>
							{\App\Language::translate('LBL_FINISH', $QUALIFIED_MODULE)}
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
{/strip}
