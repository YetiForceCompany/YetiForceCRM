{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-InventoryHeader -->
	{assign var=GROUP_FIELD value=$INVENTORY_MODEL->getField('grouplabel')}
	<div class="table-responsive mb-1 rounded border">
		<table class="table inventoryHeader mb-0 ">
			<thead>
				<tr data-rownumber="0" class="u-min-w-650pxr">
					{if $GROUP_FIELD}
						<th class="text-center u-w-1per-45px">
							<button type="button" title="{\App\Language::translate('LBL_INV_ADD_BLOCK', $MODULE_NAME)}"
								class="btn btn-sm btn-light js-inv-add-group border mb-1 mb-lg-0 text-nowrap"
								data-js="click">
								<span class="fas fa-layer-group"></span>
							</button>
						</th>
					{/if}
					{assign var="ROW_NO" value=0}
					{foreach item=FIELD from=$INVENTORY_MODEL->getFieldsByBlock(0)}
						<th class="{if !$FIELD->isEditable()} d-none {/if}">
							<span class="inventoryLineItemHeader mr-1">{\App\Language::translate($FIELD->getLabel(), $FIELD->getModuleName())}</span>
							{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('EditView',$MODULE_NAME)}
							<div class="input-group-sm">
								{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE_NAME) ITEM_DATA=$INVENTORY_ROW }
							</div>
						</th>
					{/foreach}
				</tr>
			</thead>
		</table>
	</div>
	<!-- /tpl-Base-Edit-InventoryHeader -->
{/strip}
