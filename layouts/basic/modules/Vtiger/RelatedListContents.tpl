{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{include file=\App\Layout::getTemplatePath('ListViewAlphabet.tpl', $RELATED_MODULE_NAME) MODULE_MODEL=$RELATED_MODULE}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	{assign var=IS_INVENTORY value=($RELATED_VIEW === 'List' && !empty($INVENTORY_MODULE) && !empty($INVENTORY_FIELDS))}
	<div class="listViewEntriesDiv u-overflow-scroll-xsm-down">
		<table class="table tableBorderHeadBody listViewEntriesTable {if $VIEW_MODEL && !$VIEW_MODEL->isEmpty('entityState')}listView{$VIEW_MODEL->get('entityState')}{/if}">
			<thead>
			<tr class="listViewHeaders">
				{assign var=COUNT value=0}
				<th class="noWrap"></th>
				{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
					{if !empty($COLUMNS) && $COUNT == $COLUMNS }
						{break}
					{/if}
					{assign var=COUNT value=$COUNT+1}
					<th {if $HEADER_FIELD@last} colspan="2"{/if} nowrap>
						{if $HEADER_FIELD->getColumnName() eq 'access_count' or $HEADER_FIELD->getColumnName() eq 'idlists' }
							<a href="javascript:void(0);"
							   class="noSorting">{\App\Language::translate($HEADER_FIELD->getFieldLabel(), $RELATED_MODULE->get('name'))}</a>
						{else}
							<a href="javascript:void(0);" class="relatedListHeaderValues"
							   {if $HEADER_FIELD->isListviewSortable()}data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->getColumnName()}{$NEXT_SORT_ORDER}{else}ASC{/if}"{/if}
							   data-fieldname="{$HEADER_FIELD->getColumnName()}">{\App\Language::translate($HEADER_FIELD->getFieldLabel(), $RELATED_MODULE->get('name'))}
								&nbsp;&nbsp;{if $COLUMN_NAME eq $HEADER_FIELD->getColumnName()}<span
								class="{$SORT_IMAGE}"></span>{/if}
							</a>
						{/if}
					</th>
				{/foreach}
				{assign var=ADDITIONAL_TD value=0}
				{if $SHOW_CREATOR_DETAIL}
					{assign var=ADDITIONAL_TD value=$ADDITIONAL_TD + 2}
					<th>
						{\App\Language::translate('LBL_RELATION_CREATED_TIME', $RELATED_MODULE->get('name'))}
					</th>
					<th>
						{\App\Language::translate('LBL_RELATION_CREATED_USER', $RELATED_MODULE->get('name'))}
					</th>
				{/if}
				{if $SHOW_COMMENT}
					{assign var=ADDITIONAL_TD value=$ADDITIONAL_TD + 1}
					<th>
						{\App\Language::translate('LBL_RELATION_COMMENT', $RELATED_MODULE->get('name'))}
					</th>
				{/if}
			</tr>
			</thead>
			<tbody>
			{if $RELATED_MODULE->isQuickSearchEnabled()}
				<tr>
					<td class="listViewSearchTd">
						<div class="flexWrapper">
							<a class="btn btn-light" role="button" data-trigger="listSearch" href="javascript:void(0);">
								<span class="fas fa-search" title="{\App\Language::translate('LBL_SEARCH')}"></span>
							</a>
							<button type="button" class="btn btn-light removeSearchConditions">
								<span class="fas fa-times"
									  title="{\App\Language::translate('LBL_CLEAR_SEARCH')}"></span>
							</button>
						</div>
					</td>
					{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
						<td>
							{assign var=FIELD_UI_TYPE_MODEL value=$HEADER_FIELD->getUITypeModel()}
							{assign var=ARRAY_ELEMENT value=$HEADER_FIELD->getName()}
							{if isset($SEARCH_DETAILS[$ARRAY_ELEMENT])}
								{assign var=SEARCH_INFO value=$SEARCH_DETAILS[$ARRAY_ELEMENT]}
							{else}
								{assign var=SEARCH_INFO value=[]}
							{/if}
							{include file=\App\Layout::getTemplatePath($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(), $RELATED_MODULE_NAME)
							FIELD_MODEL=$HEADER_FIELD SEARCH_INFO=$SEARCH_INFO USER_MODEL=$USER_MODEL MODULE_MODEL=$RELATED_MODULE MODULE=$RELATED_MODULE_NAME}
						</td>
					{/foreach}
					<td class="reducePadding" colspan="{$ADDITIONAL_TD + 1}"></td>
				</tr>
			{/if}
			{assign var="RELATED_HEADER_COUNT" value=count($RELATED_HEADERS)}
			{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
				{assign var="RECORD_COLORS" value=$RELATED_RECORD->getListViewColor()}
				<tr class="listViewEntries js-list__row" data-js="each" data-id='{$RELATED_RECORD->getId()}'
						{if $RELATED_RECORD->isViewable()}
					data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'
						{/if}>
					{assign var=COUNT value=0}
					<td class="{$WIDTHTYPE} noWrap leftRecordActions"
						{if $RECORD_COLORS['leftBorder']}style="border-left-color: {$RECORD_COLORS['leftBorder']};"{/if}>
						{include file=\App\Layout::getTemplatePath('RelatedListLeftSide.tpl', $RELATED_MODULE_NAME)}
					</td>
					{foreach item=HEADER_FIELD from=$RELATED_HEADERS name=listHeaderForeach}
						{if !empty($COLUMNS) && $COUNT == $COLUMNS }
							{break}
						{/if}
						{assign var=COUNT value=$COUNT+1}
						{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->getFieldName()}
					<td class="{$WIDTHTYPE}" data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap
						{if $smarty.foreach.listHeaderForeach.iteration eq $RELATED_HEADER_COUNT}colspan="2"{/if}>
						{if ($HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->getUIType() eq '4') && $RELATED_RECORD->isViewable()}
							<a class="modCT_{$RELATED_MODULE_NAME} js-list__field" data-js="width" title=""
							   href="{$RELATED_RECORD->getDetailViewUrl()}">
								{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)|truncate:50}
							</a>
						{elseif $HEADER_FIELD->get('fromOutsideList') eq true}
							{if $HEADER_FIELD->get('isEditable')}
								<input name="{$RELATED_HEADERNAME}"
									   class="form-control form-control-sm js-edit-{$RELATED_HEADERNAME} {$HEADER_FIELD->get('class')}"
									   title="{App\Language::translate($HEADER_FIELD->getFieldLabel(), $RELATED_MODULE_NAME)}"
									   data-fieldinfo="{\App\Purifier::encodeHtml(\App\Json::encode($HEADER_FIELD->getFieldInfo()))}"
									   value="{$HEADER_FIELD->getEditViewDisplayValue($RELATED_RECORD->get($RELATED_HEADERNAME))}"
									   data-js="change"
								/>
							{else}
								{$HEADER_FIELD->getDisplayValue($RELATED_RECORD->get($RELATED_HEADERNAME))}
							{/if}
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
							nowrap>{$RELATED_RECORD->get('rel_comment')}</td>
					{/if}
					{if $IS_INVENTORY}
						{$COUNT = $COUNT+1}
						<td class="medium" nowrap>
							<button type="button"
									class="btn btn-sm btn-info float-right js-popover-tooltip showInventoryRow"
									data-js="popover" data-placement="left"
									data-content="{\App\Language::translate('LBL_SHOW_INVENTORY_ROW')}"><span
										class="fas fa-arrows-alt-v"></span></button>
						</td>
					{/if}
				</tr>
				{if $IS_INVENTORY}
					{assign var="INVENTORY_DATA" value=$RELATED_RECORD->getInventoryData()}
					{assign var="INVENTORY_MODEL" value=Vtiger_Inventory_Model::getInstance($RELATED_RECORD->getModuleName())}
					<tr class="listViewInventoryEntries d-none">
						{if $RELATED_MODULE->isQuickSearchEnabled()}
							{$COUNT = $COUNT+1}
						{/if}
						<td colspan="{$COUNT + $ADDITIONAL_TD}" class="backgroundWhiteSmoke">
							<table class="table table-sm no-margin">
								<thead>
								<tr>
									{foreach from=$INVENTORY_FIELDS item=FIELD key=NAME}
										<th class="medium" nowrap>
											{\App\Language::translate($FIELD->get('label'),$RELATED_MODULE_NAME)}
										</th>
									{/foreach}
								</tr>
								</thead>
								<tbody>
								{foreach from=$INVENTORY_DATA item=INVENTORY_ROW}
									<tr>
										{if !empty($INVENTORY_ROW['name'])}
											{assign var="ROW_MODULE" value=\App\Record::getType($INVENTORY_ROW['name'])}
										{/if}
										{foreach from=$INVENTORY_FIELDS item=FIELD key=NAME}
											{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('DetailView',$RELATED_MODULE_NAME)}
											<td>
												{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $RELATED_MODULE_NAME) ITEM_VALUE=$INVENTORY_ROW[$FIELD->getColumnName()]}
											</td>
										{/foreach}
									</tr>
								{/foreach}
								</tbody>
							</table>
						</td>
					</tr>
				{/if}
			{/foreach}
			</tbody>
			<tfoot class="listViewSummation">
			<tr>
				<td></td>
				{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
					<td {if $HEADER_FIELD@last} colspan="2" {/if}
							class="noWrap {if !empty($HEADER_FIELD->isCalculateField())}border{/if}">
						{if !empty($HEADER_FIELD->isCalculateField())}
							<button class="btn btn-sm btn-light js-popover-tooltip" data-js="popover" type="button"
									data-operator="sum" data-field="{$HEADER_FIELD->getName()}"
									data-content="{\App\Language::translate('LBL_CALCULATE_SUM_FOR_THIS_FIELD')}">
								<span class="fas fa-signal"></span>
							</button>
							<span class="calculateValue"></span>
						{/if}
					</td>
				{/foreach}
			</tr>
			</tfoot>
		</table>
	</div>
{/strip}
