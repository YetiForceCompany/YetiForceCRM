{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-header d-block">
		<div class="d-flex">
			<h5 class="modal-title">
				<span class="fas fa-search-plus mr-1"></span>
				{\App\Language::translate('LBL_EDITING', $MODULE)}
			</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="input-group pt-2">
			<input id="valueSearchTree" type="text" class="form-control" placeholder="{\App\Language::translate('LBL_SEARCH', $MODULE)} ...">
			<div class="input-group-append">
				<button id="btnSearchTree" class="btn btn-danger" type="button">{\App\Language::translate('LBL_SEARCH', $MODULE)}</button>
			</div>
		</div>
	</div>
	<div id="treePopupContainer" class="modal-body col-md-12">
		<input type="hidden" id="relationType" value="{$RELATION_TYPE}" />
		<input type="hidden" id="relatedModule" value="{$MODULE}" />
		<input type="hidden" name="tree" id="treePopupValues" value="{\App\Purifier::encodeHtml($TREE)}" />
		{if $TREE}
			<div class="col-md-12 mb-2">
				<div class="col-md-12" id="treePopupContents"></div>
			</div>
		{else}
			<h4 class="textAlignCenter ">{\App\Language::translate('LBL_RECORDS_NO_FOUND', $MODULE)}</h4>
		{/if}
	</div>
	<div class="modal-footer d-block">
		<div class="float-left pt-2 counterSelected"></div>
		<div class="float-right">
			<button class="btn btn-success mr-1" type="submit" name="saveButton">
				<strong>{\App\Language::translate('LBL_SAVE', $MODULE)}</strong>
			</button>
			<button class="btn btn-danger" type="reset" data-dismiss="modal">
				<strong>{\App\Language::translate('LBL_CANCEL', $MODULE)}</strong>
			</button>
		</div>
	</div>
{/strip}
