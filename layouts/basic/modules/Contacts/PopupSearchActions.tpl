{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	<div class="col-md-4 form-group pull-left">
		{if $MULTI_SELECT}
			{if !empty($LISTVIEW_ENTRIES)}<button class="select btn btn-default"><strong>{vtranslate('LBL_SELECT', $MODULE)}</strong></button>&nbsp;{/if}
		{/if}
		{if $SOURCE_MODULE eq 'SSalesProcesses'}
			<div class="btn-group">
				<input class="switchPopup switchBtn" type="checkbox" checked title="{vtranslate('LBL_SWITCH_BUTTON',$MODULE)}" data-size="normal" data-label-width="5" data-on-text="{vtranslate('LBL_ACCOUNT',$MODULE)}" data-off-text="{vtranslate('LBL_ALL',$MODULE)}" data-on-val="{$RELATED_PARENT_ID}" data-off-val="0" data-field="relatedParentId">
			</div>
			&nbsp;<a href="#" class="popoverTooltip pull-right-xs pull-right-sm" title="" data-placement="auto bottom" data-content="{vtranslate('LBL_NARROW_DOWN_RECORDS_LIST',$MODULE)}" data-original-title="{vtranslate('LBL_SWITCH_BUTTON',$MODULE)}"><span class="glyphicon glyphicon-info-sign"></span></a>
		{/if}
	</div>
{/strip}
