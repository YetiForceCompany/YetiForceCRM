{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	<div class="col-md-2 form-group pull-left">
		{if $MULTI_SELECT}
			{if !empty($LISTVIEW_ENTRIES)}<button class="select btn btn-default"><strong>{vtranslate('LBL_SELECT', $MODULE)}</strong></button>&nbsp;{/if}
		{/if}
	</div>
	{if $SWITCH && !empty($LISTVIEW_ENTRIES)}
		<div class="col-md-4 form-group pull-left">
			<div class="btn-group">
				<input class="switchPopup switchBtn" type="checkbox"{if $RELATED_PARENT_ID} checked{else} disabled{/if} title="{vtranslate('LBL_POPUP_SWITCH_BUTTON',$MODULE)}" data-size="normal" data-label-width="5" data-on-text="{$POPUP_SWITCH_ON_TEXT}" data-off-text="{vtranslate('LBL_ALL',$MODULE)}" data-on-val="{$RELATED_PARENT_ID}" data-off-val="0" data-field="relatedParentId">
			</div>
			<div class="btn-group">
				&nbsp;<a href="#" class="popoverTooltip pull-right-xs pull-right-sm pull-right" title="" data-placement="auto bottom" data-content="{vtranslate('LBL_POPUP_NARROW_DOWN_RECORDS_LIST',$MODULE)}" data-original-title="{vtranslate('LBL_POPUP_SWITCH_BUTTON',$MODULE)}"><span class="glyphicon glyphicon-info-sign"></span></a>
			</div>
		</div>
	{/if}
{/strip}
