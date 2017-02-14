{strip}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<div class="listViewEntriesDiv contents-bottomscroll relatedContents">
		<table class="table noStyle listViewEntriesTable">
			<thead>
				<tr class="">
					<th class="noWrap listViewSearchTd">&nbsp;</th>
					{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
						<th nowrap>
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
					<td class="{$WIDTHTYPE} noWrap leftRecordActions">
						{include file=vtemplate_path('RelatedListLeftSide.tpl',$RELATED_MODULE_NAME)}
					</td>
					{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
						{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
						<td class="{$WIDTHTYPE}" data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap>
							{if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
								<a class="moduleColor_{$RELATED_MODULE_NAME}" title="{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}" href="{$RELATED_RECORD->getDetailViewUrl()}">
									{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)|truncate:50}
								</a>
							{else}
								{$RELATED_RECORD->getListViewDisplayValue($RELATED_HEADERNAME)}
							{/if}
						</td>
					{/foreach}
					{if $SHOW_CREATOR_DETAIL}
						<td class="{$WIDTHTYPE}" data-field-type="rel_created_time" nowrap>{Vtiger_Datetime_UIType::getDisplayDateTimeValue($RELATED_RECORD->get('rel_created_time'))}</td>
						<td class="{$WIDTHTYPE}" data-field-type="rel_created_user" nowrap>{\App\Fields\Owner::getLabel($RELATED_RECORD->get('rel_created_user'))}</td>
					{/if}
					{if $SHOW_COMMENT}
						<td class="{$WIDTHTYPE}" data-field-type="rel_comment" nowrap>
							{if strlen($RELATED_RECORD->get('rel_comment')) > AppConfig::relation('COMMENT_MAX_LENGTH')}
								<a class="popoverTooltip" data-placement="top" data-content="{$RELATED_RECORD->get('rel_comment')}">
									{vtlib\Functions::textLength($RELATED_RECORD->get('rel_comment'), AppConfig::relation('COMMENT_MAX_LENGTH'))}
								</a>
							{else}	
								{$RELATED_RECORD->get('rel_comment')}
							{/if}&nbsp;&nbsp;
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
