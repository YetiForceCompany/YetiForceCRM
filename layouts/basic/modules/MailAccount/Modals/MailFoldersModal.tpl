{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Modals-TreeModal modal-body js-modal-body" id="treePopupContainer" data-js="container">
		<input type="hidden" class="js-multiple" value="{$IS_MULTIPLE}" data-js="value" />
		<input type="hidden" class="js-tree-value" value="{App\Purifier::encodeHtml($TREE)}" data-js="value" />
		{if $ERROR}
			<div class="alert alert-warning" role="alert">
				{\App\Language::translate($ERROR, $MODULE_NAME)}
			</div>
		{elseif count($MISSING_FOLDERS) > 0}
			<div class="alert alert-danger" role="alert">
				{\App\Language::translate('LBL_INFO_ABOUT_FOLDERS_TO_REMOVE', $MODULE_NAME)}
				<ul class="mb-0">
					{foreach from=$MISSING_FOLDERS item=$FOLDER_NAME}
						<li>{\App\Purifier::encodeHtml($FOLDER_NAME)}</li>
					{/foreach}
				</ul>
			</div>
		{/if}

		<div class="js-tree-contents" data-js="container"></div>
	</div>
{/strip}
