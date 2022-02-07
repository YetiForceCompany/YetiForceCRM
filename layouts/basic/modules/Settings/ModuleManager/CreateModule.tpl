{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-ModuleManager-CreateModule modal addKeyContainer fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{\App\Language::translate('LBL_CREATING_MODULE', $QUALIFIED_MODULE)}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form>
						<div class="form-group row">
							<label class="col-sm-4 col-form-label text-right"><span
									class="text-danger">*</span>{\App\Language::translate('LBL_ENTER_MODULE_NAME', $QUALIFIED_MODULE)}
							</label>
							<div class="col-sm-6 controls">
								<input type="text" class="module_name form-control"
									data-validation-engine="validate[required, maxSize[{Settings_ModuleManager_Module_Model::$maxLengthModuleName}], funcCall[Settings_Module_Manager_Js.validateField], funcCall[Settings_Module_Manager_Js.validateModuleName]]"
									name="module_name" placeholder="HelpDesk" required="true">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-4 col-form-label text-right"><span
									class="text-danger">*</span>{\App\Language::translate('LBL_ENTER_MODULE_LABEL', $QUALIFIED_MODULE)}
							</label>
							<div class="col-sm-6 controls">
								<input type="text" class="module_name form-control"
									data-validation-engine="validate[required, maxSize[{Settings_ModuleManager_Module_Model::$maxLengthModuleLabel}], funcCall[Settings_Module_Manager_Js.validateField]]"
									name="module_label" placeholder="Help Desk" required="true">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-4 col-form-label text-right"><span
									class="text-danger">*</span>{\App\Language::translate('LBL_ENTITY_FIELDNAME', $QUALIFIED_MODULE)}
							</label>
							<div class="col-sm-6 controls">
								<input type="text" class="entityfieldname form-control"
									data-validation-engine="validate[required, maxSize[{Settings_ModuleManager_Module_Model::$maxLengthFieldName}], funcCall[Settings_Module_Manager_Js.validateField]]"
									name="entityfieldname" placeholder="{\App\Language::translate('LBL_SAMPLE_FIELD_NAME', $QUALIFIED_MODULE)}"
									required="true">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-4 col-form-label text-right"><span
									class="text-danger">*</span>{\App\Language::translate('LBL_ENTITY_FIELDLABEL', $QUALIFIED_MODULE)}
							</label>
							<div class="col-sm-6 controls">
								<input type="text" class="entityfieldlabel form-control"
									data-validation-engine="validate[required, maxSize[{Settings_ModuleManager_Module_Model::$maxLengthFieldLabel}], funcCall[Settings_Module_Manager_Js.validateField]]"
									name="entityfieldlabel" placeholder="{\App\Language::translate('LBL_SAMPLE_LABEL', $QUALIFIED_MODULE)}"
									required="true">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-4 col-form-label text-right">{\App\Language::translate('LBL_MODULE_TYPE', $QUALIFIED_MODULE)}</label>
							<div class="col-sm-6 controls">
								<select class="select2 form-control"
									title="{\App\Language::translate('LBL_MODULE_TYPE', $QUALIFIED_MODULE)}"
									name="entitytype">
									<option value="0"
										selected>{\App\Language::translate('LBL_BASE_MODULE', $QUALIFIED_MODULE)}</option>
									<option value="1">{\App\Language::translate('LBL_INVENTORY_MODULE', $QUALIFIED_MODULE)}</option>
								</select>
							</div>
						</div>
					</form>
				</div>
				{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
			</div>
		</div>
	</div>
{/strip}
