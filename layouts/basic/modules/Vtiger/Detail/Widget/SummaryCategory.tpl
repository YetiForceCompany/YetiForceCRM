{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Detail-Widget-SummaryCategory -->
{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId(\App\Language::translate($WIDGET['label'],$MODULE_NAME))}"}
<div class="tpl-Detail-Widget-SummaryCategory c-detail-widget c-detail-widget--summmary-category mb-1 js-detail-widget recordDetails"
	data-js="container">
	{if $WIDGET['label'] neq ' ' && $WIDGET['label'] neq ''}
		<div class="c-detail-widget__header js-detail-widget-header collapsed" data-js="container|value">
			<div class="c-detail-widget__header__container d-flex align-items-center py-1 w-100">
				<div class="c-detail-widget__toggle collapsed" id="{$WIDGET_UID}" data-toggle="collapse"
					data-target="#{$WIDGET_UID}-collapse" aria-expanded="false" aria-controls="{$WIDGET_UID}-collapse">
					<span class="u-transform_rotate-180deg mdi mdi-chevron-down" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}"></span>

				</div>
				<div class="c-detail-widget__header__title">
					<h5 class="mb-0 modCT_{$WIDGET['label']}" title="{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}">
						{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h5>
				</div>
			</div>
		</div>
	{/if}
	<div class="c-detail-widget__content js-detail-widget-collapse js-detail-widget-content collapse multi-collapse" id="{$WIDGET_UID}-collapse" data-storage-key="{$WIDGET['id']}" aria-labelledby="{$WIDGET_UID}" data-js="container|value">
			<div class="px-0">
			{foreach item=SUMMARY_CATEGORY from=$RECORD->getSummaryInfo()}
				<div class="d-flex text-center o-summary-category__row mb-2 px-2">
					{foreach item=FIELD_VALUE from=$SUMMARY_CATEGORY}
						<div class="card u-w-24per mx-auto px-0" data-reference="{$FIELD_VALUE.reference}">
							<div class="card-header p-1">
								<label class="card-text small text-md-nowrap">
									<strong>{\App\Language::translate($FIELD_VALUE.name,$MODULE_NAME)}</strong>
								</label>
							</div>
							<div class="card-body bg-light rounded px-0 py-1 d-flex align-items-center">
								<div class="card-body bg-light rounded px-0 py-1">
									{if !empty($FIELD_VALUE.data) && !is_array($FIELD_VALUE.data)}
										{$FIELD_VALUE.data}
									{elseif is_array($FIELD_VALUE.data)}
										<div  class="d-flex">
											<div  class="col-6 px-0">
												<div  class="card-text small">
													{\App\Language::translate('LBL_OPEN')}
												</div>
												<div  class="card-text">
													<span class="badge badge-secondary px-2" >
														{$FIELD_VALUE.data.open}
													</span>
												</div>

											</div>
											<div  class="col-6 px-0">
												<div  class="card-text small">
													{\App\Language::translate('LBL_ALL')}
												</div>
												<div  class="card-text">
													<span class="badge badge-secondary px-2">
														{$FIELD_VALUE.data.total}
													</span>
												</div>

											</div>
										</div>
									{else}
										0
									{/if}
								</div>
							</div>
						</div>
					{/foreach}
				</div>
			{/foreach}
		</div>
	</div>
</div>
<!-- /tpl-Base-Detail-Widget-SummaryCategory -->
{/strip}
