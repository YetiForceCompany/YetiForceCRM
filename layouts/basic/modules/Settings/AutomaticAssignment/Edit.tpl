{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Settings-AutomaticAssingment-Edit">
		<div class="widget_header row">
			<div class="col-12">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<div class="form-horizontal mt-2">
			<input type="hidden" id="record" name="record" value="{$RECORD_MODEL->getId()}"/>
			<ul class="nav nav-tabs" id="myTab" role="tablist">
				{foreach from=$RECORD_MODEL->getEditFields() item=LABEL key=FIELD_NAME name=tabs}
					<li class="nav-item">
						<a class="nav-link {if $smarty.foreach.tabs.first}active show{/if}"
						   data-toggle="tab" role="tab" href="#{$FIELD_NAME}"
						   aria-selected="{if $smarty.foreach.tabs.first}true{else}false{/if}"
						>{\App\Language::translate($LABEL, $QUALIFIED_MODULE)}</a>
					</li>
				{/foreach}
			</ul>
			<div class="tab-content">
				{foreach from=$RECORD_MODEL->getEditFields() item=LABEL key=FIELD_NAME name=tabs}
					<div id="{$FIELD_NAME}" data-url="{$RECORD_MODEL->getEditViewTabUrl($FIELD_NAME)}"
						 class="tab-pane fade show{if $smarty.foreach.tabs.first} active{/if}{if !$RECORD_MODEL->isRefreshTab($FIELD_NAME)} noRefresh{/if}">
						{include file=\App\Layout::getTemplatePath('Tab.tpl', $QUALIFIED_MODULE)}
					</div>
				{/foreach}
			</div>
		</div>
	</div>
{/strip}
