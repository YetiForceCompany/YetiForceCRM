{strip}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
<div class="listViewEntriesDiv contents-bottomscroll">
	<table class="table table-bordered listViewEntriesTable">
		<thead>
			<tr class="listViewHeaders">
				{assign var=COUNT value=0}
				{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
					{if $COLUMNS != '' && $COUNT == $COLUMNS }
						{break}
					{/if}
					{assign var=COUNT value=$COUNT+1}
					<th {if $HEADER_FIELD@last} colspan="2" {/if} nowrap>
						{if $HEADER_FIELD->get('column') eq 'access_count' or $HEADER_FIELD->get('column') eq 'idlists' }
							<a href="javascript:void(0);" class="noSorting">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}</a>
						{elseif $HEADER_FIELD->get('column') eq 'time_start'}
						{else}
							<a href="javascript:void(0);" class="relatedListHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->get('column')}">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}
								&nbsp;&nbsp;{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}<img class="{$SORT_IMAGE}">{/if}
							</a>
						{/if}
					</th>
				{/foreach}
			</tr>
		</thead>
		{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
			<tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' 
				{if $RELATED_MODULE_NAME eq 'Calendar'}
					{assign var=DETAILVIEWPERMITTED value=isPermitted($RELATED_MODULE->get('name'), 'DetailView', $RELATED_RECORD->getId())}
					{if $DETAILVIEWPERMITTED eq 'yes'}
						data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'
					{/if}
				{else}
					data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'
				{/if}>
				{assign var=COUNT value=0}
				{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
					{if $COLUMNS != '' && $COUNT == $COLUMNS }
						{break}
					{/if}
					{assign var=COUNT value=$COUNT+1}
					{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
					<td class="{$WIDTHTYPE}" data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap>
						{if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
							<a class="moduleColor_{$RELATED_MODULE_NAME}" title="" href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)|truncate:50}</a>
						{elseif $RELATED_HEADERNAME eq 'access_count'}
							{$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
						{elseif $RELATED_HEADERNAME eq 'time_start'}
						{elseif $RELATED_HEADERNAME eq 'listprice' || $RELATED_HEADERNAME eq 'unit_price'}
							{CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
							{if $RELATED_HEADERNAME eq 'listprice'}
								{assign var="LISTPRICE" value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
							{/if}
						{else if $RELATED_HEADERNAME eq 'filename'}
							 {$RELATED_RECORD->get($RELATED_HEADERNAME)}
						{else}
							{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
						{/if}
						{if $HEADER_FIELD@last}
						</td><td nowrap class="{$WIDTHTYPE}">
							<div class="pull-right actions">
								<span class="actionImages">
									{if $RELATED_MODULE_NAME eq 'Calendar'}
										{if $IS_EDITABLE && $RELATED_RECORD->get('taskstatus') neq 'Held' && $RELATED_RECORD->get('taskstatus') neq 'Completed'}
											<a class="markAsHeld"><i title="{vtranslate('LBL_MARK_AS_HELD', $MODULE)}" class="icon-ok alignMiddle"></i></a>&nbsp;
										{/if}
										{if $IS_EDITABLE && $RELATED_RECORD->get('taskstatus') eq 'Held'}
											<a class="holdFollowupOn"><span title="{vtranslate('LBL_HOLD_FOLLOWUP_ON', "Events")}" class="icon-flag alignMiddle"></span></a>&nbsp;
										{/if}
										{if $DETAILVIEWPERMITTED eq 'yes'}
											<a href="{$RELATED_RECORD->getFullDetailViewUrl()}"><span title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="icon-th-list alignMiddle"></span></a>&nbsp;
										{/if}
									{else}
										<a href="{$RELATED_RECORD->getFullDetailViewUrl()}"><span title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="icon-th-list alignMiddle"></span></a>&nbsp;
									{/if}
									{if $IS_EDITABLE}
										{if $RELATED_MODULE_NAME eq 'PriceBooks'}
											<a data-url="index.php?module=PriceBooks&view=ListPriceUpdate&record={$PARENT_RECORD->getId()}&relid={$RELATED_RECORD->getId()}&currentPrice={$LISTPRICE}"
											   class="editListPrice cursorPointer" data-related-recordid='{$RELATED_RECORD->getId()}' data-list-price={$LISTPRICE}>
												<i class="icon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $MODULE)}"></i>
											</a>
										{elseif $RELATED_MODULE_NAME eq 'Calendar'}
											{if isPermitted($RELATED_MODULE->get('name'), 'EditView', $RELATED_RECORD->getId()) eq 'yes'}
												<a href='{$RELATED_RECORD->getEditViewUrl()}'><span title="{vtranslate('LBL_EDIT', $MODULE)}" class="icon-pencil alignMiddle"></span></a>
											{/if}
										{else}
											<a href='{$RELATED_RECORD->getEditViewUrl()}'><span title="{vtranslate('LBL_EDIT', $MODULE)}" class="icon-pencil alignMiddle"></span></a>
										{/if}
									{/if}
									{if $IS_DELETABLE}
										{if $RELATED_MODULE_NAME eq 'Calendar'}
											{if isPermitted($RELATED_MODULE->get('name'), 'Delete', $RELATED_RECORD->getId()) eq 'yes'}
												<a class="relationDelete"><span title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></span></a>
											{/if}
										{else}
											<a class="relationDelete"><span title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></span></a>
										{/if}
									{/if}
								</span>
							</div>
						</td>
					{/if}
					</td>
				{/foreach}
			</tr>
		{/foreach}
	</table>
</div>
{/strip}
