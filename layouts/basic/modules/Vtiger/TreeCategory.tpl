{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="massEditHeader" class="modal-title">{vtranslate('LBL_EDITING', $MODULE_NAME)}</h3>
	</div>
	<div id="treePopupContainer" class="modal-body col-md-12">
		<input type="hidden" name="related_module" value="{$MODULE}" />
		<input type="hidden" name="src_record" value="{$SRC_RECORD}" />
		<input type="hidden" name="src_module" value="{$SRC_MODULE}" />
		<input type="hidden" name="template" value="{$TEMPLATE}" />
		<input type="hidden" name="tree" id="treePopupValues" value="{Vtiger_Util_Helper::toSafeHTML($TREE)}" />
		{if count($TREE) != 0}
			<div class="col-md-12 marginBottom10px">
				<div class="input-group">
					<input id="valueSearchTree" type="text" class="form-control" placeholder="{vtranslate('LBL_SEARCH', $MODULE_NAME)} ..." >
					<span class="input-group-btn">
						<button id="btnSearchTree" class="btn btn-danger" type="button">{vtranslate('LBL_SEARCH', $MODULE_NAME)}</button>
					</span>
				</div>
			</div>
			<div class="col-md-12 marginBottom10px">
				<div class="col-md-12" id="treePopupContents"></div>
			</div>
			<div class="counterSelected">
			</div>
		{else}	
			<h4 class="textAlignCenter ">{vtranslate('LBL_RECORDS_NO_FOUND', $MODULE_NAME)}</h4>
		{/if}
	</div>
	{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
{/strip}
