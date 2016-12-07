{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="widget_header row ">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{\App\Language::translate('LBL_'|cat:$MODULE|upper|cat:'_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
	</div>
	<div class="form-horizontal">
		<input type="hidden" id="record" name="record" value="{$RECORD_MODEL->getId()}" />
		<ul class="nav nav-tabs" id="myTab">
			{foreach from=$RECORD_MODEL->getEditFields() item=LABEL key=FIELD_NAME name=tabs}
				<li class="{if $smarty.foreach.tabs.first}active{/if}"><a data-toggle="tab" href="#{$FIELD_NAME}">{\App\Language::translate($LABEL, $QUALIFIED_MODULE)}</a></li>
				{/foreach}
		</ul>
		<div class="tab-content">
			{foreach from=$RECORD_MODEL->getEditFields() item=LABEL key=FIELD_NAME name=tabs}
				<div id="{$FIELD_NAME}" data-url="{$RECORD_MODEL->getEditViewTabUrl($FIELD_NAME)}" class="tab-pane fade in{if $smarty.foreach.tabs.first} active{/if}{if !$RECORD_MODEL->isRefreshTab($FIELD_NAME)} noRefresh{/if}">
					{include file=vtemplate_path('Tab.tpl', $QUALIFIED_MODULE)}
				</div>
			{/foreach}
		</div>
	</div>
{/strip}
