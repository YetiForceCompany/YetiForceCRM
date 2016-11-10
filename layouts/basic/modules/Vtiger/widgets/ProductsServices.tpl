{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="addReletedRecordBtn">
		{if $RELATED_MODULE eq 'Products' && Users_Privileges_Model::isPermitted('Assets')}
			<button class="btn btn-xs btn-default showModal" title="{vtranslate('LBL_SELECT',$MODULE_NAME)}" type="button" data-url="index.php?module=Products&view=TreeCategoryModal&src_module={$SOURCE_MODULE}&src_record={$RECORDID}">
				<span class="glyphicon glyphicon-zoom-in"></span>
			</button>
		{/if}
		{if $RELATED_MODULE eq 'OutsourcedProducts' && Users_Privileges_Model::isPermitted('Assets')}
			<button class="btn btn-xs btn-default showModal" title="{vtranslate('LBL_SELECT',$MODULE_NAME)}" type="button" data-module="OutsourcedProducts" data-url="index.php?module=OutsourcedProducts&view=TreeCategoryModal&src_module={$SOURCE_MODULE}&src_record={$RECORDID}">
				<span class="glyphicon glyphicon-zoom-in" ></span>
			</button>
		{/if}
		{if $RELATED_MODULE eq 'Assets' && Users_Privileges_Model::isPermitted('Assets', 'CreateView')}
			<button class="btn btn-xs btn-default" type="button" title="{vtranslate('LBL_ADD',$MODULE_NAME)}" onclick="Vtiger_Header_Js.getInstance().quickCreateModule('Assets')">
				<span class="glyphicon glyphicon-plus-sign" ></span>
			</button>
		{/if}
		{if $RELATED_MODULE eq 'Services' && Users_Privileges_Model::isPermitted('Assets')}
			<button class="btn btn-xs btn-default showModal" title="{vtranslate('LBL_SELECT',$MODULE_NAME)}" type="button" data-url="index.php?module=Services&view=TreeCategoryModal&src_module={$SOURCE_MODULE}&src_record={$RECORDID}">
				<span class="glyphicon glyphicon-zoom-in"></span>
			</button>
		{/if}
		{if $RELATED_MODULE eq 'OSSOutsourcedServices' && Users_Privileges_Model::isPermitted('Assets')}
			<button class="btn btn-xs btn-default showModal" title="{vtranslate('LBL_SELECT',$MODULE_NAME)}" type="button" data-module="OSSOutsourcedServices" data-url="index.php?module=OSSOutsourcedServices&view=TreeCategoryModal&src_module={$SOURCE_MODULE}&src_record={$RECORDID}">
				<span class="glyphicon glyphicon-zoom-in" ></span>
			</button>
		{/if}
		{if $RELATED_MODULE eq 'OSSSoldServices' && Users_Privileges_Model::isPermitted('OSSSoldServices', 'CreateView')}
			<button class="btn btn-xs btn-default" type="button" title="{vtranslate('LBL_SELECT',$MODULE_NAME)}" onclick="Vtiger_Header_Js.getInstance().quickCreateModule('OSSSoldServices')">
				<span class="glyphicon glyphicon-plus-sign" ></span>
			</button>
		{/if}
	</div>
	<div class="contents-bottomscroll">
		{if $RELATED_RECORDS}
			<table class="table">
				<thead>
					<tr class="">
						{foreach item=HEADER_FIELD key=KEY from=$RELATED_HEADERS}
							<th class="{$KEY}" nowrap>
								{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE)}
							</th>
						{/foreach}
					</tr>
				</thead>
				<tbody>
					{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
						<tr class="listViewEntries" data-id="{$RELATED_RECORD->getId()}" {if $RELATED_RECORD->isViewable()}data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'{/if}>
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
								</td>
							{/foreach}
						</tr>
					{/foreach}
				</tbody>
			</table>
		{/if}
		{if isset($RELATED_HEADERS_TREE) && $RELATED_RECORDS_TREE}
			<table class="table">
				<thead>
					<tr class="">
						{foreach item=HEADER from=$RELATED_HEADERS_TREE}
							<th nowrap>
								{vtranslate($HEADER, $RELATED_MODULE)}
							</th>
						{/foreach}
					</tr>
				</thead>
				<tbody>
					{foreach item=RECORD from=$RELATED_RECORDS_TREE}
						<tr class="listViewEntries"> 
							{foreach item=HEADER key=NAME from=$RELATED_HEADERS_TREE}
								<td class="{$WIDTHTYPE}" nowrap>{$RECORD[$NAME]}</td>
							{/foreach}
						</tr>
					{/foreach}
				</tbody>
			</table>
		{/if}
	</div>
	{if $RECORD_PAGING_MODEL->isNextPageExists()}
		<div class="row">
			<div class="pull-right">
				<button type="button" class="btn btn-primary btn-xs marginRight10 marginTop10 moreProductsService">{vtranslate('LBL_MORE',$MODULE_NAME)}..</button>
			</div>
		</div>
	{/if}
{/strip}
