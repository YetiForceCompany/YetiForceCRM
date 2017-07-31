{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	<div class="chatItem {if \App\User::getCurrentUserId() == $ROW['userid']}active{/if}" data-cid="{$ROW['id']}">
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
