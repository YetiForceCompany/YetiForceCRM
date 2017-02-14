{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<table class="table table-striped">
		<thead>
			<tr>
				{foreach item=HEADER from=$HEADERS}
					<th>
						{vtranslate($HEADER->get('label'), $MODULE)}
					</th>
				{/foreach}
			</tr>
		</thead>
		<tbody>
			{foreach item=ENTRY from=$ENTRIES name=listview}
				<tr>
					{foreach item=HEADER from=$HEADERS}
						{assign var=HEADERNAME value=$HEADER->get('name')}
						<td>
							{if $HEADER->isNameField() eq true}
								<a {if $HEADER->isNameField() eq true}class="moduleColor_{$MODULE}"{/if} href="{$ENTRY->getDetailViewUrl()}">
									{$ENTRY->getListViewDisplayValue($HEADERNAME)}
								</a>
							{else}
								{$ENTRY->getListViewDisplayValue($HEADERNAME)}
							{/if}
						</td>
					{/foreach}
				</tr>
			{/foreach}
		</tbody>
	</table>
{/strip}
