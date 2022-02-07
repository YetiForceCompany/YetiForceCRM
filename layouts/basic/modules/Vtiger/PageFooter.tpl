{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-PageFooter -->
	</div>
	<input type="hidden" id="processEvents" value="{\App\Purifier::encodeHtml(\App\Json::encode(App\Process::getEvents()))}" />
	{if $SHOW_FOOTER}
		{include file=\App\Layout::getTemplatePath('Footer.tpl')}
	{/if}
	{* javascript files *}
	{include file=\App\Layout::getTemplatePath('JSResources.tpl')}
	{if \App\Debuger::isDebugBar()}
		{\App\Debuger::getDebugBar()->getJavascriptRenderer()->render()}
	{/if}
	</body>

	</html>
	<!-- /tpl-Base-PageFooter -->
{/strip}
