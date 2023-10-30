{*<!-- {[The file is published on the basis of YetiForce Public License 6.5 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-WidgetsManagement-WidgetConfig -->
	{assign var=LINKID value=$WIDGET_MODEL->get('linkid')}
	{assign var=LINK_LABEL_KEY value=$WIDGET_MODEL->get('linklabel')}
	{assign var=ALERT value=$LINK_LABEL_KEY === 'LBL_UPDATES' && !\App\YetiForce\Shop::check('YetiForceWidgets')}
	<li class="col-md-12">
		<div class="opacity editFieldsWidget ml-0 border1px {if $ALERT}bg-color-red-100{/if}" data-block-id="{$AUTHORIZATION_KEY}"
			data-field-id="{$WIDGET_MODEL->get('id')}" data-linkid="{$LINKID}" data-sequence="">
			<div class="row py-1 justify-content-between">
				<div class="col-9 text-truncate">
					{if $ALERT}
						<span class="fieldLabel ml-3" title="{\App\Language::translate('LBL_PAID_FUNCTIONALITY', 'Settings::YetiForce')}">{$WIDGET_MODEL->getTranslatedTitle()}</span>
						<a class="btn btn-sm" href="index.php?parent=Settings&module=YetiForce&view=Shop&product=YetiForceWidgets&mode=showProductModal" title="{\App\Language::translate('LBL_PAID_FUNCTIONALITY', 'Settings::YetiForce')}">
							<span class="yfi-premium color-red-600"></span></a>
					{else}
						<span class="fieldLabel ml-3">{$WIDGET_MODEL->getTranslatedTitle()}</span>
					{/if}
				</div>
				<div class="actions col-3">
					<div class="float-right pr-1">
						{foreach item=LINK from=$WIDGET_MODEL->getSettingsLinks()}
							{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $QUALIFIED_MODULE) BUTTON_VIEW='' BTN_CLASS=""}
						{/foreach}
					</div>
				</div>
				</span>
			</div>
		</div>
	</li>
	<!-- /tpl-Settings-WidgetsManagement-WidgetConfig -->
{/strip}
