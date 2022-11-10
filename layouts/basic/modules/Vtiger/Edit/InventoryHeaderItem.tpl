{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-InventoryHeaderItem -->
	{assign var=GROUP_FIELD value=$INVENTORY_MODEL->getField('grouplabel')}
	{if $GROUP_FIELD}
		<tr class="inventoryRowGroup">
			{assign var=COL_SPAN value=count($INVENTORY_MODEL->getFieldsByBlock(1))}
			<td class="u-white-space-nowrap u-w-1per-45px p-1">
				{if $INVENTORY_MODEL->isField('seq')}
					<a class="dragHandle mx-1 mr-2">
						<img src="{\App\Layout::getImagePath('drag.png')}" alt="{\App\Language::translate('LBL_DRAG', $MODULE_NAME)}" />
					</a>
				{/if}
				<button type="button" class="btn btn-sm btn-danger fas fa-trash-alt js-delete-header-item" title="{\App\Language::translate('LBL_DELETE',$MODULE_NAME)}"></button>
				<button type="button" class="btn btn-sm btn-light ml-1 js-toggle-icon__container js-inv-group-collapse-btn" data-js="click">
					<span class="js-toggle-icon fa-fw fas {if $GROUP_FIELD->isOpened()}fa-angle-down{else}fa-angle-right{/if}" data-active="fa-angle-down" data-inactive="fa-angle-right" data-js="click"></span>
				</button>
			</td>
			<td class="p-1" colspan="{$COL_SPAN}">
				<div class="{if !$GROUP_FIELD->isReadOnly()}u-max-w-250px{/if} fieldValue">
					{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$GROUP_FIELD->getTemplateName('EditView',$MODULE_NAME)}
					{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE_NAME) FIELD=$GROUP_FIELD}
				</div>
			</td>
		</tr>
	{/if}
	<!-- /tpl-Base-Edit-InventoryHeaderItem -->
{/strip}
