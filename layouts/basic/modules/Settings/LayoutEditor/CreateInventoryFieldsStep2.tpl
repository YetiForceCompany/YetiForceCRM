{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- /tpl-Settings-LayoutEditor-CreateInventoryFieldsStep2 -->
	<div class="tpl-Settings-LayoutEditor-CreateInventoryFieldsStep2 modal fade" tabindex="-1" data-js="container">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					{if $FIELD_INSTANCE->get('id')}
						<h5 class="modal-title">{\App\Language::translate('LBL_EDITING_INVENTORY_FIELD', $QUALIFIED_MODULE)}</h5>
					{else}
						<h5 class="modal-title">{\App\Language::translate('LBL_CREATING_INVENTORY_FIELD', $QUALIFIED_MODULE)}</h5>
					{/if}
					<button type="button" class="close" data-dismiss="modal"
						title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form class="form-horizontal">
					<div class="modal-body">
						<input type="hidden" name="id" id="id" value="{$FIELD_INSTANCE->get('id')}" />
						<input type="hidden" name="type" value="{$FIELD_INSTANCE->getType()}" />
						<input type="hidden" name="columnName" id="columnName" value="{$FIELD_INSTANCE->getColumnName()}" />
						<div class="form-group row align-items-center">
							<div class="col-md-4 col-form-label text-right">
								{\App\Language::translate('LBL_NAME_FIELD', $QUALIFIED_MODULE)}:
							</div>
							<div class="col-md-7 col-form-label">
								<b>{\App\Language::translate($FIELD_INSTANCE->getType(), $QUALIFIED_MODULE)}</b>
							</div>
						</div>
						{include file=\App\Layout::getTemplatePath($FIELD_INSTANCE->getEditTemplateName(), $QUALIFIED_MODULE)}
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-LayoutEditor-CreateInventoryFieldsStep2 -->
{/strip}
