{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="table-responsive tpl-Settings-SocialMedia-Index-Logs">
		<table class="table tableRWD table-bordered table-sm listViewEntriesTable">
			<thead>
			<tr>
				<th class="col-sm-1">{\App\Language::translate('LBL_DATE', $QUALIFIED_MODULE)}</th>
				<th class="col-sm-1">{\App\Language::translate('LBL_TYPE_OF_LOG', $QUALIFIED_MODULE)}</th>
				<th class="col-sm-1">{\App\Language::translate('LBL_SOCIAL_MEDIA_TYPE', $QUALIFIED_MODULE)}</th>
				<th>{\App\Language::translate('LBL_MESSAGE', $QUALIFIED_MODULE)}</th>
			</tr>
			</thead>
			<tbody>
			{foreach from=\App\SocialMedia::getLogs() item=ITEM}
				{if $ITEM['type']==='error'}
					{assign var=TR_CLASS value='table-danger font-weight-bold'}
				{elseif $ITEM['type']==='warning'}
					{assign var=TR_CLASS value='table-warning font-weight-bold'}
				{else}
					{assign var=TR_CLASS value=''}
				{/if}
				<tr class="{$TR_CLASS}">
					<td>{$ITEM['date']}</td>
					<td>{$ITEM['type']}</td>
					<td>{$ITEM['name']}</td>
					<td>{$ITEM['message']}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
{/strip}
