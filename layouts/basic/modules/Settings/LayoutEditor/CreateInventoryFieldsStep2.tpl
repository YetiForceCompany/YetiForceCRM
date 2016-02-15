{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					{if is_array($FIELD_INSTANCE)}
						{assign var='FIELD_INSTANCE' value=current($FIELD_INSTANCE)}
						<h4 class="modal-title">{vtranslate('LBL_EDITING_INVENTORY_FIELD', $QUALIFIED_MODULE)}</h4>
					{else}
						<h4 class="modal-title">{vtranslate('LBL_CREATING_INVENTORY_FIELD', $QUALIFIED_MODULE)}</h4>
					{/if}
				</div>
				<form class="form-horizontal">
					<div class="modal-body">
						<input type="hidden" name="id" id="id" value="{$ID}" />
						<input type="hidden" name="name" id="name" value="{$FIELD_INSTANCE->getName()}" />
						<div class="form-group">
							<label class="col-md-4 control-label">{vtranslate('LBL_NAME_FIELD', $QUALIFIED_MODULE)}:</label>
							<div class="col-md-7 form-control-static">{vtranslate($FIELD_INSTANCE->getName(), $QUALIFIED_MODULE)}</div>
						</div>
						{include file='inventoryTypes/'|cat:{$FIELD_INSTANCE->getName()}|cat:'.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
					</div>
					{include file='ModalFooter.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
				</form>
			</div>
		</div>
	</div>
{/strip}
