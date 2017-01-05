{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-header">
		<button data-dismiss="modal" class="close" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
		<h3 class="modal-title">{vtranslate('LBL_MASS_ADD', $MODULE)}</h3>
	</div>
	<form class="form-horizontal" id="addDocuments" method="post" action="index.php" enctype="multipart/form-data">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="action" value="MassAdd" />
		<div class="modal-body row">
			<div class="col-md-12 uploadFileContainer">
				<input type="file" name="file[]" multiple id="filesToUpload">
			</div>
			<div class="fileContainer hide">
				<div class="fileItem">
					<label>{vtranslate('Title', $MODULE)}</label>
					<div class="input-group">
						<input type="text" name="nameFile[]" class="form-control">
						<span class="input-group-addon" id="basic-addon1"><span class="glyphicon glyphicon-file"></span></span>
					</div>
				</div>
			</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
	</form>
{/strip}
