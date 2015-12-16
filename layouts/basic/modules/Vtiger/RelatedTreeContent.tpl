{strip}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<div class="listViewEntriesDiv contents-bottomscroll">
		<table class="table noStyle">
			<thead>
				<tr class="">
					{foreach item=HEADER from=$RELATED_HEADERS}
						<th nowrap>
							{vtranslate($HEADER, $RELATED_MODULE_NAME)}
						</th>
					{/foreach}
				</tr>
			</thead>
			{foreach item=RECORD from=$RELATED_RECORDS}
				<tr> 
					{foreach item=HEADER key=NAME from=$RELATED_HEADERS}
						<td class="{$WIDTHTYPE}" nowrap>{$RECORD[$NAME]}</td>
					{/foreach}
				</tr>
			{/foreach}
		</table>
	</div>
{/strip}
