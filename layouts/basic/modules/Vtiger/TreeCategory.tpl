{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="massEditHeader" class="modal-title">{vtranslate('LBL_EDITING', $MODULE_NAME)}</h3>
	</div>
	<div id="treePopupContainer" class="modal-body">
		<input type="hidden" name="src_record" value="{$SRC_RECORD}" />
		<input type="hidden" name="src_module" value="{$SRC_MODULE}" />
		<input type="hidden" name="template" value="{$TEMPLATE}" />
		<input type="hidden" name="tree" id="treePopupValues" value="{Vtiger_Util_Helper::toSafeHTML($TREE)}" />
		<div>
			<div id="treePopupContents"></div>
		</div>
	</div>
	{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
{/strip}
