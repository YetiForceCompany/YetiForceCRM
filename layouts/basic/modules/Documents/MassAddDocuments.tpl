{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-header">
		<h5 class="modal-title">{\App\Language::translate('LBL_MASS_ADD', $MODULE)}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
        </button>
	</div>
	<form class="form-horizontal" id="addDocuments" method="post" action="index.php" enctype="multipart/form-data">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="action" value="MassAdd" />
		<div class="modal-body row">
			<div class="col-md-12 uploadFileContainer">
				<input type="file" name="file[]" multiple id="filesToUpload">
			</div>
			<div class="fileContainer d-none">
				<div class="fileItem">
					<label>{\App\Language::translate('Title', $MODULE)}</label>
					<div class="input-group">
						<input type="text" name="nameFile[]" class="form-control">
						<span class="input-group-addon" id="basic-addon1"><span class="fas fa-file"></span></span>
					</div>
				</div>
			</div>
		</div>
		{include file=\App\Layout::getTemplatePath('ModalFooter.tpl', $MODULE)}
	</form>
{/strip}
