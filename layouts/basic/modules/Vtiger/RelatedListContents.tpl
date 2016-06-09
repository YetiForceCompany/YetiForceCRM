{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<div class="listViewEntriesDiv contents-bottomscroll">
		<table class="table table-bordered listViewEntriesTable">
			<thead>
				<tr class="listViewHeaders">
					{assign var=COUNT value=0}
					{if $IS_FAVORITES}
						<th></th>
						{/if}
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
								<a href="javascript:void(0);" class="relatedListHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->get('column')}">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}
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
			{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
				<tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' 
					{if $RELATED_RECORD->isViewable()}
						data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'
					{/if}>
					{assign var=COUNT value=0}
					{if $IS_FAVORITES}
						<td class="{$WIDTHTYPE} text-center text-center font-larger">
							{assign var=RECORD_IS_FAVORITE value=(int)in_array($RELATED_RECORD->getId(),$FAVORITES)}
							<a class="favorites" data-state="{$RECORD_IS_FAVORITE}">
								<span title="{vtranslate('LBL_REMOVE_FROM_FAVORITES', $MODULE)}" class="glyphicon glyphicon-star alignMiddle {if !$RECORD_IS_FAVORITE}hide{/if}"></span>
								<span title="{vtranslate('LBL_ADD_TO_FAVORITES', $MODULE)}" class="glyphicon glyphicon-star-empty alignMiddle {if $RECORD_IS_FAVORITE}hide{/if}"></span>
							</a>
						</td>
					{/if}
					{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
						{if !empty($COLUMNS) && $COUNT == $COLUMNS }
							{break}
						{/if}
						{assign var=COUNT value=$COUNT+1}
						{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
						<td class="{$WIDTHTYPE}" data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap>
							{if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
								<a class="moduleColor_{$RELATED_MODULE_NAME}" title="" href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)|truncate:50}</a>
							{elseif $HEADER_FIELD->fromOutsideList eq true}
								{$HEADER_FIELD->getDisplayValue($RELATED_RECORD->get($RELATED_HEADERNAME))}
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
								{include file=vtemplate_path('RelatedListActions.tpl',$RELATED_MODULE_NAME)}
							</td>
						{/if}
						</td>
					{/foreach}
					{if $SHOW_CREATOR_DETAIL}
						<td class="medium" data-field-type="rel_created_time" nowrap>{$RELATED_RECORD->get('relCreatedTime')}</td>
						<td class="medium" data-field-type="rel_created_user" nowrap>{$RELATED_RECORD->get('relCreatedUser')}</td>
					{/if}
					{if $SHOW_COMMENT}
						<td class="medium" data-field-type="rel_comment" nowrap>{$RELATED_RECORD->get('relComment')}</td>
					{/if}
				</tr>
				{if $RELATED_RECORD->get('inventoryData')}
					{assign var="INVENTORY_DATA" value=$RELATED_RECORD->get('inventoryData')}
					{assign var="INVENTORY_FIELDS" value=Vtiger_InventoryField_Model::getInstance($RELATED_MODULE_NAME)->getFields()}
					<tr class="listViewInventoryEntries hide">
						<td colspan="{$COUNT+1}" class="backgroundWhiteSmoke">
							<table class="table table-condensed no-margin">
								<thead>
									<tr>
										{foreach from=$INVENTORY_DATA[0] item=VALUE key=NAME}
											{assign var="FIELD" value=$INVENTORY_FIELDS[$NAME]}
											<th class="medium" nowrap>{vtranslate($FIELD->get('label'),$RELATED_MODULE_NAME)}</th>
										{/foreach}
									</tr>
								</thead>
								<tbody>
									{foreach from=$INVENTORY_DATA item=ROWDATA}
										<tr>
											{if $INVENTORY_ROW['name']}
												{assign var="ROW_MODULE" value=Vtiger_Functions::getCRMRecordType($INVENTORY_ROW['name'])}
											{/if}
											{foreach from=$ROWDATA item=VALUE key=NAME}
												{assign var="FIELD" value=$INVENTORY_FIELDS[$NAME]}
												{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('DetailView',$RELATED_MODULE_NAME)}
												<td>		
													{include file=$FIELD_TPL_NAME|@vtemplate_path:$RELATED_MODULE_NAME ITEM_VALUE=$ROWDATA[$FIELD->get('columnname')]}
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
		</table>
	</div>
{/strip}
