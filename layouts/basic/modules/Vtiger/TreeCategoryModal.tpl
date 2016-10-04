{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="massEditHeader" class="modal-title">{vtranslate('LBL_EDITING', $MODULE)}</h3>
		<div class="input-group paddingTop10">
			<input id="valueSearchTree" type="text" class="form-control" placeholder="{vtranslate('LBL_SEARCH', $MODULE)} ..." >
			<span class="input-group-btn">
				<button id="btnSearchTree" class="btn btn-danger" type="button">{vtranslate('LBL_SEARCH', $MODULE)}</button>
			</span>
		</div>
	</div>
	<div id="treePopupContainer" class="modal-body col-md-12">
		<input type="hidden" id="isActiveCategory" value="{$SELECTABLE_CATEGORY}" />
		<input type="hidden" id="relationType" value="{$RELATION_TYPE}" />
		<input type="hidden" id="relatedModule" value="{$MODULE}" />
		<input type="hidden" name="tree" id="treePopupValues" value="{Vtiger_Util_Helper::toSafeHTML($TREE)}" />
		{if count($TREE) != 0}
			<div class="col-md-12 marginBottom10px">
				<div class="col-md-12" id="treePopupContents"></div>
			</div>
		{else}	
			<h4 class="textAlignCenter ">{vtranslate('LBL_RECORDS_NO_FOUND', $MODULE)}</h4>
		{/if}
	</div>
	<div class="modal-footer">
		<div class="pull-left paddingTop10 counterSelected"></div>
		<div class="pull-right">
			<button class="btn btn-success" type="submit" name="saveButton"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
			<button class="btn btn-warning" type="reset" data-dismiss="modal"><strong>{vtranslate('LBL_CANCEL', $MODULE)}</strong></button>
		</div>
	</div>
{/strip}
