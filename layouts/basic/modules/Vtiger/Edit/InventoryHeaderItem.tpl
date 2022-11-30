{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-InventoryHeaderItem -->
	{assign var=GROUP_FIELD value=$INVENTORY_MODEL->getField('grouplabel')}
	{if $GROUP_FIELD}
		<tr class="inventoryRowGroup">
			<td class="u-white-space-nowrap u-w-1per-45px p-1">
				{if $INVENTORY_MODEL->isField('seq')}
					<a class="dragHandle mx-1 mr-2">
						<img src="{\App\Layout::getImagePath('drag.png')}" alt="{\App\Language::translate('LBL_DRAG', $MODULE_NAME)}" />
					</a>
				{/if}
				<button type="button" class="btn btn-sm btn-danger fas fa-trash-alt js-delete-header-item" title="{\App\Language::translate('LBL_DELETE',$MODULE_NAME)}"></button>
				<button type="button" class="btn btn-sm btn-light ml-1 js-toggle-icon__container js-inv-group-collapse-btn{if $GROUP_FIELD->isOpened()} js-inv-group-collapse-btn__active{/if}" data-js="click">
					<span class="js-toggle-icon fa-fw fas {if $GROUP_FIELD->isOpened()}fa-angle-down{else}fa-angle-right{/if}" data-active="fa-angle-down" data-inactive="fa-angle-right" data-js="click"></span>
				</button>
			</td>
			{foreach item=FIELD from=$INVENTORY_MODEL->getFieldsByBlock(1)}
				<td {if !$FIELD->isEditable()}colspan="0" {/if} class="{if !$FIELD->isEditable()} d-none{/if} text-right">
					{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('GroupHeaders/',$MODULE_NAME)}
					{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE_NAME) FIELD=$FIELD}
				</td>
			{/foreach}
		</tr>
	{/if}
	<!-- /tpl-Base-Edit-InventoryHeaderItem -->
{/strip}
