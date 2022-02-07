{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Dashboards-MultifilterContents -->
	{assign var="IS_HIDDEN" value=true}
	<div class="detailViewTable" data-js="data-id|data-url" data-id="{$CUSTOM_VIEW_ID}"
		data-url="{\App\Purifier::encodeHtml($MULTIFILTER_WIDGET_MODEL->getTotalCountURL())}">
		<div class="js-toggle-panel c-panel" data-js="click|data-dynamic">
			<div class="blockHeader c-panel__header py-1">
				<h6 class="c-panel__title my-0 u-font-size-100per">
					{if $WIDGET_SHOW_FULL_NAME}
						{\App\Language::translate($MODULE_NAME,$MODULE_NAME)}&nbsp;-&nbsp;
					{/if}{\App\Language::translate($CUSTOM_VIEW_NAME,$MODULE_NAME)}
				</h6>
				<span class="u-cursor-pointer js-block-toggle fas fa-angle-right m-1" data-js="click"
					alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide"></span>
				<span class="u-cursor-pointer js-block-toggle fas fa-angle-down m-1 d-none" data-js="click"
					alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show"></span>

				<div class="position-absolute u-position-r-5px u-position-t-5px">
					<a class="mr-1" href="{\App\Purifier::encodeHtml($LIST_VIEW_URL)}"><span class="fa fa-list"></span></a>
					<span class="js-count badge count badge badge-danger c-badge--md">0</span>
				</div>
			</div>
			<div class="c-panel__body blockContent d-none">
				{assign var="SPANSIZE_ARRAY" value=[]}
				{assign var="SPANSIZE" value=12}
				{assign var="HEADER_COUNT" value=$MULTIFILTER_WIDGET_MODEL->getHeaderCount()}
				{if $HEADER_COUNT}
					{assign var="SPANSIZE" value=(12/$HEADER_COUNT)|string_format:"%d"}
				{/if}
				{assign var="MULTIFILTER_WIDGET_RECORDS" value=$MULTIFILTER_WIDGET_MODEL->getRecords()}
				{if !empty(count($MULTIFILTER_WIDGET_RECORDS))}
					<div class="row mb-1 border-bottom">
						{foreach item=FIELD from=$MULTIFILTER_WIDGET_MODEL->getHeaders() name=headers}
							{assign var="ITERATION" value=$smarty.foreach.headers.iteration}
							{$SPANSIZE_ARRAY[$ITERATION] = $SPANSIZE}
							{if $HEADER_COUNT eq 5 && in_array($ITERATION, [4,5])}
								{$SPANSIZE_ARRAY[$ITERATION] = 3}
							{/if}
							<div class="col-sm-{$SPANSIZE_ARRAY[$ITERATION]}">
								<strong>{\App\Language::translate($FIELD->get('label'),$BASE_MODULE)} </strong>
							</div>
						{/foreach}
					</div>
					{foreach item=RECORD from=$MULTIFILTER_WIDGET_RECORDS}
						<div class="row mb-1">
							{foreach item=FIELD from=$MULTIFILTER_WIDGET_MODEL->getHeaders() name="multifilterWidgetModelRowHeaders"}
								{assign var="ITERATION" value=$smarty.foreach.multifilterWidgetModelRowHeaders.iteration}
								{assign var="LAST_RECORD" value=$smarty.foreach.multifilterWidgetModelRowHeaders.last}
								{assign var="FIELD_VALUE" value=$RECORD->get($FIELD->get('name'))}
								<div class="col-sm-{$SPANSIZE_ARRAY[$ITERATION]}">
									{if $LAST_RECORD}
										<a href="{$RECORD->getDetailViewUrl()}" class="float-right"><span
												title="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS',$MODULE_NAME)}"
												class="fas fa-th-list alignMiddle"></span></a>
									{/if}
									{if $FIELD_VALUE}
										<div class="pr-2">
											<div class="js-popover-tooltip--ellipsis" data-toggle="popover" data-content="{\App\Purifier::encodeHtml($RECORD->getDisplayValue($FIELD->get('name')))}" data-js="popover">
												{if empty($FIELD->get('source_field_name')) && $FIELD->isNameField() && $RECORD->getModule()->isListViewNameFieldNavigationEnabled() && $RECORD->isViewable()}
													<a class="modCT_{$RECORD->getModuleName()}" href="{$RECORD->getDetailViewUrl()}">
														{$RECORD->getDisplayValue($FIELD->get('name'))}
													</a>
												{else}
													{$RECORD->getDisplayValue($FIELD->get('name'))}
												{/if}
											</div>
										</div>
									{/if}
								</div>
							{/foreach}
						</div>
					{/foreach}
				{else}
					<div class="row mt-3 mb-3">
						<div class="col-md-12 text-center">
							{\App\Language::translate('LBL_NO_RECORDS')}
						</div>
					</div>
				{/if}
			</div>
		</div>
		<!-- /tpl-Base-Dashboards-MultifilterContents -->
{/strip}
