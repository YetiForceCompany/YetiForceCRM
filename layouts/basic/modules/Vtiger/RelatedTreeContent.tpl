{strip}
	{if count($RELATED_RECORDS) > 0}
		<div class="listViewEntriesDiv u-overflow-scroll-non-desktop contents-bottomscroll">
			<table class="table noStyle">
				<thead>
					<tr>
						{foreach item=HEADER from=$RELATED_HEADERS}
							<th nowrap class="p-1 text-center">
								{\App\Language::translate($HEADER, $RELATED_MODULE_NAME)}
							</th>
						{/foreach}
						{if $SHOW_CREATOR_DETAIL}
							<th class="p-1 text-center">
								{\App\Language::translate('LBL_RELATION_CREATED_TIME', $RELATED_MODULE_NAME)}
							</th>
							<th class="p-1 text-center">
								{\App\Language::translate('LBL_RELATION_CREATED_USER', $RELATED_MODULE_NAME)}
							</th>
						{/if}
						{if $SHOW_COMMENT}
							<th class="p-1 text-center">
								{\App\Language::translate('LBL_RELATION_COMMENT', $RELATED_MODULE_NAME)}
							</th>
						{/if}
					</tr>
				</thead>
				{foreach item=RECORD from=$RELATED_RECORDS}
					<tr class="listViewEntries">
						{foreach item=HEADER key=NAME from=$RELATED_HEADERS}
							<td class="{$WIDTHTYPE} text-center" nowrap>{$RECORD[$NAME]}</td>
						{/foreach}
						{if $SHOW_CREATOR_DETAIL}
							<td class="{$WIDTHTYPE} text-center" data-field-type="rel_created_time" nowrap>{$RECORD['rel_created_time']}</td>
							<td class="{$WIDTHTYPE} text-center" data-field-type="rel_created_user" nowrap>{$RECORD['rel_created_user']}</td>
						{/if}
						{if $SHOW_COMMENT}
							<td class="{$WIDTHTYPE} text-center" data-field-type="rel_comment" nowrap>
								{if strlen($RECORD['rel_comment']) > App\Config::relation('COMMENT_MAX_LENGTH')}
									<a class="js-popover-tooltip" data-js="popover" data-placement="top" data-content="{$RECORD['rel_comment']}">
										{App\TextUtils::textTruncate($RECORD['rel_comment'], App\Config::relation('COMMENT_MAX_LENGTH'))}
									</a>
								{else}
									{$RECORD['rel_comment']}
								{/if}&nbsp;&nbsp;
								<span class="actionImages">
									<a class="showModal" data-url="index.php?module={$MODULE}&view=RelatedCommentModal&record={$RECORDID}&relid={$RECORD['id']}&relmodule={$RELATED_MODULE_NAME}">
										<span class="yfi yfi-full-editing-view" title="{\App\Language::translate('LBL_EDIT', $MODULE)}"></span>
									</a>
								</span>
							</td>
						{/if}
					</tr>
				{/foreach}
			</table>
		</div>
	{else}
		<div class="summaryWidgetContainer js-no-comments-msg-container">
			<p class="textAlignCenter">{\App\Language::translate('LBL_NO_RECORDS_FOUND',$MODULE_NAME)}</p>
		</div>
	{/if}
{/strip}
