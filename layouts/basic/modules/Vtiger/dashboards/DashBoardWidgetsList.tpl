{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<button class="js-widget-predefined btn btn-outline-secondary c-btn-block-xs-down addButton dropdown-toggle u-remove-dropdown-icon{if !$WIDGETS|count gt 0} d-none{/if} tpl-Base-dashboards-DashBoardWidgetsList"
			data-js="class: d-none" data-toggle="dropdown">
				<span class="fa-layers fa-fw mr-md-2">
					<span class="fas fa-chart-pie" data-fa-transform="shrink-5 up-6"></span>
					<span class="fas fa-chart-line" data-fa-transform="shrink-5 right-7 down-6"></span>
					<span class="fas fa-chart-area" data-fa-transform="shrink-5 left-7 down-6"></span>
				</span>
		<span class="d-none d-md-inline">{\App\Language::translate('LBL_PREDEFINED_WIDGETS')}</span>
	</button>
	<ul class="js-widget-list dropdown-menu widgetsList addWidgetDropDown" data-js="container">
		{assign var="WIDGET" value=""}
		{foreach from=$WIDGETS item=WIDGET}
			<li class="js-widget-list__item dropdown-item d-flex flex-row-reverse align-items-center justify-content-between"
				data-js="remove">
				{if $WIDGET->get('deleteFromList')}
					<button data-widget-id="{$WIDGET->get('widgetid')}"
							class="removeWidgetFromList btn btn-danger btn-sm m-1 p-1">
						<span class='fas fa-trash-alt'></span>
					</button>
				{/if}
				<a class="pl-1"
				   onclick="Vtiger_DashBoard_Js.addWidget(this, '{$WIDGET->getUrl()}')"
				   href="#"
				   data-linkid="{$WIDGET->get('linkid')}" data-name="{$WIDGET->getName()}"
				   data-width="{$WIDGET->getWidth()}" data-height="{$WIDGET->getHeight()}"
				   data-id="{$WIDGET->get('widgetid')}">
					{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}
				</a>
			</li>
		{/foreach}
	</ul>
{/strip}
