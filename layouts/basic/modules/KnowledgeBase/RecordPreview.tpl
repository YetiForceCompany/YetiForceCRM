
{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-KnowledgeBase-RecordPreview -->
<div class="modal js-modal-data quasar-modal quasar-reset" tabindex="-1" data-js="data" data-focus="false"
	 role="dialog" {foreach from=$MODAL_VIEW->modalData key=KEY item=VALUE} data-{$KEY}="{$VALUE}"{/foreach}>
	{foreach item=MODEL from=$MODAL_CSS}
		<link  rel="{$MODEL->getRel()}" href="{$MODEL->getHref()}"/>
	{/foreach}
	<link rel="stylesheet" href="{\App\Layout::getPublicUrl('libraries/@mdi/font/css/materialdesignicons.min.css')}">
	<link id="quasar-css" rel="stylesheet" href="{\App\Layout::getPublicUrl('src/css/app.css')}">
	{foreach item=MODEL from=$MODAL_SCRIPTS}
		<script type="{$MODEL->getType()}" src="{$MODEL->getSrc()}"></script>
	{/foreach}
	<script type="text/javascript">app.registerModalController();</script>
	<div id="RecordPreview"></div>
</div>
	<!-- /tpl-KnowledgeBase-RecordPreview -->
{/strip}
