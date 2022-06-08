{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=IS_INVENTORY value=($RELATED_MODULE->isInventory() && !empty($INVENTORY_FIELDS))}
	{if !$TYPE_VIEW || $TYPE_VIEW eq 'List'}
		<input type="hidden" class="relatedView" value="List">
		<div class="listViewEntriesDiv relatedContents table-responsive">
			<table class="table c-detail-widget__table listViewEntriesTable">
				<thead>
					<tr class="text-left">
						{if !$IS_READ_ONLY}
							<th class="noWrap p-1"></th>
						{/if}
						{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
							<th nowrap class="p-1">
								{\App\Language::translate($HEADER_FIELD->getFieldLabel(), $HEADER_FIELD->getModuleName())}
							</th>
						{/foreach}
						{if $SHOW_CREATOR_DETAIL}
							<th class="p-1">{\App\Language::translate('LBL_RELATION_CREATED_TIME', $RELATED_MODULE->get('name'))}</th>
							<th class="p-1">{\App\Language::translate('LBL_RELATION_CREATED_USER', $RELATED_MODULE->get('name'))}</th>
						{/if}
						{if $SHOW_COMMENT}
							<th class="p-1">{\App\Language::translate('LBL_RELATION_COMMENT', $RELATED_MODULE->get('name'))}</th>
						{/if}
						{if $IS_INVENTORY}
							<th class="noWrap p-1"></th>
						{/if}
					</tr>
				</thead>
				{assign var=COUNT value=0}
				{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
					<tr class="listViewEntries js-list__row" data-id="{$RELATED_RECORD->getId()}" {if $RELATED_RECORD->isViewable()}
						data-recordUrl="{$RELATED_RECORD->getDetailViewUrl()}" {/if} data-js="container">
						{if !$IS_READ_ONLY}
							<td class="noWrap leftRecordActions listButtons {$WIDTHTYPE}">
								{include file=\App\Layout::getTemplatePath('RelatedListLeftSide.tpl', $RELATED_MODULE_NAME)}
							</td>
						{/if}
						{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
							{$COUNT = $COUNT+1}
							{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->getFieldName()}
							<td class="text-left {$WIDTHTYPE}
							{if $HEADER_FIELD->getFieldDataType() eq 'documentsFileUpload' && $HEADER_FIELD->isEditable() && $RELATED_RECORD->isEditable() && 'I' === $RELATED_RECORD->getValueByField('filelocationtype') } js-drop-container{/if}"
								data-id="{$RELATED_RECORD->getId()}"
								data-module="{$RELATED_MODULE->getName()}"
								data-field-name="{$RELATED_HEADERNAME}"
								data-field-type="{$HEADER_FIELD->getFieldDataType()}"
								nowrap>
								{if ($HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->getUIType() eq '4') && $RELATED_RECORD->isViewable()}
									<a class="modCT_{$RELATED_MODULE_NAME} js-popover-tooltip--record" href="{$RELATED_RECORD->getDetailViewUrl()}">
										{$RELATED_RECORD->getListViewDisplayValue($RELATED_HEADERNAME)}
									</a>
								{elseif $HEADER_FIELD->get('fromOutsideList') eq true}
									{$HEADER_FIELD->getDisplayValue($RELATED_RECORD->get($RELATED_HEADERNAME))}
								{else}
									{$RELATED_RECORD->getListViewDisplayValue($RELATED_HEADERNAME)}
								{/if}
							</td>
						{/foreach}
						{if $SHOW_CREATOR_DETAIL}
							<td class="{$WIDTHTYPE} text-left" data-field-type="rel_created_time"
								nowrap>{App\Fields\DateTime::formatToDisplay($RELATED_RECORD->get('rel_created_time'))}</td>
							<td class="{$WIDTHTYPE} text-left" data-field-type="rel_created_user"
								nowrap>{\App\Fields\Owner::getLabel($RELATED_RECORD->get('rel_created_user'))}</td>
						{/if}
						{if $SHOW_COMMENT}
							<td class="{$WIDTHTYPE} text-left" data-field-type="rel_comment" nowrap>
								{if strlen($RELATED_RECORD->get('rel_comment')) > App\Config::relation('COMMENT_MAX_LENGTH')}
									<a class="js-popover-tooltip" data-js="popover" data-placement="top"
										data-content="{$RELATED_RECORD->get('rel_comment')}">
										{App\TextUtils::textTruncate($RELATED_RECORD->get('rel_comment'), App\Config::relation('COMMENT_MAX_LENGTH'))}
									</a>
								{else}
									{$RELATED_RECORD->get('rel_comment')}
								{/if}&nbsp;&nbsp;
								<span class="actionImages">
									<a class="showModal"
										data-url="index.php?module={$PARENT_RECORD->getModuleName()}&view=RelatedCommentModal&record={$PARENT_RECORD->getId()}&relid={$RELATED_RECORD->getId()}&relmodule={$RELATED_MODULE->get('name')}">
										<span class="yfi yfi-full-editing-view"
											title="{\App\Language::translate('LBL_EDIT', $MODULE)}"></span>
									</a>
								</span>
							</td>
						{/if}
						{if $IS_INVENTORY}
							{$COUNT = $COUNT+1}
							<td nowrap>
								<button type="button" class="btn btn-sm btn-info js-popover-tooltip js-toggle-hidden-row" data-js="popover" data-placement="left" data-element="inventory" data-content="{\App\Language::translate('LBL_SHOW_INVENTORY_ROW')}">
									<span class="fas fa-arrows-alt-v"></span>
								</button>
							</td>
						{/if}
					</tr>
					{if $IS_INVENTORY}
						{assign var="INVENTORY_DATA" value=$RELATED_RECORD->getInventoryData()}
						{assign var="INVENTORY_MODEL" value=Vtiger_Inventory_Model::getInstance($RELATED_RECORD->getModuleName())}
						<tr class="js-hidden-row d-none">
							{if $RELATED_MODULE->isQuickSearchEnabled()}
								{$COUNT = $COUNT+1}
							{/if}
							<td colspan="{$COUNT+1}" class="backgroundWhiteSmoke">
								<div class="js-hidden-row__block d-none" data-element="inventory">
									<table class="table table-sm no-margin d-none" data-element="inventory">
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
													{if $INVENTORY_ROW['name']}
														{assign var="ROW_MODULE" value=\App\Record::getType($INVENTORY_ROW['name'])}
													{/if}
													{foreach from=$INVENTORY_FIELDS item=FIELD key=NAME}
														{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('DetailView', $RELATED_MODULE_NAME)}
														<td>
															{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $RELATED_MODULE_NAME) ITEM_VALUE=$INVENTORY_ROW[$FIELD->getColumnName()]}
														</td>
													{/foreach}
												</tr>
											{/foreach}
										</tbody>
									</table>
								</div>
							</td>
						</tr>
					{/if}
				{/foreach}
			</table>
		</div>
	{elseif $TYPE_VIEW eq 'Summary'}
		<div class="listViewEntriesDiv relatedContents table-responsive">
			<div class="carousel slide" data-interval="false" data-ride="carousel">
				<div class="carousel-inner" role="listbox">
					{foreach item=RELATED_RECORD from=$RELATED_RECORDS name=recordlist}
						<div class="carousel-item  js-carousel-item {if $smarty.foreach.recordlist.first}active{/if}"
							data-id="{$RELATED_RECORD->getId()}" data-js="click">
							<table class="c-detail-widget__table u-table-fixed">
								<tbody>
									{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
										<tr class="c-table__row--hover border-bottom">
											<td class="u-w-40per {$WIDTHTYPE} px-0">
												<label class="font-weight-bold mb-0">
													{assign var=ICON value=$HEADER_FIELD->getIcon('Summary')}
													{if isset($ICON['name'])}<span class="{$ICON['name']} mr-2"></span>{/if}
													{\App\Language::translate($HEADER_FIELD->getFieldLabel(), $RELATED_MODULE->get('name'))}
												</label>
											</td>
											{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->getFieldName()}
											<td class="fieldValue {$WIDTHTYPE} px-0">
												<div class="value u-word-break pr-0">
													{if ($HEADER_FIELD->isNameField() eq true) && $RELATED_RECORD->isViewable()}
														<a class="modCT_{$RELATED_MODULE_NAME} js-popover-tooltip--record" href="{$RELATED_RECORD->getDetailViewUrl()}">
															{$RELATED_RECORD->getListViewDisplayValue($RELATED_HEADERNAME)}
														</a>
													{else}
														{$RELATED_RECORD->getListViewDisplayValue($RELATED_HEADERNAME)}
													{/if}
												</div>
											</td>
										</tr>
									{/foreach}
								</tbody>
							</table>
							<div class="float-right py-1">
								{if $RELATED_RECORD->isViewable()}
									<a class="addButton" href="{$RELATED_RECORD->getFullDetailViewUrl()}">
										<button class="btn btn-sm btn-light js-popover-tooltip" data-js="popover"
											type="button">
											<span title="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}"
												class="fas fa-th-list"></span>
										</button>
									</a>
								{/if}
								{if $RELATED_RECORD->isEditable()}
									<a class="addButton" href="{$RELATED_RECORD->getEditViewUrl()}">
										<button class="btn btn-sm btn-light js-popover-tooltip" data-js="popover"
											type="button">
											<span title="{\App\Language::translate('LBL_EDIT', $MODULE)}"
												class="yfi yfi-full-editing-view"></span>
										</button>
									</a>
									{if $RELATED_MODULE->isQuickCreateSupported()}
										<button type="button" class="btn btn-sm btn-light js-quick-edit-modal js-popover-tooltip" data-module="{$RELATED_RECORD->getModuleName()}" data-record="{$RELATED_RECORD->getId()}" data-content="{\App\Language::translate('LBL_QUICK_EDIT',$MODULE)}" data-js="click|popover">
											<span title="{\App\Language::translate('LBL_QUICK_EDIT',$MODULE)}" class="yfi yfi-quick-creation "></span>
										</button>
									{/if}
								{/if}
							</div>
						</div>
					{/foreach}
				</div>
			</div>
		</div>
	{else}
		<div class="listViewEntriesDiv relatedContents table-responsive">
			<table class="table c-detail-widget__table listViewEntriesTable">
				<thead>
					<tr class="text-center">
						{if !$IS_READ_ONLY}
							<th class="noWrap p-1">&nbsp;</th>
						{/if}
						{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
							<th nowrap class="p-1">
								{\App\Language::translate($HEADER_FIELD->getFieldLabel(), $RELATED_MODULE->get('name'))}
							</th>
						{/foreach}
						{if $SHOW_CREATOR_DETAIL}
							<th class="p-1">{\App\Language::translate('LBL_RELATION_CREATED_TIME', $RELATED_MODULE->get('name'))}</th>
							<th class="p-1">{\App\Language::translate('LBL_RELATION_CREATED_USER', $RELATED_MODULE->get('name'))}</th>
						{/if}
						{if $SHOW_COMMENT}
							<th class="p-1">{\App\Language::translate('LBL_RELATION_COMMENT', $RELATED_MODULE->get('name'))}</th>
						{/if}
					</tr>
				</thead>
				{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
					<tr class="listViewEntries showSummaryRelRecord" data-id="{$RELATED_RECORD->getId()}"
						{if $RELATED_RECORD->isViewable()}
							data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'
						{/if}>
						{if !$IS_READ_ONLY}
							<td class="noWrap leftRecordActions listButtons {$WIDTHTYPE}">
								{include file=\App\Layout::getTemplatePath('RelatedListLeftSide.tpl', $RELATED_MODULE_NAME)}
							</td>
						{/if}
						{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
							{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->getFieldName()}
							<td class="text-center {$WIDTHTYPE}" data-field-type="{$HEADER_FIELD->getFieldDataType()}"
								nowrap>
								{$RELATED_RECORD->getListViewDisplayValue($RELATED_HEADERNAME)}
							</td>
						{/foreach}
						{if $SHOW_CREATOR_DETAIL}
							<td class="{$WIDTHTYPE} text-center" data-field-type="rel_created_time"
								nowrap>{App\Fields\DateTime::formatToDisplay($RELATED_RECORD->get('rel_created_time'))}</td>
							<td class="{$WIDTHTYPE} text-center" data-field-type="rel_created_user"
								nowrap>{\App\Fields\Owner::getLabel($RELATED_RECORD->get('rel_created_user'))}</td>
						{/if}
						{if $SHOW_COMMENT}
							<td class="{$WIDTHTYPE} text-center" data-field-type="rel_comment" nowrap>
								{if strlen($RELATED_RECORD->get('rel_comment')) > App\Config::relation('COMMENT_MAX_LENGTH')}
									<a class="js-popover-tooltip" data-js="popover" data-placement="top"
										data-content="{$RELATED_RECORD->get('rel_comment')}">
										{vtlib\Functions::textLength($RELATED_RECORD->get('rel_comment'), App\Config::relation('COMMENT_MAX_LENGTH'))}
									</a>
								{else}
									{$RELATED_RECORD->get('rel_comment')}
								{/if}&nbsp;&nbsp;
								<span class="actionImages">
									<a class="showModal"
										data-url="index.php?module={$PARENT_RECORD->getModuleName()}&view=RelatedCommentModal&record={$PARENT_RECORD->getId()}&relid={$RELATED_RECORD->getId()}&relmodule={$RELATED_MODULE->get('name')}">
										<span class="yfi yfi-full-editing-view"
											title="{\App\Language::translate('LBL_EDIT', $MODULE)}"></span>
									</a>
								</span>
							</td>
						{/if}
					</tr>
				{/foreach}
			</table>
			{foreach item=RELATED_RECORD from=$RELATED_RECORDS name=recordlist}
				{assign var=ID value=$RELATED_RECORD->getId()}
				<div class="hide summaryRelRecordView summaryRelRecordView{$ID}" data-id="{$ID}">
					<table class="c-detail-widget__table u-table-fixed">
						<tbody>
							{foreach item=HEADER_FIELD from=$RELATED_SUMMARY_HEADERS}
								<tr class="c-table__row--hover border-bottom">
									<td class="u-w-40per {$WIDTHTYPE} px-0">
										<label class="font-weight-bold mb-0">
											{assign var=ICON value=$HEADER_FIELD->getIcon('Summary')}
											{if isset($ICON['name'])}<span class="{$ICON['name']} mr-2"></span>{/if}
											{\App\Language::translate($HEADER_FIELD->getFieldLabel(), $RELATED_MODULE->get('name'))}
										</label>
									</td>
									{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->getFieldName()}
									<td class="fieldValue {$WIDTHTYPE} px-0">
										<div class="value u-word-break pr-0">
											{$RELATED_RECORD->getListViewDisplayValue($RELATED_HEADERNAME)}
										</div>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
					<div class="float-right py-1">
						{if $RELATED_RECORD->isViewable()}
							<a class="addButton" href="{$RELATED_RECORD->getDetailViewUrl()}">
								<button type="button" class="btn btn-sm btn-light js-popover-tooltip" data-js="popover"
									data-content="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}">
									<span title="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}"
										class="fas fa-th-list"></span>
								</button>
							</a>
						{/if}
						{if $RELATED_RECORD->isEditable()}
							<a class="addButton" href="{$RELATED_RECORD->getEditViewUrl()}">
								<button type="button" class="btn btn-sm btn-light js-popover-tooltip" data-js="popover"
									data-content="{\App\Language::translate('LBL_EDIT', $MODULE)}">
									<span title="{\App\Language::translate('LBL_EDIT', $MODULE)}"
										class="yfi yfi-full-editing-view"></span>
								</button>
							</a>
							{if $RELATED_MODULE->isQuickCreateSupported()}
								<button type="button" class="btn btn-sm btn-light js-quick-edit-modal js-popover-tooltip" data-module="{$RELATED_RECORD->getModuleName()}" data-record="{$RELATED_RECORD->getId()}" data-content="{\App\Language::translate('LBL_QUICK_EDIT',$MODULE)}" data-js="click|popover">
									<span title="{\App\Language::translate('LBL_QUICK_EDIT',$MODULE)}" class="yfi yfi-quick-creation "></span>
								</button>
							{/if}
						{/if}
						<button type="button" class="btn btn-sm btn-light js-popover-tooltip">
							<span class="far fa-times-circle hideSummaryRelRecordView u-cursor-pointer"></span>
						</button>
					</div>
				</div>
			{/foreach}
		</div>
	{/if}
{/strip}
