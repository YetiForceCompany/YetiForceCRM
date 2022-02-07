{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-MailIntegration-Iframe-Content -->
	<iframe id="js-iframe" width="100%" height="100%" frameborder="0" class="w-100 position-absolute" data-view="iframe" allowfullscreen="true" allow="geolocation;microphone;camera" data-js="iframe"></iframe>
	{foreach item=MODEL from=$MODAL_SCRIPTS}
		<script type="{$MODEL->getType()}" src="{$MODEL->getSrc()}"></script>
	{/foreach}
	<!-- /tpl-MailIntegration-Iframe-Content -->
{/strip}
