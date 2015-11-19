{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<table class="table table-striped">
		<thead>
			<tr>
				{foreach item=HEADER from=$HEADERS}
					<th>{vtranslate($HEADER->get('label'), $MODULE)}</th>
				{/foreach}
			</tr>
		</thead>
		<tbody>
			{foreach item=ENTRY from=$ENTRIES name=listview}
				<tr>
					{foreach item=HEADER from=$HEADERS}
						{assign var=HEADERSNAME value=$HEADER->get('name')}
						<td>
							{if $HEADER->getFieldDataType() eq 'double'}
								{decimalFormat($LISTVIEW_ENTRY->get($HEADERSNAME))}
							{else if $HEADER->getFieldDataType() eq 'sharedOwner' || $HEADER->getFieldDataType() eq 'boolean' || $HEADER->getFieldDataType() eq 'tree'}
								{$ENTRY->getDisplayValue($HEADERSNAME)}
							{else}
								{$ENTRY->get($HEADERSNAME)}
							{/if}
						</td>
					{/foreach}
				</tr>
			{/foreach}
		</tbody>
    </table>
{/strip}
