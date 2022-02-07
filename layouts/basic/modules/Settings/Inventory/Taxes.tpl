{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" id="view" value="{$VIEW}" />
	<div class="tpl-Settings-Inventory-Taxes" id="inventory">
		<div class="o-breadcrumb widget_header form-row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="contents form-row">
			<div class="col-md-12">
				<button type="button" class="btn btn-success addInventory my-2"
					data-url="{$RECORD_MODEL->getCreateUrl()}" data-type="0"><span
						class="fas fa-plus mr-1"></span>{\App\Language::translate('LBL_ADD', $QUALIFIED_MODULE)} {\App\Language::translate($PAGE_LABELS.title_single, $QUALIFIED_MODULE)}
				</button>
				<table class="table tableRWD table-bordered inventoryTable themeTableColor">
					<thead>
						<tr class="blockHeader">
							<th class="themeTextColor textAlignCenter {$WIDTHTYPE}">{\App\Language::translate('LBL_NAME', $QUALIFIED_MODULE)}</th>
							<th class="themeTextColor textAlignCenter {$WIDTHTYPE}">{\App\Language::translate('LBL_VALUE', $QUALIFIED_MODULE)}</th>
							<th class="themeTextColor textAlignCenter {$WIDTHTYPE}">{\App\Language::translate('LBL_STATUS', $QUALIFIED_MODULE)}</th>
							<th class="themeTextColor textAlignCenter {$WIDTHTYPE}">{\App\Language::translate('LBL_DEFAULT', $QUALIFIED_MODULE)}</th>
						</tr>
					</thead>
					<tbody>
						{foreach item=RECORD from=$INVENTORY_DATA}
							<tr class="opacity" data-id="{$RECORD->getId()}">
								<td class="textAlignCenter {$WIDTHTYPE}"><label class="name">{$RECORD->getName()}</label>
								</td>
								<td class="textAlignCenter {$WIDTHTYPE}"><span
										class="value">{$RECORD->getValue()} {if empty($CURRENCY_BOOL)}%{else}{$CURRENCY.currency_symbol}{/if}</span>
								</td>
								<td class="textAlignCenter {$WIDTHTYPE}">
									<input type="checkbox" data-field-name="status"
										class="status js-update-field my-2"
										{if !$RECORD->getStatus()}checked="checked" {/if} />
								</td>
								<td class="textAlignCenter {$WIDTHTYPE}">
									<div class="float-right w-50 d-flex justify-content-between mr-2">
										<input type="checkbox" data-field-name="default"
											class="default js-update-field my-2"
											{if $RECORD->getDefault()}checked{/if} />
										<div class="actions">
											<button class="btn btn-info btn-sm text-white editInventory u-cursor-pointer mr-1"
												data-url="{$RECORD->getEditUrl()}"><span
													title="{\App\Language::translate('LBL_EDIT', $MODULE)}"
													class="yfi yfi-full-editing-view alignBottom"></span></button>
											<button class="removeInventory u-cursor-pointer btn btn-danger btn-sm text-white"
												data-url="{$RECORD->getEditUrl()}"><span
													title="{\App\Language::translate('LBL_DELETE', $MODULE)}"
													class="fas fa-trash-alt alignBottom"></span></button>
										</div>
									</div>
								</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
{/strip}
