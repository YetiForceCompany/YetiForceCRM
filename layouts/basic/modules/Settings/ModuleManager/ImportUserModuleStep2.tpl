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
	<div class="" id="importModules">
		<div class='widget_header row '>
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
				{if isset($SELECTED_PAGE)}
					{\App\Language::translate($SELECTED_PAGE->get('description'),$QUALIFIED_MODULE)}
				{/if}
			</div>
		</div>
		<div class="contents">
			<div>
				<div id="vtlib_modulemanager_import_div">
					<form method="POST" action="index.php">
						<input type="hidden" name="module" value="ModuleManager">
						<input type="hidden" name="parent" value="Settings" />
						{if $MODULEIMPORT_ERROR neq ''}
							<div class="alert alert-warning">
								<div class="modal-header">
									<h3>{\App\Language::translate('LBL_FAILED', $QUALIFIED_MODULE)}</h3>
								</div>
								<div class="modal-body">
									<p><b>{\App\Language::translate($MODULEIMPORT_ERROR, $QUALIFIED_MODULE)}</b></p>
								</div>
								<div class="">
									<input type="hidden" name="view" value="List">
									<button  class="btn btn-success" type="submit"><strong>{\App\Language::translate('LBL_FINISH', $QUALIFIED_MODULE)}</strong></button>
								</div>
							</div>
						{else}
							<table class="table table-bordered">
								<thead>
									<tr class="blockHeader">
										<th colspan="2"><strong>{\App\Language::translate('LBL_VERIFY_IMPORT_DETAILS',$QUALIFIED_MODULE)}</strong></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td style="min-width: 100px;"><b>{\App\Language::translate('LBL_MODULE_NAME', $QUALIFIED_MODULE)}</b></td>
										<td>
											{\App\Language::translate($MODULEIMPORT_NAME, $QUALIFIED_MODULE)}
											{if $MODULEIMPORT_EXISTS eq 'true'} <font color=red><b>{\App\Language::translate('LBL_EXISTS', $QUALIFIED_MODULE)}</b></font> {/if}
										</td>
									</tr>
									<tr>
										<td><b>{\App\Language::translate('LBL_MODULE_TYPE', $QUALIFIED_MODULE)}</b></td>
										<td>{\App\Language::translate($MODULEIMPORT_PACKAGE->getTypeName(), $QUALIFIED_MODULE)}</td>
									</tr>
									<tr>
										<td><b>{\App\Language::translate('LBL_REQ_YETIFORCE_VERSION', $QUALIFIED_MODULE)}</b></td>
										<td>{$MODULEIMPORT_DEP_VTVERSION}</td>
									</tr>
									<tr>
										<td><b>{\App\Language::translate('LBL_MODULE_VERSION', $QUALIFIED_MODULE)}</b></td>
										<td>{$MODULEIMPORT_PACKAGE->getVersion()}</td>
									</tr>
									{if $MODULEIMPORT_PACKAGE->isUpdateType()}
										{assign var="INFO" value=$MODULEIMPORT_PACKAGE->getUpdateInfo()}
										<tr>
											<td><b>{\App\Language::translate('LBL_UPDATE_FROM_VERSION', $QUALIFIED_MODULE)}</b></td>
											<td>{$INFO['from']}</td>
										</tr>
										<tr>
											<td><b>{\App\Language::translate('LBL_UPDATE_TO_VERSION', $QUALIFIED_MODULE)}</b></td>
											<td>{$INFO['to']}</td>
										</tr>
									{/if}
									{assign var="need_license_agreement" value="false"}
									{if $MODULEIMPORT_LICENSE}
										{assign var="need_license_agreement" value="true"}
										<tr>
											<td width=20%>
												{if $MODULEIMPORT_PACKAGE->isUpdateType()}
													<b>{\App\Language::translate('Attention')}</b>
												{else}
													<b>{\App\Language::translate('LBL_LICENSE', $QUALIFIED_MODULE)}</b>
												{/if}
											</td>
											<td>
												<textarea rows="10" readonly class='form-control'>{$MODULEIMPORT_LICENSE}</textarea><br />
												{if $MODULEIMPORT_EXISTS neq 'true'}
													{literal}<input type="checkbox" id="license_agreement" onclick="if (this.form.saveButton) {
																if (this.checked) {
																	this.form.saveButton.disabled = false;
																} else {
																	this.form.saveButton.disabled = true;
																}
															}">{/literal}
														<label for="license_agreement" style="display: inline-block;margin-left: 10px;"> {\App\Language::translate('LBL_LICENSE_ACCEPT_AGREEMENT', $QUALIFIED_MODULE)}</label>
														{/if}
														</td>
													</tr>
													{/if}
														{foreach item=PARAMETER from=$MODULEIMPORT_PARAMETERS}
															<tr>
																<td colspan="2">
																	{if $PARAMETER->type == 'checkbox'}
																		<label>
																			<input value="1" autocomplete="off" type="checkbox" name="param_{$PARAMETER->name}" {if $PARAMETER->checked == '1'}checked{/if}>&nbsp;&nbsp;
																			{\App\Language::translate($PARAMETER->lable, $QUALIFIED_MODULE)}
																		</label>
																	{/if}
																</td>
															</tr>
														{/foreach}
													</tbody>
												</table>
												{if $MODULEIMPORT_DIR_EXISTS eq 'true'}
													<br />
													<div class="alert alert-danger" role="alert">{\App\Language::translate('LBL_DELETE_EXIST_DIRECTORY', $QUALIFIED_MODULE)}</div>
												{/if}
												<div class="modal-footer">
													{if $MODULEIMPORT_EXISTS eq 'true' || $MODULEIMPORT_DIR_EXISTS eq 'true'}
														<input type="hidden" name="view" value="List">
														<button class="btn btn-success" class="crmbutton small delete"
																onclick="this.form.mode.value = '';">
															<strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong>
														</button>
														{if $MODULEIMPORT_EXISTS eq 'true'}
															<input type="hidden" name="view" value="ModuleImport">
															<input type="hidden" name="module_import_file" value="{$MODULEIMPORT_FILE}">
															<input type="hidden" name="module_import_type" value="{$MODULEIMPORT_TYPE}">
															<input type="hidden" name="module_import_name" value="{$MODULEIMPORT_NAME}">
															<input type="hidden" name="mode" value="importUserModuleStep3">

															<input type="checkbox" class="float-right" onclick="this.form.mode.value = 'updateUserModuleStep3';
																	this.form.submit();" >
															<span class="float-right">I would like to update now.&nbsp;</span>
														{/if}
													{else}
														<input type="hidden" name="view" value="ModuleImport">
														<input type="hidden" name="module_import_file" value="{$MODULEIMPORT_FILE}">
														<input type="hidden" name="module_import_type" value="{$MODULEIMPORT_TYPE}">
														<input type="hidden" name="module_import_name" value="{$MODULEIMPORT_NAME}">
														<input type="hidden" name="mode" value="importUserModuleStep3">
														<span class="col-md-6 float-right">
															{\App\Language::translate('LBL_PROCEED_WITH_IMPORT', $QUALIFIED_MODULE)}&nbsp;&nbsp;
															<div class="float-right cancelLinkContainer">
																<a class="cancelLink btn btn-warning" type="reset" data-dismiss="modal" onclick="javascript:window.history.back();">{\App\Language::translate('LBL_NO', $MODULE)}</a>
															</div>
															<button  class="btn btn-success" type="submit" name="saveButton"
																	 {if $need_license_agreement eq 'true'} disabled {/if}><strong>{\App\Language::translate('LBL_YES')}</strong></button>
														</span>
													{/if}
												</div>
												{/if}
												</form>
											</div>
										</div>
									</div>
								</div>
								{/strip}
