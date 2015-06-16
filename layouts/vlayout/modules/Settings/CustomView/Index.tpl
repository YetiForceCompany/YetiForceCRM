{strip}
<div class=" CustomViewList">
	<div class="widget_header row">
		<div class="col-md-10"><h3>{vtranslate($MODULE, $QUALIFIED_MODULE)}</h3>{vtranslate('LBL_MODULE_DESC', $QUALIFIED_MODULE)}</div>
		<div class="col-md-2"></div>
	</div>
	<hr>
	<div class="contents tabbable">
		<table class="table table-striped table-bordered table-condensed listViewEntriesTable">
			<thead>
				<tr class="blockHeader">
					<th><strong>{vtranslate('Module',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{vtranslate('ViewName',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{vtranslate('SetDefault',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{vtranslate('Privileges',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{vtranslate('Actions',$QUALIFIED_MODULE)}</strong></th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$MODULE_MODEL->getCustomViews() item=item key=key}
					<tr data-cvid="{$key}" data-mod="{$item['entitytype']}">
						<td>{vtranslate($item['entitytype'],$item['entitytype'])}</td>
						{if $item['viewname'] eq 'All'}
							<td>{vtranslate('All',$item['entitytype'])}</td>
						{else}
							<td>{$item['viewname']}</td>
						{/if}
						<td><input class="updateField" type="checkbox" name="setdefault" {if $item['cn'] eq 1}readonly{/if} {if $item['setdefault']}checked{/if}></td>
						<td><input class="updateField" type="checkbox" name="privileges" {if $item['privileges']}checked{/if}></td>
						<td>
							<button class="btn btn-primary marginLeftZero btn-sm update" data-cvid="{$key}" data-editurl="{$MODULE_MODEL->GetUrlToEdit($item['entitytype'],$key)}">{vtranslate('Edit',$QUALIFIED_MODULE)}</button>
							&nbsp;&nbsp;
							{if $item['cn'] neq 1}
								<button class="btn btn-danger marginLeftZero delete" data-cvid="{$key}">{vtranslate('Delete',$QUALIFIED_MODULE)}</button>
							{/if}
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	<div class="clearfix"></div>
{/strip}
