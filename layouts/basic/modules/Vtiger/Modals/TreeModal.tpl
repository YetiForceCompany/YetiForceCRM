{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Modals-TreeModal modal-body js-modal-body" id="treePopupContainer" data-js="container">
		<input type="hidden" class="js-multiple" value="{$IS_MULTIPLE}" data-js="value" />
		<input type="hidden" class="js-tree-value" value="{App\Purifier::encodeHtml($TREE)}" data-js="value" />
		<div class="js-tree-contents" data-js="container"></div>
	</div>
{/strip}
