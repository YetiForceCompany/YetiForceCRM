
{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-KnowledgeBase-RecordPreview -->
<div id="quasar-reset" class="modal js-modal-data quasar-modal quasar-reset" tabindex="-1" data-js="data" data-focus="false"
	 role="dialog" {foreach from=$MODAL_VIEW->modalData key=KEY item=VALUE} data-{$KEY}="{$VALUE}"{/foreach}>
	{foreach item=MODEL from=$MODAL_CSS}
		<link  rel="{$MODEL->getRel()}" href="{$MODEL->getHref()}"/>
	{/foreach}
	{foreach item=MODEL from=$MODAL_SCRIPTS}
		<script type="{$MODEL->getType()}" src="{$MODEL->getSrc()}"></script>
	{/foreach}
	<script type="text/javascript">app.registerModalController();</script>
	<div id="RecordPreview"></div>
</div>
	<!-- /tpl-KnowledgeBase-RecordPreview -->
{/strip}
