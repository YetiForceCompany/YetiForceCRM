{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="addRelatedRecordBtn w-100 mb-2">
		{if $RELATED_MODULE eq 'Products' && \App\Privilege::isPermitted('Products')}
			<button class="btn btn-sm btn-block btn-light showModal" title="{\App\Language::translate('LBL_SELECT',$MODULE_NAME)}"
				type="button"
				data-url="index.php?module=Products&view=TreeCategoryModal&src_module={$SOURCE_MODULE}&src_record={$RECORDID}">
				<span class="fas fa-search-plus"></span>
			</button>
		{else if $RELATED_MODULE eq 'OutsourcedProducts' && \App\Privilege::isPermitted('OutsourcedProducts')}
			<button class="btn btn-sm btn-block btn-light showModal" title="{\App\Language::translate('LBL_SELECT',$MODULE_NAME)}"
				type="button" data-module="OutsourcedProducts"
				data-url="index.php?module=OutsourcedProducts&view=TreeCategoryModal&src_module={$SOURCE_MODULE}&src_record={$RECORDID}">
				<span class="fas fa-search-plus"></span>
			</button>
		{else if $RELATED_MODULE eq 'Assets' && \App\Privilege::isPermitted('Assets', 'CreateView')}
			<button class="btn btn-sm btn-block btn-light" type="button"
				title="{\App\Language::translate('LBL_ADD',$MODULE_NAME)}"
				onclick="App.Components.QuickCreate.createRecord('Assets')">
				<span class="fas fa-plus-circle"></span>
			</button>
		{else if $RELATED_MODULE eq 'Services' && \App\Privilege::isPermitted('Services')}
			<button class="btn btn-sm btn-block btn-light showModal" title="{\App\Language::translate('LBL_SELECT',$MODULE_NAME)}"
				type="button"
				data-url="index.php?module=Services&view=TreeCategoryModal&src_module={$SOURCE_MODULE}&src_record={$RECORDID}">
				<span class="fas fa-search-plus"></span>
			</button>
		{else if $RELATED_MODULE eq 'OSSOutsourcedServices' && \App\Privilege::isPermitted('OSSOutsourcedServices')}
			<button class="btn btn-sm btn-block btn-light showModal" title="{\App\Language::translate('LBL_SELECT',$MODULE_NAME)}"
				type="button" data-module="OSSOutsourcedServices"
				data-url="index.php?module=OSSOutsourcedServices&view=TreeCategoryModal&src_module={$SOURCE_MODULE}&src_record={$RECORDID}">
				<span class="fas fa-search-plus"></span>
			</button>
		{else if $RELATED_MODULE eq 'OSSSoldServices' && \App\Privilege::isPermitted('OSSSoldServices', 'CreateView')}
			<button class="btn btn-sm btn-block btn-light" type="button"
				title="{\App\Language::translate('LBL_SELECT',$MODULE_NAME)}"
				onclick="App.Components.QuickCreate.createRecord('OSSSoldServices')">
				<span class="fas fa-plus-circle"></span>
			</button>
		{/if}
	</div>
	<div class="contents-bottomscroll">
		{if $RELATED_RECORDS}
			<table class="table">
				<thead>
					<tr class="">
						{foreach item=HEADER_FIELD key=KEY from=$RELATED_HEADERS}
							<th class="{$KEY} p-1" nowrap>
								{\App\Language::translate($HEADER_FIELD->getFieldLabel(), $RELATED_MODULE)}
							</th>
						{/foreach}
					</tr>
				</thead>
				<tbody>
					{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
						<tr class="listViewEntries" data-id="{$RELATED_RECORD->getId()}" {if $RELATED_RECORD->
											isViewable()}data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}' {/if}>
						{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
							{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->getFieldName()}
							<td class="{$WIDTHTYPE}" data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap>
								{if ($HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->getUIType() eq '4') &&
															$RELATED_RECORD->isViewable()}
								<a class="modCT_{$RELATED_RECORD->getModuleName()}"
									href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)|truncate:50}</a>
							{elseif $RELATED_HEADERNAME eq 'access_count'}
								{$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
							{elseif $RELATED_HEADERNAME eq 'time_start'}
							{elseif $RELATED_HEADERNAME eq 'listprice'}
								{CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
								{if $RELATED_HEADERNAME eq 'listprice'}
									{assign var="LISTPRICE" value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME),
																		null, true)}
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
						<th nowrap class="p-1">
							{\App\Language::translate($HEADER, $RELATED_MODULE)}
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
	<div class="d-flex py-1">
		<div class="ml-auto">
			<button type="button"
				class="btn btn-primary btn-sm moreProductsService">{\App\Language::translate('LBL_MORE',$MODULE_NAME)}..</button>
		</div>
	</div>
{/if}
{/strip}
