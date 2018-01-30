{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="col-md-2 form-group pull-left">
		{if $MULTI_SELECT}
			{if !empty($LISTVIEW_ENTRIES)}<button class="select btn btn-light"><strong>{\App\Language::translate('LBL_SELECT', $MODULE)}</strong></button>&nbsp;{/if}
		{/if}
	</div>
	{if $SWITCH && !empty($LISTVIEW_ENTRIES)}
		<div class="col-md-4 form-group pull-left">
			<div class="btn-group">
				<input class="switchPopup switchBtn" type="checkbox"{if $RELATED_PARENT_ID} checked{else} disabled{/if} title="{\App\Language::translate('LBL_POPUP_SWITCH_BUTTON',$MODULE)}" data-size="normal" data-label-width="5" data-on-text="{$POPUP_SWITCH_ON_TEXT}" data-off-text="{\App\Language::translate('LBL_ALL',$MODULE)}" data-on-val="{$RELATED_PARENT_ID}" data-off-val="0" data-field="relatedParentId">
			</div>
			<div class="btn-group">
				&nbsp;<a href="#" class="popoverTooltip pull-right-xs pull-right-sm pull-right" title="" data-placement="auto bottom" data-content="{\App\Language::translate('LBL_POPUP_NARROW_DOWN_RECORDS_LIST',$MODULE)}" data-original-title="{\App\Language::translate('LBL_POPUP_SWITCH_BUTTON',$MODULE)}"><span class="fa fa-info-circle"></span></a>
			</div>
		</div>
	{/if}
{/strip}
