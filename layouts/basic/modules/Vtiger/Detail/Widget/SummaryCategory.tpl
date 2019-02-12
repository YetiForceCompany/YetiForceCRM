{strip}
	<div class="tpl-Detail-Widget-SummaryCategory c-detail-widget c-detail-widget--summmary-category u-mb-13px js-detail-widget recordDetails" data-js="container">
		{if $WIDGET['label'] neq ' ' && $WIDGET['label'] neq ''}
			<div>
				<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
					<h5 class="mb-0 py-2">{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h5>
					<hr class="widgetHr">
				</div>
			</div>
		{/if}
		<div class="mx-2">
			{foreach item=SUMMARY_CATEGORY from=$RECORD->getSummaryInfo()}
				<div class="row text-center o-summary-category__row">
					{foreach item=FIELD_VALUE from=$SUMMARY_CATEGORY}
						<div class="o-summary-category__card col-md-3" data-reference="{$FIELD_VALUE.reference}">
							<div class="o-summary-category__card__body mb-3 bg-light rounded py-2">
								<div>
									<label class="o-summary-category__card__label small">
										{\App\Language::translate($FIELD_VALUE.name,$MODULE_NAME)}
									</label>
								</div>
								<div>
									<label class="o-summary-category__card__label h5">
										{if !empty($FIELD_VALUE.data)}{$FIELD_VALUE.data}{else}0{/if}
									</label>
								</div>
							</div>
						</div>
					{/foreach}
				</div>
			{/foreach}
		</div>
	</div>
{/strip}
