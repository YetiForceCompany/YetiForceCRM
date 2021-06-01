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
	<div class="tpl-Settings-ModuleManager-ImportUserModuleStep3" id="importModules">
		<div class='widget_header row '>
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="contents">
			<div>
				<div id="vtlib_modulemanager_import_div">
					<table class="table table-bordered mt-2">
						<thead>
							<tr class="blockHeader">
								<th colspan="2">
									<strong>{\App\Language::translate('LBL_IMPORTING_MODULE',$QUALIFIED_MODULE)}</strong>
								</th>
							</tr>
						</thead>
						<tbody>
							<tr valign=top>
								<td class='cellText small'>
									{if isset($MODULEIMPORT_ERROR) && $MODULEIMPORT_ERROR}
										<div class="alert alert-warning">
											<div class="modal-header">
												<h3>{\App\Language::translate('LBL_FAILED', $QUALIFIED_MODULE)}</h3>
											</div>
											<div class="modal-body">
												<p>
													<b>{\App\Language::translate($MODULEIMPORT_ERROR, $QUALIFIED_MODULE)}</b>
												</p>
											</div>
										</div>
									{else}
										{if $IMPORT_MODULE_TYPE eq 'Language'}
											{\App\Language::translate('LBL_IMPORTED_LANGUAGE', $QUALIFIED_MODULE)}
										{else if $IMPORT_MODULE_TYPE eq 'extension'}
											{\App\Language::translate('LBL_IMPORTED_EXTENSION', $QUALIFIED_MODULE)}
										{else if $IMPORT_MODULE_TYPE eq 'update'}
											{\App\Language::translateArgs('LBL_IMPORTED_UPDATE', $QUALIFIED_MODULE, $MODULEIMPORT_LABEL)}
										{else if $IMPORT_MODULE_TYPE eq 'font'}
											{\App\Language::translate('LBL_IMPORTED_FONT', $QUALIFIED_MODULE)}
										{else}
											{\App\Language::translateArgs('LBL_IMPORTED_MODULE', $QUALIFIED_MODULE, $MODULEIMPORT_LABEL)}
										{/if}
									{/if}
								</td>
							</tr>
						</tbody>
					</table>
					{if $IMPORT_MODULE_TYPE eq 'update'}
						<a href="index.php?parent=Settings&module=Updates&view=Index" role="button" class="btn btn-success float-right">
							<span class="fas fa-check mr-1"></span>{\App\Language::translate('LBL_FINISH', $QUALIFIED_MODULE)}
						</a>
					{else}
						<a href="index.php?module=ModuleManager&parent=Settings&view=List" role="button" class="btn btn-success float-right">
							<span class="fas fa-check mr-1"></span>{\App\Language::translate('LBL_FINISH', $QUALIFIED_MODULE)}
						</a>
					{/if}
				</div>
			</div>
		</div>
	</div>
{/strip}
