{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
<div class="modal fade" tabindex="-1">
	<div class="modal-dialog">
        <div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				{if is_array($MODULE_MODEL)}
					{assign var='MODULE_MODEL' value=current($MODULE_MODEL)}
					<h4 class="modal-title">{vtranslate('LBL_EDITING_INVENTORY_FIELD', $QUALIFIED_MODULE)}</h4>
				{else}
					<h4 class="modal-title">{vtranslate('LBL_CREATING_INVENTORY_FIELD', $QUALIFIED_MODULE)}</h4>
				{/if}
			</div>
			<form class="form-horizontal">
				<div class="modal-body">
					<input type="hidden" name="id" id="id" value="{$ID}" />
					<input type="hidden" name="name" id="name" value="{$MODULE_MODEL->getName()}" />
					<div class="form-group">
						<label class="col-md-4 control-label">{vtranslate('LBL_NAME_FIELD', $QUALIFIED_MODULE)}:</label>
						<div class="col-md-7 form-control-static">{vtranslate($MODULE_MODEL->getName(), $QUALIFIED_MODULE)}</div>
					</div>
					{include file='inventoryTypes/'|cat:{$MODULE_MODEL->getName()}|cat:'.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
			</form>
		</div>
	</div>
</div>
