{assign var="announcement" value=$ANNOUNCEMENT->get('announcement')}
{if $announcement}
	<div class="announcement" id="announcement">
		<div class="alert alert-info">
			{if isset($announcement)}{$announcement}{else}{vtranslate('LBL_NO_ANNOUNCEMENTS',$MODULE)}{/if}
		</div>
	</div>
{/if}
