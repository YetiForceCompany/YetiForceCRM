{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-SummaryCategory -->
	{assign var=WIDGET_UID value="id-{\App\Layout::getUniqueId($WIDGET['id']|cat:_)}"}
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
						<h5 class="mb-0" title="{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}">
							{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}
						</h5>
					</div>
				</div>
			</div>
		{/if}
		<div class="c-detail-widget__content js-detail-widget-collapse js-detail-widget-content collapse multi-collapse" id="{$WIDGET_UID}-collapse" data-storage-key="{$WIDGET['id']}" aria-labelledby="{$WIDGET_UID}" data-js="container|value">
			<div class="px-0">
				{foreach item=SUMMARY_CATEGORY from=$RECORD->getSummaryInfo() }
					<div class="d-flex text-center o-summary-category__row mb-2 px-0">
						{foreach item=FIELD_VALUE from=$SUMMARY_CATEGORY}
							<div class="card col mx-1 px-0" data-reference="{$FIELD_VALUE.reference}">
								<div class="card-header p-1">
									<label class="card-text small text-md-nowrap">
										{if isset($FIELD_VALUE.icon)}
											<span class="{$FIELD_VALUE.icon} mr-1"></span>
										{/if}
										<strong>
											{\App\Language::translate($FIELD_VALUE.name,$MODULE_NAME)}
										</strong>
									</label>
								</div>
								<div class="card-body bg-light rounded px-0 pt-1 pb-2 d-flex align-items-center justify-content-center">
									{foreach item=DATA from=$FIELD_VALUE.data}
										{if isset($FIELD_VALUE.type) && $FIELD_VALUE.type eq 'badge' }
											<div class="col px-1">
												<div class="px-0">
													<div class="card-text small">
														{$DATA.label}
													</div>
													<div class="card-text mt-1">
														<a class="badge {$DATA.class} px-2 u-fs-lg" {if isset($DATA.badgeLink)} href="{$DATA.badgeLink}" {/if}>
															{$DATA.value}
														</a>
													</div>
												</div>
											</div>
										{else}
											<label class="card-text small text-md-nowrap text-center">
												{if !empty($DATA)}
													{$DATA}
												{else}
													0
												{/if}
											</label>
										{/if}
									{/foreach}
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
