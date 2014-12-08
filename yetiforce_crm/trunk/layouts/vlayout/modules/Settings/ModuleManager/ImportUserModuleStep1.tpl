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
		<h3>{vtranslate('LBL_IMPORT_MODULE_FROM_FILE', $QUALIFIED_MODULE)}</h3>
	</div><hr>
	<div class="contents">
		<div class="row-fluid">
			<form class="form-horizontal contentsBackground" id="importUserModule" name="importUserModule" action='index.php' method="POST" enctype="multipart/form-data">
				<input type="hidden" name="module" value="ModuleManager" />
				<input type="hidden" name="moduleAction" value="Import"/>
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="view" value="ModuleImport" />
				<input type="hidden" name="mode" value="importUserModuleStep2" />
				<div name='uploadUserModule'>
					<div class="modal-body tabbable">
						<div class="tab-content massEditContent">
							<table class="massEditTable table table-bordered">
								<tr>
									<td class="fieldLabel alignMiddle">{vtranslate('LBL_IMPORT_MODULE_FROM_FILE', $QUALIFIED_MODULE)}</td>
									<td class="fieldValue">
										<input type="file" name="moduleZip" id="moduleZip" size="80px" 
											  data-validation-engine="validate[required, funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
											  data-validator={Zend_Json::encode([['name'=>'UploadModuleZip']])}
										/>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class=" pull-right cancelLinkContainer">
						<a class="cancelLink" href="index.php?module=ModuleManager&parent=Settings&view=List">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
					</div>
					<button class="btn btn-success" type="submit" name="saveButton"><strong>{vtranslate('LBL_IMPORT', $MODULE)}</strong></button>
				</div>
			</form>
		</div>
	</div>
</div>
{/strip}