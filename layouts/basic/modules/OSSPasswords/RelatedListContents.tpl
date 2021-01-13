{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{include file=\App\Layout::getTemplatePath('ListViewAlphabet.tpl', $RELATED_MODULE_NAME) MODULE_MODEL=$RELATED_MODULE}
	<div class="listViewEntriesDiv u-overflow-scroll-non-desktop contents-bottomscroll">
		<table class="table tableBorderHeadBody listViewEntriesTable {if $VIEW_MODEL && !$VIEW_MODEL->isEmpty('entityState')}listView{$VIEW_MODEL->get('entityState')}{/if}">
			<thead>
			<tr class="listViewHeaders">
				{assign var=COUNT value=0}
				<th class="noWrap">
					<div class="d-flex align-items-center">
						{if $RELATED_MODULE->isAdvSortEnabled()}
							<button type="button"
								class="ml-2 btn btn-info btn-xs js-show-modal"
								data-url="index.php?view=SortOrderModal&fromView={$VIEW}&module={$RELATED_MODULE_NAME}"
								data-modalid="sortOrderModal-{\App\Layout::getUniqueId()}">
								<span class="fas fa-sort"></span>
							</button>
						{/if}
						<div class="js-list-reload" data-js="click">
					</div>
				</th>
				{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
					{if !empty($COLUMNS) && $COUNT == $COLUMNS }
						{break}
					{/if}
					{assign var=COUNT value=$COUNT+1}
					{assign var=HEADER_FIELD_NAME value=$HEADER_FIELD->getFullName()}
					<th {if $HEADER_FIELD@last} colspan="2" {/if} nowrap class="{if isset($ORDER_BY[$HEADER_FIELD_NAME])} columnSorted{/if}">
						{if $HEADER_FIELD->getColumnName() eq 'access_count' or $HEADER_FIELD->getColumnName() eq 'idlists' }
							<a href="javascript:void(0);"
							   class="noSorting">{\App\Language::translate($HEADER_FIELD->getFieldLabel(), $RELATED_MODULE->get('name'))}</a>
						{elseif $HEADER_FIELD->getColumnName() neq 'time_start'}
							<span class="listViewHeaderValues float-left  {if $HEADER_FIELD->isListviewSortable()} js-change-order u-cursor-pointer{/if}"
								data-nextsortorderval="{if isset($ORDER_BY[$HEADER_FIELD_NAME]) && $ORDER_BY[$HEADER_FIELD_NAME] eq \App\Db::ASC}{\App\Db::DESC}{else}{\App\Db::ASC}{/if}"
								data-columnname="{$HEADER_FIELD_NAME}"
								data-js="click">
								{$HEADER_FIELD->getFullLabelTranslation($RELATED_MODULE)}
								{if isset($ORDER_BY[$HEADER_FIELD_NAME])}
									&nbsp;&nbsp;<span class="fas {if $ORDER_BY[$HEADER_FIELD_NAME] eq \App\Db::DESC}fa-chevron-down{else}fa-chevron-up{/if}"></span>
								{/if}
							</span>
						{/if}
					</th>
				{/foreach}
				{if $SHOW_CREATOR_DETAIL}
					<th>{\App\Language::translate('LBL_RELATION_CREATED_TIME', $RELATED_MODULE->get('name'))}</th>
					<th>{\App\Language::translate('LBL_RELATION_CREATED_USER', $RELATED_MODULE->get('name'))}</th>
				{/if}
				{if $SHOW_COMMENT}
					<th>{\App\Language::translate('LBL_RELATION_COMMENT', $RELATED_MODULE->get('name'))}</th>
				{/if}
			</tr>
			</thead>
			<tbody>
			{if $RELATED_MODULE->isQuickSearchEnabled()}
				<tr>
					<td>
						<a class="btn btn-light" role="button" data-trigger="listSearch" href="javascript:void(0);">
							<span class="fas fa-search" title="{\App\Language::translate('LBL_SEARCH')}"></span>
						</a>
					</td>
					{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
						<td>
							{assign var=FIELD_UI_TYPE_MODEL value=$HEADER_FIELD->getUITypeModel()}
							{assign var=HEADER_FIELD_NAME value=$HEADER_FIELD->getName()}
							{if isset($SEARCH_DETAILS[$HEADER_FIELD_NAME])}
								{assign var=SEARCH_INFO value=$SEARCH_DETAILS[$HEADER_FIELD_NAME]}
							{else}
								{assign var=SEARCH_INFO value=[]}
							{/if}
							{include file=\App\Layout::getTemplatePath($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(), $RELATED_MODULE_NAME)
							FIELD_MODEL=$HEADER_FIELD SEARCH_INFO=$SEARCH_INFO USER_MODEL=$USER_MODEL MODULE_MODEL=$RELATED_MODULE MODULE=$RELATED_MODULE_NAME}
						</td>
					{/foreach}
					<td>
						<button type="button" class="btn btn-light removeSearchConditions">
							<span class="fas fa-times" title="{\App\Language::translate('LBL_CLEAR_SEARCH')}"></span>
						</button>
					</td>
				</tr>
			{/if}
			{assign var="RELATED_HEADER_COUNT" value=count($RELATED_HEADERS)}
			{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
				{assign var="RECORD_COLORS" value=$RELATED_RECORD->getListViewColor()}
				<tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}'
						{if $RELATED_RECORD->isViewable()}
					data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'
						{/if}>
					{assign var=COUNT value=0}
					{* create id for possword *}
					{assign var="PASS_ID" value=''}
					{if array_key_exists('password',$RELATED_HEADERS)}
						{assign var=PASS_ID value=$RELATED_RECORD->get('id')}
					{/if}
					<td class="noWrap leftRecordActions listButtons {$WIDTHTYPE}"
						{if $RECORD_COLORS['leftBorder']}style="border-left-color: {$RECORD_COLORS['leftBorder']};"{/if}>
						{include file=\App\Layout::getTemplatePath('RelatedListLeftSide.tpl', $RELATED_MODULE_NAME)}
					</td>
					{foreach item=HEADER_FIELD from=$RELATED_HEADERS name=listHeaderForeach}
						{if !empty($COLUMNS) && $COUNT == $COLUMNS }
							{break}
						{/if}
						{assign var=COUNT value=$COUNT+1}
						{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->getFieldName()}
					<td class="{$WIDTHTYPE}" data-field-type="{$HEADER_FIELD->getFieldDataType()}"
						nowrap {if $RELATED_HEADERNAME eq 'password'} id="pass_{$PASS_ID}"{/if} {if $smarty.foreach.listHeaderForeach.iteration eq $RELATED_HEADER_COUNT}colspan="2"{/if}>
						{if $RELATED_HEADERNAME eq 'password'}
							{str_repeat('*', 10)}
						{elseif ($HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->getUIType() eq '4') && $RELATED_RECORD->isViewable()}
							<a class="modCT_{$RELATED_MODULE_NAME}" title=""
							   href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getListViewDisplayValue($RELATED_HEADERNAME)}</a>
						{elseif $HEADER_FIELD->get('fromOutsideList') eq true}
							{$HEADER_FIELD->getDisplayValue($RELATED_RECORD->get($RELATED_HEADERNAME,$RELATED_RECORD->getId(), $RELATED_RECORD))}
						{else}
							{$RELATED_RECORD->getListViewDisplayValue($RELATED_HEADERNAME)}
						{/if}
						{if $HEADER_FIELD@last}
							</td>
						{/if}
						</td>
					{/foreach}
					{if $SHOW_CREATOR_DETAIL}
						<td class="medium" data-field-type="rel_created_time"
							nowrap>{App\Fields\DateTime::formatToDisplay($RELATED_RECORD->get('rel_created_time'))}</td>
						<td class="medium" data-field-type="rel_created_user"
							nowrap>{\App\Fields\Owner::getLabel($RELATED_RECORD->get('rel_created_user'))}</td>
					{/if}
					{if $SHOW_COMMENT}
						<td class="medium" data-field-type="rel_comment"
							nowrap>{\App\Purifier::encodeHtml($RELATED_RECORD->get('rel_comment'))}</td>
					{/if}
				</tr>
			{/foreach}
			</tbody>
			{if !empty($SHOW_SUMMATION_ROW)}
				<tfoot class="listViewSummation">
					<tr>
						<td></td>
						{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
							<td {if $HEADER_FIELD@last} colspan="2" {/if} class="noWrap {if !empty($HEADER_FIELD->isCalculateField())}border{/if}">
								{if !empty($HEADER_FIELD->isCalculateField())}
									<button class="btn btn-sm btn-light js-popover-tooltip" data-js="popover" type="button" data-operator="sum" data-field="{$HEADER_FIELD->getName()}" data-content="{\App\Language::translate('LBL_CALCULATE_SUM_FOR_THIS_FIELD')}">
										<span class="fas fa-signal"></span>
									</button>
									<span class="calculateValue"></span>
								{/if}
							</td>
						{/foreach}
					</tr>
				</tfoot>
			{/if}
		</table>
	</div>
	{foreach key=index item=jsModel from=$RELATED_SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
{/strip}
