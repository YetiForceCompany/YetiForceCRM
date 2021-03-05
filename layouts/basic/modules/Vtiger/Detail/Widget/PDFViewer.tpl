{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Detail-Widget-PDFViewer -->
<div class="js-iframe-content" >
	{if $TEMPLATE}
		<iframe  class="w-100 modal-iframe js-modal-iframe" frameborder="0" src="index.php?module={$MODULE_NAME}&action=PDF&record={$RECORD_ID}&mode=generate&fromview=Detail&pdf_template={$TEMPLATE}&single_pdf=1&flag=I">
		</iframe>
		<a href="#" class="js-more noLinkBtn font-weight-lighter js-popover-tooltip c-btn-floating-right-bottom btn btn-primary" data-iframe="true" data-content="{\App\Language::translate('LBL_FULLSCREEN')}">
		<span class="mdi mdi-overscan"></span></a>
	{/if}
</div>
<!-- /tpl-Base-Detail-Widget-PDFViewer -->
{/strip}
