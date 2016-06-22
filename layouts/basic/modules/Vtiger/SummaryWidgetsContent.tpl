{strip}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<div class="listViewEntriesDiv contents-bottomscroll">
		<table class="table noStyle listViewEntriesTable">
			<thead>
				<tr class="">
					{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
						<th {if $HEADER_FIELD@last} colspan="2" {/if} nowrap>
							{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}
						</th>
					{/foreach}
					{if $SHOW_CREATOR_DETAIL}
						<th>{vtranslate('LBL_RELATION_CREATED_TIME', $RELATED_MODULE->get('name'))}</th>
						<th>{vtranslate('LBL_RELATION_CREATED_USER', $RELATED_MODULE->get('name'))}</th>
						{/if}
						{if $SHOW_COMMENT}
						<th>{vtranslate('LBL_RELATION_COMMENT', $RELATED_MODULE->get('name'))}</th>
						{/if}
				</tr>
			</thead>
			{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
				<tr class="listViewEntries" data-id="{$RELATED_RECORD->getId()}"
					{if $RELATED_RECORD->isViewable()}
						data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'
					{/if}
					{if !empty($COLOR_LIST[$RELATED_RECORD->getId()])}
						style="background: {$COLOR_LIST[$RELATED_RECORD->getId()]['background']}; color: {$COLOR_LIST[$RELATED_RECORD->getId()]['text']}"
					{/if}>
					{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
						{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
						<td class="{$WIDTHTYPE}" data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap>
							{if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
								<a class="moduleColor_{$RELATED_MODULE_NAME}" title="{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}" href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)|truncate:50}</a>
							{elseif $RELATED_HEADERNAME eq 'access_count'}
								{$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
							{elseif $RELATED_HEADERNAME eq 'time_start'}
							{elseif $RELATED_HEADERNAME eq 'listprice' || $RELATED_HEADERNAME eq 'unit_price'}
								{CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
								{if $RELATED_HEADERNAME eq 'listprice'}
									{assign var="LISTPRICE" value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
								{/if}
							{else}
								{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
							{/if}
							{if $HEADER_FIELD@last}
							</td><td nowrap class="{$WIDTHTYPE}">
								<div class="pull-right actions">
									<span class="actionImages">
										{if $RELATED_MODULE_NAME eq 'Calendar'}
											{assign var=CURRENT_ACTIVITY_LABELS value=Calendar_Module_Model::getComponentActivityStateLabel('current')}
											{if $IS_EDITABLE && in_array($RELATED_RECORD->get('activitystatus'),$CURRENT_ACTIVITY_LABELS)}
												<a class="showModal" data-url="{$RELATED_RECORD->getActivityStateModalUrl()}">
													<span title="{vtranslate('LBL_SET_RECORD_STATUS', $MODULE)}" class="glyphicon glyphicon-ok alignMiddle"></span>
												</a>&nbsp;
											{/if}
											{if $RELATED_RECORD->isViewable()}
												<a href="{$RELATED_RECORD->getFullDetailViewUrl()}">
													<span title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="glyphicon glyphicon-th-list alignMiddle"></span>
												</a>&nbsp;
											{/if}
										{else}
											<a href="{$RELATED_RECORD->getFullDetailViewUrl()}">
												<span title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="glyphicon glyphicon-th-list alignMiddle"></span>
											</a>&nbsp;
										{/if}
										{if $IS_EDITABLE}
											{if $RELATED_MODULE_NAME eq 'PriceBooks'}
												<a data-url="index.php?module=PriceBooks&view=ListPriceUpdate&record={$PARENT_RECORD->getId()}&relid={$RELATED_RECORD->getId()}&currentPrice={$LISTPRICE}"
												   class="editListPrice cursorPointer" data-related-recordid='{$RELATED_RECORD->getId()}' data-list-price={$LISTPRICE}>
													<span class="glyphicon glyphicon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $MODULE)}"></span>
												</a>
											{elseif $RELATED_RECORD->isEditable()}
												<a href='{$RELATED_RECORD->getEditViewUrl()}'>
													<span title="{vtranslate('LBL_EDIT', $MODULE)}" class="glyphicon glyphicon-pencil alignMiddle"></span>
												</a>
											{/if}
										{/if}
										{if $IS_DELETABLE}
											{if $RELATED_MODULE_NAME eq 'Calendar'}
												{if $RELATED_RECORD->isDeletable()}
													<a class="relationDelete">
														<span title="{vtranslate('LBL_DELETE', $MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span>
													</a>
												{/if}
											{elseif $RELATED_RECORD->isViewable()}
												<a class="relationDelete">
													<span title="{vtranslate('LBL_DELETE', $MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span>
												</a>
											{/if}
										{/if}
									</span>
								</div>
							</td>
						{/if}
						</td>
					{/foreach}
					{if $SHOW_CREATOR_DETAIL}
						<td class="{$WIDTHTYPE}" data-field-type="rel_created_time" nowrap>{$RELATED_RECORD->get('relCreatedTime')}</td>
						<td class="{$WIDTHTYPE}" data-field-type="rel_created_user" nowrap>{$RELATED_RECORD->get('relCreatedUser')}</td>
					{/if}
					{if $SHOW_COMMENT}
						<td class="{$WIDTHTYPE}" data-field-type="rel_comment" nowrap>
							{if $RELATED_RECORD->has('relCommentFull')}
								<a class="popoverTooltip" data-placement="top" data-content="{$RELATED_RECORD->get('relCommentFull')}">{$RELATED_RECORD->get('relComment')}</a>
							{else}	
								{$RELATED_RECORD->get('relComment')}
							{/if}
							<span class="actionImages">
								<a class="showModal" data-url="index.php?module={$PARENT_RECORD->getModuleName()}&view=RelatedCommentModal&record={$PARENT_RECORD->getId()}&relid={$RELATED_RECORD->getId()}&relmodule={$RELATED_MODULE->get('name')}">
									<span class="glyphicon glyphicon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $MODULE)}"></span>
								</a>
							</span>
						</td>
					{/if}
				</tr>
			{/foreach}
		</table>
	</div>
{/strip}
