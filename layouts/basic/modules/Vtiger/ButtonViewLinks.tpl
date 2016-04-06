{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{if count($LINKS) gt 0}
		{assign var=REQUEST_URI value=Vtiger_Functions::getBrowserInfo()->requestUri}
		{assign var=TEXT_HOLDER value=''}
		{assign var=REQUEST_URI_PARAMS value=Vtiger_Functions::getQueryParams($REQUEST_URI)}
		{foreach item=LINK from=$LINKS}
			{assign var=LINK_PARAMS value=Vtiger_Functions::getQueryParams($LINK->getUrl())}
			{if $REQUEST_URI_PARAMS['module'] == $LINK_PARAMS['module'] && $REQUEST_URI_PARAMS['view'] == $LINK_PARAMS['view']}
				{assign var=TEXT_HOLDER value=$LINK->getLabel()}
			{/if} 
		{/foreach}
		{if !$BTN_GROUP}<div class="btn-group buttonTextHolder {$CLASS}">{/if} 
			<button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
				<span class="glyphicon glyphicon-list" aria-hidden="true"></span>
				&nbsp;
				<span class="textHolder">{vtranslate($TEXT_HOLDER, $MODULE_NAME)}</span>
				&nbsp;<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
				{foreach item=LINK from=$LINKS}
					<li>
						<a class="quickLinks" href="{$LINK->getUrl()}">
							{vtranslate($LINK->getLabel(), $MODULE_NAME)}
						</a>
					</li>
				{/foreach}
			</ul>
			{if !$BTN_GROUP}</div>{/if} 
		{/if} 
	{/strip}
