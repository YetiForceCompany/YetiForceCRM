{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-OSSMailScanner-Folders -->
	<div class="modal-header">
		<h5 class="modal-title">
			<span class="fas fa-folder-open mr-1"></span>
			{\App\Language::translate('LBL_EDIT_FOLDER_ACCOUNT', $MODULE_NAME)} - {$ADDRESS_EMAIL}
		</h5>
		<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="modal-body" data-user="{$RECORD}">
		<div class="js-tree-container" data-js="jstree">
			<input class="js-tree-data" value="{\App\Purifier::encodeHtml($TREE)}">
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-success mr-1" type="submit" name="saveButton">
			<strong>
				<span class="fas fa-check mr-1"></span>
				{\App\Language::translate('LBL_SAVE', $MODULE_NAME)}
			</strong>
		</button>
		<button class="btn btn-danger" type="reset" data-dismiss="modal">
			<strong>
				<span class="fas fa-times mr-1"></span>
				{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}
			</strong>
		</button>
	</div>
	<!-- /tpl-Settings-OSSMailScanner-Folders -->
{/strip}
