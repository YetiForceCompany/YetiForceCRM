{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-MailIntegration-RelationPreview-Preview -->
<iframe id="js-iframe" frameborder="0" class="w-100 position-absolute" allowfullscreen="true" data-js="iframe"></iframe>
{foreach item=MODEL from=$MODAL_SCRIPTS}
	<script type="{$MODEL->getType()}" src="{$MODEL->getSrc()}"></script>
{/foreach}
<!-- /tpl-MailIntegration-RelationPreview-Preview -->
{/strip}
