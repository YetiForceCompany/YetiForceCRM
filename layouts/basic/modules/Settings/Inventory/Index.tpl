{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" id="view" value="{$VIEW}" />
	<div class="tpl-Settings-Inventory-Index" id="inventory">
		<div class="widget_header form-row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
				{\App\Language::translate($PAGE_LABELS.description,$QUALIFIED_MODULE)}
			</div>
		</div>
		{if $VIEW == 'CreditLimits'}
			{assign var=CURRENCY_BOOL value=true}
			<input type="hidden" id="currency" value='{\App\Json::encode($CURRENCY)}' />
			<div class="alert alert-info mb-2">
				<a href="#" class="close" data-dismiss="alert">&times;</a>
				{\App\Language::translate('LBL_CREDITLIMITS_INFO', $QUALIFIED_MODULE)}
			</div>
		{/if}
		<div class="contents form-row">
			<div class="col-md-12">
				{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
				<div class="mb-2">
					<button type="button" class="btn btn-success addInventory addButton" data-url="{$RECORD_MODEL->getCreateUrl()}" data-type="0"><i class="fas fa-plus"></i>&nbsp;&nbsp;<strong>{\App\Language::translate('LBL_ADD', $QUALIFIED_MODULE)} {\App\Language::translate($PAGE_LABELS.title_single, $QUALIFIED_MODULE)}</strong></button>
				</div>
				<table class="table tableRWD table-bordered inventoryTable themeTableColor">
					<thead>
						<tr class="blockHeader">
							<th class="themeTextColor textAlignCenter {$WIDTHTYPE}"><strong>{\App\Language::translate('LBL_NAME', $QUALIFIED_MODULE)}</strong></th>
							<th class="themeTextColor textAlignCenter {$WIDTHTYPE}"><strong>{\App\Language::translate('LBL_VALUE', $QUALIFIED_MODULE)}</strong></th>
							<th class="themeTextColor textAlignCenter {$WIDTHTYPE}"><strong>{\App\Language::translate('LBL_STATUS', $QUALIFIED_MODULE)}</strong></th>
							<th class="themeTextColor textAlignCenter {$WIDTHTYPE}"><strong>{\App\Language::translate('LBL_DEFAULT', $QUALIFIED_MODULE)}</strong></th>
						</tr>
					</thead>
					<tbody>
						{foreach item=RECORD from=$INVENTORY_DATA}
							<tr class="opacity" data-id="{$RECORD->getId()}">
								<td class="textAlignCenter {$WIDTHTYPE}"><label class="name">{$RECORD->getName()}</label></td>
								<td class="textAlignCenter {$WIDTHTYPE}"><span class="value">{$RECORD->getValue()} {if !$CURRENCY_BOOL}%{else}{$CURRENCY.currency_symbol}{/if}</span></td>
								<td class="textAlignCenter {$WIDTHTYPE}"><input type="checkbox" data-field-name="status" class="status js-update-field" {if !$RECORD->getStatus()}checked{/if} /></td>
								<td class="textAlignCenter {$WIDTHTYPE}"><input type="checkbox" data-field-name="default" class="default js-update-field" {if $RECORD->getDefault()}checked{/if} />
									<div class="float-right actions">
										<button class="btn btn-info btn-sm text-white editInventory u-cursor-pointer" data-url="{$RECORD->getEditUrl()}"><span title="{\App\Language::translate('LBL_EDIT', $MODULE)}" class="fas fa-edit alignBottom"></span></button>&nbsp;
										<button class="removeInventory u-cursor-pointer btn btn-danger btn-sm text-white" data-url="{$RECORD->getEditUrl()}"><span title="{\App\Language::translate('LBL_DELETE', $MODULE)}" class="fas fa-trash-alt alignBottom"></span></button>&nbsp;
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
