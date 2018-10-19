{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Chat-Item chatItem {if \App\User::getCurrentUserId() == $ROW['userid']}active {/if}"
		 data-mid="{$ROW['id']}">
		<div class="float-right">
			<small>
				{\App\Fields\DateTime::formatToViewDate($ROW['created'])}
			</small>
		</div>
		<div class="author">
			<i class="far fa-comment"></i>
			<b>{$ROW['user_name']}</b>
			{assign var=IMAGE value=$ROW['image']}
			{if $IMAGE}
				<img src="{$IMAGE.url}" class="mr-2" alt="{$ROW['user_name']} {$ROW['last_name']}"
					 title="{$ROW['user_name']} {$ROW['last_name']}"
					 height="80" align="left">
				<br/>
			{else}
				<span class="o-detail__icon js-detail__icon userIcon-{$MODULE}"></span>
			{/if}
		</div>
		<div class="messages">{$ROW['messages']}</div>
	</div>
{/strip}
