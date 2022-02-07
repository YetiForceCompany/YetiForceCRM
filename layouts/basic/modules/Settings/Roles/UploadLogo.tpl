{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Roles-UploadLogo -->
	<form name="UploadLogo" class="form-horizontal js-form-upload-logo validateForm"
		action="index.php" method="post" enctype="multipart/form-data" data-js="container">
		<input type="hidden" name="module" value="Roles">
		<input type="hidden" name="parent" value="Settings" />
		<input type="hidden" name="action" value="UploadLogo">
		<input type="hidden" name="mode" value="upload" />
		<div class="modal-body">
			<div class="form-group row">
				<label class="col-sm-4 col-form-label">
					{\App\Language::translate('LBL_SELECT_FILE', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-8 controls">
					<input type="file" name="role_logo" class="fieldValue js-role-logo" data-validation-engine="validate[required]">
				</div>
			</div>
		</div>
		<div class="tpl-Modals-Footer modal-footer">
			<div class="float-right cancelLinkContainer">
				<button class="btn btn-success saveButton" type="submit">
					<strong>
						<span class="fa fa-check u-mr-5px"></span>{\App\Language::translate('LBL_UPLOAD_LOGO', $QUALIFIED_MODULE)}
					</strong>
				</button>
				<button class="btn btn-warning" type="reset" data-dismiss="modal">
					<span class="fa fa-times u-mr-5px"></span>{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
				</button>
			</div>
		</div>
	</form>
	<!-- /tpl-Settings-Roles-UploadLogo -->
{/strip}
