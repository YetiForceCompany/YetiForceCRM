{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<!-- tpl-dashboards-WidgetHeaderTitle -->
{if !isset($TITLE)}
	{assign var=TITLE value=\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME, null, true, 'Dashboard')}
{/if}
<div class="js-popover-tooltip--ellipsis" title="{App\Purifier::encodeHtml($TITLE)}" data-toggle="popover" data-js="popover | mouseenter">
	<h5 class="h6 d-block p-1 m-0 js-popover-tooltip--ellipsis js-widget__header__title" title="{App\Purifier::encodeHtml($TITLE)}" data-toggle="popover" data-js="popover | text | mouseenter">
		{$TITLE}
	</h5>
	<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
</div>
<!-- /tpl-dashboards-WidgetHeaderTitle -->
