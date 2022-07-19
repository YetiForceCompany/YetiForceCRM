{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Documents-MassAddDocuments-->
	<div class="modal-header">
		<h5 class="modal-title">
			<span class="yfi-document-templates mr-2"></span>
			{\App\Language::translate('LBL_MASS_ADD', $MODULE_NAME)}
		</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<form class="form-horizontal" id="addDocuments" method="post" action="index.php" enctype="multipart/form-data">
		<input type="hidden" name="module" value="{$MODULE_NAME}" />
		<input type="hidden" name="action" value="MassAdd" />
		{if !empty($SOURCE_MODULE)}
			<input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}" />
		{/if}
		{if !empty($SOURCE_RECORD)}
			<input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}" />
		{/if}
		<div class="modal-body row">
			<div class="col-md-12 uploadFileContainer">
				<input type="file" name="file[]" multiple id="filesToUpload">
			</div>
			<div class="fileContainer d-none">
				<div class="fileItem">
					<label>{\App\Language::translate('Title', $MODULE_NAME)}</label>
					<div class="input-group">
						<input type="text" name="nameFile[]" class="form-control">
						<div class="input-group-append">
							<span class="input-group-text">
								<span class="fas fa-file"></span>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE_NAME) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
	</form>
	<!-- /tpl-Documents-MassAddDocuments-->
{/strip}
