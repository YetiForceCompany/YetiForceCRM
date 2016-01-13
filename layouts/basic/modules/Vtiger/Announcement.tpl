{assign var="announcement" value=$ANNOUNCEMENT->get('announcement')}
{if $announcement}
	<div class="announcement" id="announcement">
		<marquee direction="left" scrolldelay="10" scrollamount="3" behavior="scroll" class="marStyle" onmousedown="this.stop();" onmouseup="this.start();">{if isset($announcement)}{$announcement}{else}{vtranslate('LBL_NO_ANNOUNCEMENTS',$MODULE)}{/if}</marquee>
	</div>
{/if}
