{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	<div class="modal-header">
		<button class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">x</button>
		<h4 class="modal-title">{\App\Language::translate('LBL_ADD_FILES', $MODULE_NAME)}</h4>
	</div>
	<form class="form-horizontal" id="fileUploadForm" name="fileUploadForm" method="post" action="file.php" enctype="multipart/form-data">
		<input type="hidden" name="module" value="{$MODULE_NAME}">
		<input type="hidden" name="action" value="Image">
		<input type="hidden" name="field" value="{$INPUT_NAME}">
		<input type="hidden" name="fileType" value="{$FILE_TYPE}">
		<input type="hidden" name="record" value="{$RECORD}">
		<input type="hidden" id="maxUploadFileSize" class="maxUploadFileSize" value={\AppConfig::main('upload_maxsize')}>
		<div class="modal-body">
			<div>
				<div class="uploadFileContainer">
				<input id="fileupload" class="fileupload marginBottom5" type="file" name="files[]" multiple {if $FILE_TYPE eq 'image'}accept="image/x-png,image/gif,image/jpeg"{/if}>
			</div>
				<div class="fileContainer hide">
				<div class="fileItem marginBottom5">
					<div class="input-group input-group-sm">
						<input type="text" name="nameFile[]" class="form-control" readonly>
						<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-file"></span></span>
					</div>
				</div>
			</div>
			</div>
		</div>
		<div class="modal-footer">
			<button class="btn btn-success sendFiles" type="submit" disabled="">{\App\Language::translate('BTN_SUBMIT', $MODULE_NAME)}</button>
			<button type="button" class="btn btn-warning dismiss" data-dismiss="modal">{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}</button>
		</div>
	</form>
{/strip}
