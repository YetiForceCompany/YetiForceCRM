{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="js-popover-tooltip" data-ellipsis="true" data-content="{if !isset($TITLE)}{App\Purifier::encodeHtml(App\Language::translate($WIDGET->getTitle(), $MODULE_NAME))}{else}{$TITLE}{/if}" data-toggle="popover" data-js="tooltip">
	<h5 class="h6 d-block p-1 m-0 js-popover-text">
		{if !isset($TITLE)}{App\Purifier::encodeHtml(App\Language::translate($WIDGET->getTitle(), $MODULE_NAME))}{else}{$TITLE}{/if}
	</h5>
	<span class="fas fa-info-circle fa-sm js-popover-icon d-none"></span>
</div>
