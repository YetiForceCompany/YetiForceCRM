{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-MailRbl-UploadListModal -->
	<div class="modal-body pb-0">
		<div class="row no-gutters">
			<div class="col-sm-18 col-md-12">
				<form name="importList" class="js-import-list form-horizontal validateForm" action="index.php" method="post" class="form-horizontal" enctype="multipart/form-data">
					<div class="modal-body">
						<input type="hidden" name="parent" value="Settings" />
						<input type="hidden" name="module" value="MailRbl" />
						<input type="hidden" name="action" value="UploadList" />
						<div class="form-group row">
							<div class="col-sm-3">
								<label class="col-form-label">
									{\App\Language::translate('LBL_LIST_SOURCE', $QUALIFIED_MODULE)}
								</label>
							</div>
							<div class="col-sm-9 controls">
								<input type="text" maxlength="10" id="source" class="form-control" data-validation-engine="validate[required]" name="source" />
							</div>
						</div>
						<div class="form-group row">
							<div class="col-sm-3">
								<label class="col-form-label">
									{\App\Language::translate('LBL_LIST_TYPE', $QUALIFIED_MODULE)}
								</label>
							</div>
							<div class="col-sm-9 controls">
								<select class="select2" name="type" data-validation-engine="validate[required]">
									<option value="2">{\App\Language::translate('LBL_PUBLIC_BLACK_LIST', $QUALIFIED_MODULE)}</option>
									<option value="3">{\App\Language::translate('LBL_PUBLIC_WHITE_LIST', $QUALIFIED_MODULE)}</option>
								</select>
							</div>
						</div>
						<div class="form-group row">
							<div class="col-sm-3">
								<label class="col-form-label">
									{\App\Language::translate('LBL_SELECT_LIST', $QUALIFIED_MODULE)}
								</label>
							</div>
							<div class="col-sm-9 controls">
								<input type="file" name="imported_list" accept=".txt" data-validation-engine="validate[required]" id="imported_list" />
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<div class="float-right">
							<button class="btn btn-success mr-1" type="submit">
								<strong>
									<span class="fas fa-download mr-1"></span>
									{\App\Language::translate('LBL_UPLOAD_LIST', $QUALIFIED_MODULE)}
								</strong>
							</button>
							<button type="button" class="btn btn-danger dismiss" data-dismiss="modal">
								<span class="fas fa-times mr-1"></span>
								{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-MailRbl-UploadListModal -->
{/strip}
