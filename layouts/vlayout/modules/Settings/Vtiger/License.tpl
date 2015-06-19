{strip}
	<div class="settingsIndexPage">
		<div class="widget_header">
			<h3>{vtranslate('LBL_SUMMARY_LICENSE',$MODULE)}</h3>
		</div>
		<hr>
		<pre>
			{if $USERLANG eq 'pl_pl'}
				{include file="licenses/LicensePL.txt"}
			{else}
				{include file="licenses/LicenseEN.txt"}
			{/if}
		</pre>
	</div>
{/strip}
