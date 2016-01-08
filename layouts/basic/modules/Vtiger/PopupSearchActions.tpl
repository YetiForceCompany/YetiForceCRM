{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	<div class="col-md-2 form-group pull-left">
		{if $MULTI_SELECT}
			{if !empty($LISTVIEW_ENTRIES)}<button class="select btn btn-default"><strong>{vtranslate('LBL_SELECT', $MODULE)}</strong></button>{/if}
				{else}
			&nbsp;
		{/if}
	</div>
{/strip}
