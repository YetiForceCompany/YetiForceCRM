{strip}
	{if count($RELATED_RECORDS) > 0}
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		<div class="listViewEntriesDiv contents-bottomscroll">
			<table class="table noStyle">
				<thead>
					<tr>
						{foreach item=HEADER from=$RELATED_HEADERS}
							<th nowrap>
								{vtranslate($HEADER, $RELATED_MODULE_NAME)}
							</th>
						{/foreach}
						{if $SHOW_CREATOR_DETAIL}
							<th>
								{vtranslate('LBL_RELATION_CREATED_TIME', $RELATED_MODULE_NAME)}
							</th>
							<th>
								{vtranslate('LBL_RELATION_CREATED_USER', $RELATED_MODULE_NAME)}
							</th>
						{/if}
						{if $SHOW_COMMENT}
							<th>
								{vtranslate('LBL_RELATION_COMMENT', $RELATED_MODULE_NAME)}
							</th>
						{/if}
					</tr>
				</thead>
				{foreach item=RECORD from=$RELATED_RECORDS}
					<tr class="listViewEntries"> 
						{foreach item=HEADER key=NAME from=$RELATED_HEADERS}
							<td class="{$WIDTHTYPE}" nowrap>{$RECORD[$NAME]}</td>
						{/foreach}
						{if $SHOW_CREATOR_DETAIL}
							<td class="{$WIDTHTYPE}" data-field-type="rel_created_time" nowrap>{$RECORD['rel_created_time']}</td>
							<td class="{$WIDTHTYPE}" data-field-type="rel_created_user" nowrap>{$RECORD['rel_created_user']}</td>
						{/if}
						{if $SHOW_COMMENT}
							<td class="{$WIDTHTYPE}" data-field-type="rel_comment" nowrap>
								{if strlen($RECORD['rel_comment']) > AppConfig::relation('COMMENT_MAX_LENGTH')}
									<a class="popoverTooltip" data-placement="top" data-content="{$RECORD['rel_comment']}">
										{vtlib\Functions::textLength($RECORD['rel_comment'], AppConfig::relation('COMMENT_MAX_LENGTH'))}
									</a>
								{else}	
									{$RECORD['rel_comment']}
								{/if}&nbsp;&nbsp;
								<span class="actionImages">
									<a class="showModal" data-url="index.php?module={$MODULE}&view=RelatedCommentModal&record={$RECORDID}&relid={$RECORD['id']}&relmodule={$RELATED_MODULE_NAME}">
										<span class="glyphicon glyphicon-pencil alignMiddle" title="{vtranslate('LBL_EDIT', $MODULE)}"></span>
									</a>
								</span>
							</td>
						{/if}
					</tr>
				{/foreach}
			</table>
		</div>
	{/if}
{/strip}
