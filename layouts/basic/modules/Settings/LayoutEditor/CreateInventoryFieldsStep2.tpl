{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-CreateInventoryFieldsStep2 -->
	<div class="modal fade" tabindex="-1" data-js="container">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><span class="modal-header-icon yfi yfi-full-editing-view mr-1"></span>
						{if $FIELD_INSTANCE->get('id')}
							{\App\Language::translate('LBL_EDITING_INVENTORY_FIELD', $QUALIFIED_MODULE)}
						{else}
							{\App\Language::translate('LBL_CREATING_INVENTORY_FIELD', $QUALIFIED_MODULE)}
						{/if}
					</h5>
					<button type="button" class="close" data-dismiss="modal"
						title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form class="form-horizontal">
					<div class="modal-body">
						<input type="hidden" name="record" id="id" value="{$FIELD_INSTANCE->getId()}" />
						<input type="hidden" name="type" value="{$FIELD_INSTANCE->getType()}" />
						<input type="hidden" name="sourceModule" value="{$INVENTORY_MODEL->getModuleName()}" />
						<input type="hidden" name="columnName" id="columnName" value="{$FIELD_INSTANCE->getColumnName()}" />
						{include file=\App\Layout::getTemplatePath($FIELD_INSTANCE->getEditTemplateName(), $QUALIFIED_MODULE)}
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-LayoutEditor-CreateInventoryFieldsStep2 -->
{/strip}
