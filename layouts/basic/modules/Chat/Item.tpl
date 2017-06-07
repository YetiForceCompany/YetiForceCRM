{strip}
	<div class="chatItem {if $USER_MODEL->getId() == $ROW['userid']}active{/if}">
		<div class="pull-right">
			<small title="{$ROW['created']}">
				{Vtiger_Util_Helper::formatDateDiffInStrings($ROW['created'])}
			</small>  
		</div>
		<div class="author">
			<i class="fa fa-comment-o" aria-hidden="true"></i>
			<span>{$ROW['user_name']}</span>
		</div>
		<div class="messages">{App\Purifier::decodeHtml($ROW['messages'])}</div>
	</div>
{/strip}
