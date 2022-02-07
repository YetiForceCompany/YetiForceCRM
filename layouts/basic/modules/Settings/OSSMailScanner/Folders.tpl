{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
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
		{if count($MISSING_FOLDERS) > 0}
			<div class="alert alert-danger" role="alert">
				{\App\Language::translate('LBL_INFO_ABOUT_FOLDERS_TO_REMOVE', $QUALIFIED_MODULE)}
				<ul>
					{foreach from=$MISSING_FOLDERS item=$FOLDER_NAME}
						<li>{$FOLDER_NAME}</li>
					{/foreach}
				</ul>
			</div>
		{/if}
		{if !$TREE}
			<div class="alert alert-danger" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				{\App\Language::translate('ERR_INCORRECT_ACCESS_DATA', $QUALIFIED_MODULE)}
			</div>
		{else}
			<div class="alert alert-warning" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				{\App\Language::translate('LBL_ALERT_EDIT_FOLDER', $MODULE_NAME)}
			</div>
			<div class="input-group mb-2">
				<div class="input-group-prepend">
					<div class="input-group-text"><span class="fas fa-search"></span></div>
				</div>
				<input type="text" id="folder-search" class="js-tree-search form-control" placeholder="{\App\Language::translate('LBL_SEARCH', $QUALIFIED_MODULE)}" data-js="keyup">
			</div>
			<div class="js-tree-container" data-js="jstree">
				<input class="js-tree-data" value="{\App\Purifier::encodeHtml(\App\Json::encode($TREE))}" data-js="val">
			</div>
		{/if}
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
