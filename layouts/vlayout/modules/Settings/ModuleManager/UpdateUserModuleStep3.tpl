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
<div class="container-fluid" id="importModules">
	<div class="widget_header row-fluid">
		<h3>{vtranslate('LBL_UPDATE_MODULE_FROM_FILE', $QUALIFIED_MODULE)}</h3>
	</div><hr>
	<div class="contents">
		<div class="row-fluid">
			<div id="vtlib_modulemanager_import_div">
				<form method="POST" action="index.php">
					<table class="table table-bordered">
						<thead>
							<tr class="blockHeader">
								<th colspan="2"><strong>{vtranslate('LBL__UPDATING_MODULE',$QUALIFIED_MODULE)}</strong></th>
							</tr>
						</thead>
						<tbody>
							<tr valign=top>
								<td class='cellText small'>
									{$UPDATE_MODULE_NAME} {vtranslate('LBL_UPDATED_MODULE', $QUALIFIED_MODULE)}
								</td>
							</tr>
						</tbody>
					</table>
					<div class="modal-footer">
						<input type="hidden" name="module" value="ModuleManager">
						<input type="hidden" name="parent" value="Settings">
						<input type="hidden" name="view" value="List">
						<button  class="btn btn-success" type="submit" ><strong>{vtranslate('LBL_FINISH', $QUALIFIED_MODULE)}</strong></button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
{/strip}