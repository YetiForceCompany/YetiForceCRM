{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
<input type="hidden" id="view" value="{$VIEW}" />
<div class="" id="inventory">
	<div class="widget_header row">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{vtranslate($PAGE_LABELS.description,$QUALIFIED_MODULE)}
		</div>
	</div>
	{if $VIEW == 'CreditLimits'}
		{assign var=CURRENCY_BOOL value=true}
		<input type="hidden" id="currency" value='{\App\Json::encode($CURRENCY)}' />
		<div class="alert alert-info fade in marginBottom5">
			<a href="#" class="close" data-dismiss="alert">&times;</a>
			{vtranslate('LBL_CREDITLIMITS_INFO', $QUALIFIED_MODULE)}
		</div>
	{/if}
	<div class="contents row">
		<div class="col-md-12">
			{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
			<div class="marginBottom10px">
				<button type="button" class="btn btn-success addInventory addButton" data-url="{$RECORD_MODEL->getCreateUrl()}" data-type="0"><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp;<strong>{vtranslate('LBL_ADD', $QUALIFIED_MODULE)} {vtranslate($PAGE_LABELS.title_single, $QUALIFIED_MODULE)}</strong></button>
			</div>
			<table class="table tableRWD table-bordered inventoryTable themeTableColor">
				<thead>
					<tr class="blockHeader">
						<th class="themeTextColor textAlignCenter {$WIDTHTYPE}"><strong>{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}</strong></th>
						<th class="themeTextColor textAlignCenter {$WIDTHTYPE}"><strong>{vtranslate('LBL_VALUE', $QUALIFIED_MODULE)}</strong></th>
						<th class="themeTextColor textAlignCenter {$WIDTHTYPE}"><strong>{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}</strong></th>
					</tr>
				</thead>
				<tbody>
					{foreach item=RECORD from=$INVENTORY_DATA}
						<tr class="opacity" data-id="{$RECORD->getId()}">
							<td class="textAlignCenter {$WIDTHTYPE}"><label class="name">{$RECORD->getName()}</label></td>
							<td class="textAlignCenter {$WIDTHTYPE}"><span class="value">{$RECORD->getValue()} {if !$CURRENCY_BOOL}%{else}{$CURRENCY.currency_symbol}{/if}</span></td>
							<td class="textAlignCenter {$WIDTHTYPE}"><input type="checkbox" class="status" {if !$RECORD->getStatus()}checked{/if} />
								<div class="pull-right actions">
									<a class="editInventory cursorPointer" data-url="{$RECORD->getEditUrl()}"><span title="{vtranslate('LBL_EDIT', $MODULE)}" class="glyphicon glyphicon-pencil alignBottom"></span></a>&nbsp;
									<a class="removeInventory cursorPointer" data-url="{$RECORD->getEditUrl()}"><span title="{vtranslate('LBL_DELETE', $MODULE)}" class="glyphicon glyphicon-trash alignBottom"></span></a>&nbsp;
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
