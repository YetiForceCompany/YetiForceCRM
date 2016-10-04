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
		<div class='widget_header row '>
			<div class="col-xs-12">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
				{if isset($SELECTED_PAGE)}
					{vtranslate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
				{/if}
			</div>
		</div>
		<div class="contents">
			<div class="">
				<div id="vtlib_modulemanager_import_div">
					<form method="POST" action="index.php">
						<table class="table table-bordered">
							<thead>
								<tr class="blockHeader">
									<th colspan="2"><strong>{vtranslate('LBL_IMPORTING_MODULE',$QUALIFIED_MODULE)}</strong></th>
								</tr>
							</thead>
							<tbody>
								<tr valign=top>
									<td class='cellText small'>
										{if $MODULEIMPORT_ERROR}
											<div class="alert alert-warning">
												<div class="modal-header">
													<h3>{vtranslate('LBL_FAILED', $QUALIFIED_MODULE)}</h3>
												</div>
												<div class="modal-body">
													<p><b>{vtranslate($MODULEIMPORT_ERROR, $QUALIFIED_MODULE)}</b></p>
												</div>
											</div>
										{else}
											{if $IMPORT_MODULE_TYPE eq 'Language'}
												{vtranslate('LBL_IMPORTED_LANGUAGE', $QUALIFIED_MODULE)}
											{else if $IMPORT_MODULE_TYPE eq 'extension'}
												{vtranslate('LBL_IMPORTED_EXTENSION', $QUALIFIED_MODULE)}
											{else if $IMPORT_MODULE_TYPE eq 'update'}
												{vtranslate('LBL_IMPORTED_UPDATE', $QUALIFIED_MODULE)}
											{else}
												{vtranslate('LBL_IMPORTED_MODULE', $QUALIFIED_MODULE, $IMPORT_MODULE_NAME)}
											{/if}
										{/if}
									</td>
								</tr>
							</tbody>
						</table>
						<div class="modal-footer">
							<a href="index.php?module=ModuleManager&parent=Settings&view=List" class="btn btn-success"><strong>{vtranslate('LBL_FINISH', $QUALIFIED_MODULE)}</strong></a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
{/strip}
