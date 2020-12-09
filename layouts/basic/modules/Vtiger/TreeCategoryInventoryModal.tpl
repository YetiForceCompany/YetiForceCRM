{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div id="treePopupContainer" class="modal-body col-md-12">
		<div class="input-group pt-2">
			<input id="valueSearchTree" type="text" class="form-control" placeholder="{\App\Language::translate('LBL_SEARCH', $MODULE_NAME)} ..." />
			<div class="input-group-append">
				<button id="btnSearchTree" class="btn btn-light" type="button">
					<span class="fas fa-search mr-2"></span> {\App\Language::translate('LBL_SEARCH', $MODULE_NAME)}
				</button>
			</div>
		</div>
		<input type="hidden" name="tree" id="treePopupValues" value="{\App\Purifier::encodeHtml($TREE)}"/>
		{if $TREE}
			<div class="col-md-12 mb-2">
				<div class="col-md-12" id="treePopupContents"></div>
			</div>
		{else}
			<h4 class="textAlignCenter ">{\App\Language::translate('LBL_RECORDS_NO_FOUND', $MODULE_NAME)}</h4>
		{/if}
	</div>
{/strip}
