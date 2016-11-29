{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{include file=vtemplate_path('ListViewAlphabet.tpl',$RELATED_MODULE_NAME) MODULE_MODEL=$RELATED_MODULE}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<div class="listViewEntriesDiv contents-bottomscroll">
		<table class="table table-bordered listViewEntriesTable">
			<thead>
				<tr class="listViewHeaders">
					{assign var=COUNT value=0}
					<th class="noWrap"></th>
					{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
							{if !empty($COLUMNS) && $COUNT == $COLUMNS }
								{break}
							{/if}
							{assign var=COUNT value=$COUNT+1}
						<th {if $HEADER_FIELD@last} colspan="2" {/if} nowrap>
							{if $HEADER_FIELD->get('column') eq 'access_count' or $HEADER_FIELD->get('column') eq 'idlists' }
								<a href="javascript:void(0);" class="noSorting">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}</a>
							{elseif $HEADER_FIELD->get('column') eq 'time_start'}
							{else}
								<a href="javascript:void(0);" class="relatedListHeaderValues" {if $HEADER_FIELD->isListviewSortable()}data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}"{/if} data-fieldname="{$HEADER_FIELD->get('column')}">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}
									&nbsp;&nbsp;{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}<span class="{$SORT_IMAGE}"></span>{/if}
								</a>
							{/if}
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
			{if $RELATED_MODULE->isQuickSearchEnabled()}
				<tr>
					<td class="listViewSearchTd">
						<a class="btn btn-default" data-trigger="listSearch" href="javascript:void(0);"><span class="glyphicon glyphicon-search"></span></a>
					</td>
					{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
						<td>
							{assign var=FIELD_UI_TYPE_MODEL value=$HEADER_FIELD->getUITypeModel()}
							{if isset($SEARCH_DETAILS[$HEADER_FIELD->getName()])}
								{assign var=SEARCH_INFO value=$SEARCH_DETAILS[$HEADER_FIELD->getName()]}
							{else}
								{assign var=SEARCH_INFO value=[]}
							{/if}
							{include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$RELATED_MODULE_NAME)
				FIELD_MODEL=$HEADER_FIELD SEARCH_INFO=$SEARCH_INFO USER_MODEL=$USER_MODEL MODULE_MODEL=$RELATED_MODULE MODULE=$RELATED_MODULE_NAME}
						</td>
					{/foreach}
					<td>
						<button type="button" class="btn btn-default removeSearchConditions">
							<span class="glyphicon glyphicon-remove"></button>
						</a>
					</td>
				</tr>
			{/if}
			{assign var="RELATED_HEADER_COUNT" value=count($RELATED_HEADERS)}
			{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
				<tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' 
					{if $RELATED_RECORD->isViewable()}
						data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'
					{/if}
					{if !empty($COLOR_LIST[$RELATED_RECORD->getId()])}
						style="background: {$COLOR_LIST[$RELATED_RECORD->getId()]['background']}; color: {$COLOR_LIST[$RELATED_RECORD->getId()]['text']}"
					{/if}>
					{assign var=COUNT value=0}
					{* create id for possword *}
					{if array_key_exists('password',$RELATED_HEADERS)} 
						{assign var=PASS_ID value=$RELATED_RECORD->get('id')}
					{/if}
					<td class="{$WIDTHTYPE} noWrap leftRecordActions">
						{include file=vtemplate_path('RelatedListLeftSide.tpl',$RELATED_MODULE_NAME)}
					</td>
					{foreach item=HEADER_FIELD from=$RELATED_HEADERS name=listHeaderForeach}
						{if !empty($COLUMNS) && $COUNT == $COLUMNS }
							{break}
						{/if}
						{assign var=COUNT value=$COUNT+1}
						{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
						<td class="{$WIDTHTYPE}" data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap {if $RELATED_HEADERNAME eq 'password'} id="{$PASS_ID}"{/if} {if $smarty.foreach.listHeaderForeach.iteration eq $RELATED_HEADER_COUNT}colspan="2"{/if}>
							{if $RELATED_HEADERNAME eq 'password'}
								{str_repeat('*', 10)}
							{elseif $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
								<a class="moduleColor_{$RELATED_MODULE_NAME}" title="" href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)|truncate:50}</a>
							{elseif $HEADER_FIELD->fromOutsideList eq true}
								{$HEADER_FIELD->getDisplayValue($RELATED_RECORD->get($RELATED_HEADERNAME))}
							{else}
								{$RELATED_RECORD->getListViewDisplayValue($RELATED_HEADERNAME)}
							{/if}
							{if $HEADER_FIELD@last}
							</td>
						{/if}
						</td>
					{/foreach}
					{if $SHOW_CREATOR_DETAIL}
						<td class="medium" data-field-type="rel_created_time" nowrap>{Vtiger_Datetime_UIType::getDisplayDateTimeValue($RELATED_RECORD->get('rel_created_time'))}</td>
						<td class="medium" data-field-type="rel_created_user" nowrap>{\App\Fields\Owner::getLabel($RELATED_RECORD->get('rel_created_user'))}</td>
					{/if}
					{if $SHOW_COMMENT}
						<td class="medium" data-field-type="rel_comment" nowrap>{$RELATED_RECORD->get('rel_comment')}</td>
					{/if}
				</tr>
			{/foreach}
		</table>
	</div>
	{foreach key=index item=jsModel from=$RELATED_SCRIPTS}
		<script type="{$jsModel->getType()}" src="{vresource_url($jsModel->getSrc())}"></script>
	{/foreach}
{/strip}
