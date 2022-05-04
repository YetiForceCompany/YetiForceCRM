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
	<div class="tpl-Settings-ModuleManager-ImportUserModuleStep1" id="importModules">
		<div class="o-breadcrumb widget_header row mb-2">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		{if \App\YetiForce\Register::isRegistered()}
			{assign var=MAXUPLOADSIZE value=\App\Config::getMaxUploadSize(false)}
			{if $MAXUPLOADSIZE < 5242880}
				<div class="alert alert-block alert-danger fade show" role="alert">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<h4 class="alert-heading">{\App\Language::translate('LBL_TOO_SMALL_UPLOAD_LIMIT', $QUALIFIED_MODULE)}</h4>
					<p>{\App\Language::translateArgs('LBL_TOO_SMALL_UPLOAD_LIMIT_DESC', $QUALIFIED_MODULE, vtlib\Functions::showBytes($MAXUPLOADSIZE),'<a href="index.php?parent=Settings&module=ConfReport&view=Index">'|cat:\App\Language::translate('LBL_CHECK_SERVER_CONFIG',$QUALIFIED_MODULE)|cat:'</a>')}</p>
				</div>
			{/if}
			<div class="contents">
				<div>
					<form class="form-horizontal js-validation-engine" id="importUserModule" name="importUserModule"
						action='index.php' method="POST" enctype="multipart/form-data" data-js="container">
						<input type="hidden" name="module" value="ModuleManager" />
						<input type="hidden" name="moduleAction" value="Import" />
						<input type="hidden" name="parent" value="Settings" />
						<input type="hidden" name="view" value="ModuleImport" />
						<input type="hidden" name="mode" value="importUserModuleStep2" />
						<div name="uploadUserModule">
							<table class="massEditTable table table-bordered">
								<thead>
									<tr class="blockHeader">
										<th class="fieldLabel">
											<strong>{\App\Language::translate('LBL_IMPORT_MODULE_FROM_FILE', $QUALIFIED_MODULE)}</strong>
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<div class="fieldValue position-relative">
												<input type="file" class="js-validation-zip" data-js="container"
													name="moduleZip" id="moduleZip"
													data-validation-engine="validate[required, funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
													accept=".ZIP" />
											</div>

										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="d-flex justify-content-end">
							<button class="btn btn-success mr-1" type="submit" name="saveButton">
								<span class="fas fa-check mr-1"></span>
								{\App\Language::translate('LBL_IMPORT', $QUALIFIED_MODULE)}
							</button>
							<div class="cancelLinkContainer">
								<a role="button" class="cancelLink btn btn-danger"
									href="index.php?module=ModuleManager&parent=Settings&view=List">
									<span class="fas fa-times mr-1"></span>
									{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
								</a>
							</div>
						</div>
					</form>
				</div>
			</div>
		{else}
			<div class="col-md-12">
				<div class="alert alert-danger">
					<span class="yfi yfi-yeti-register-alert color-red-600 u-fs-5x mr-4 float-left"></span>
					<h1 class="alert-heading">{\App\Language::translate('LBL_YETIFORCE_NOT_REGISTRATION_TITLE',$QUALIFIED_MODULE)}</h1>
					{\App\Language::translate('LBL_YETIFORCE_NOT_REGISTRATION_DESC',$QUALIFIED_MODULE)}
				</div>
			</div>
		{/if}
	</div>
{/strip}
