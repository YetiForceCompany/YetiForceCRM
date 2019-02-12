{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<button class="js-widget-predefined btn btn-outline-secondary c-btn-block-xs-down addButton dropdown-toggle u-remove-dropdown-icon{if !$WIDGETS|count gt 0} d-none{/if} tpl-Base-dashboards-DashBoardWidgetsList"
			data-js="class: d-none" data-toggle="dropdown">
				<span class="c-icon--tripple">
					<span class="c-icon--tripple__top fas fa-chart-pie"></span>
					<span class="c-icon--tripple__left fas fa-chart-line"></span>
					<span class="c-icon--tripple__right fas fa-chart-area"></span>
				</span>
		<span class="d-none d-md-inline">{\App\Language::translate('LBL_PREDEFINED_WIDGETS')}</span>
	</button>
	<div class="js-widget-list dropdown-menu widgetsList addWidgetDropDown" data-js="container">
		{assign var="WIDGET" value=""}
		{foreach from=$WIDGETS item=WIDGET}
			<a class="js-widget-list__item dropdown-item d-flex"
			   data-js="remove | click" href="#" data-widget-url="{$WIDGET->getUrl()}" data-linkid="{$WIDGET->get('linkid')}" data-name="{$WIDGET->getName()}"
			   data-width="{$WIDGET->getWidth()}" data-height="{$WIDGET->getHeight()}"
			   data-id="{$WIDGET->get('widgetid')}">
				{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}
				{if $WIDGET->get('deleteFromList')}
					<span class="text-danger pl-5 ml-auto">
						<span class="fas fa-trash-alt removeWidgetFromList u-hover-opacity" data-widget-id="{$WIDGET->get('widgetid')}" data-js="click"></span>
					</span>
				{/if}
			</a>
		{/foreach}
	</div>
{/strip}
