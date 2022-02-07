{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-header">
		<h5 class="modal-title">
			<span class="fas fa-download mr-1"></span>
			{\App\Language::translate('LBL_IMPORT_VIEW', $QUALIFIED_MODULE)}
		</h5>
		<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<form name="importTemplate" action="index.php" method="post" class="form-horizontal" enctype="multipart/form-data">
		<div class="modal-body">
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="module" value="MappedFields" />
			<input type="hidden" name="action" value="SaveAjax" />
			<input type="hidden" name="mode" value="import" />
			<div class="form-group">
				<label class="col-sm-3 col-form-label">
					{\App\Language::translate('LBL_TEMPLATE_XML', $QUALIFIED_MODULE)}
				</label>
				<div class="col-sm-8 controls">
					<input type="file" name="imported_xml" accept="text/xml" class="form-control" data-validation-engine='validate[required]' id="imported_xml" />
				</div>
			</div>

		</div>
		<div class="modal-footer">
			<div class="float-right">
				<button class="btn btn-success mr-1" type="submit">
					<strong>
						<span class="fas fa-download mr-1"></span>
						{\App\Language::translate('LBL_UPLOAD_TEMPLATE', $QUALIFIED_MODULE)}
					</strong>
				</button>
				<button type="button" class="btn btn-danger dismiss" data-dismiss="modal">
					<span class="fas fa-times mr-1"></span>
					{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}
				</button>
			</div>
		</div>
	</form>
{/strip}
