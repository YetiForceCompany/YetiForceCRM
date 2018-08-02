{strip}
	<div class="c-detail-widget u-mb-13px js-detail-widget recordDetails" data-js=”container”>
		{if $WIDGET['label'] neq ' ' && $WIDGET['label'] neq ''}
			<div class="c-detail-widget__header js-detail-widget-header" data-js=”container|value>
					<h5 class="mb-0 py-2">{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h5>
					<hr class="widgetHr">
			</div>
		{/if}
		{foreach item=SUMMARY_CATEGORY from=$RECORD->getSummaryInfo()}
			<div class="row textAlignCenter roundedCorners">
				{foreach item=FIELD_VALUE from=$SUMMARY_CATEGORY}
					<div class="col-md-3" data-reference="{$FIELD_VALUE.reference}">
						<div class="well squeezedWell">
							<div>
								<label class="font-x-small">
									{\App\Language::translate($FIELD_VALUE.name,$MODULE_NAME)}
								</label>
							</div>
							<div>
								<label class="font-x-x-large">
								{if !empty($FIELD_VALUE.data)}{$FIELD_VALUE.data}{else}0{/if}
							</label>
						</div>
					</div>
				</div>
			{/foreach}
		</div>
	{/foreach}
</div>
{/strip}
