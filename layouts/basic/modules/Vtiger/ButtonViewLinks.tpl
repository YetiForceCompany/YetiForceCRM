{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if count($LINKS) gt 0}
		{assign var=TEXT_HOLDER value=''}
		{foreach item=LINK from=$LINKS}
			{assign var=LINK_PARAMS value=vtlib\Functions::getQueryParams($LINK->getUrl())}
			{if \App\Request::_getModule() == $LINK_PARAMS['module'] && \App\Request::_get('view') == $LINK_PARAMS['view']}
				{assign var=TEXT_HOLDER value=$LINK->getLabel()}
				{if $LINK->get('linkicon') neq ''}
					{assign var=BTN_ICON value=$LINK->get('linkicon')}
				{/if}
			{/if} 
		{/foreach}
		{if isset($BTN_GROUP) && !$BTN_GROUP}<div class="btn-group buttonTextHolder {if isset($CLASS)}{$CLASS}{/if}">{/if} 
			<button class="btn btn-light dropdown-toggle" data-toggle="dropdown">
				{if $BTN_ICON}
					<span class="{$BTN_ICON}" aria-hidden="true"></span>
				{else}	
					<span class="fas fa-list" aria-hidden="true"></span>
				{/if}
				&nbsp;
				<span class="textHolder">{\App\Language::translate($TEXT_HOLDER, $MODULE_NAME)}</span>
				&nbsp;<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
				{foreach item=LINK from=$LINKS}
					<li>
						<a class="quickLinks" href="{$LINK->getUrl()}">
							{if $LINK->get('linkicon') neq ''}
								<span class="{$LINK->get('linkicon')}"></span>&nbsp;&nbsp;
							{/if}
							{\App\Language::translate($LINK->getLabel(), $MODULE_NAME)}
						</a>
					</li>
				{/foreach}
			</ul>
			{if isset($BTN_GROUP) && !$BTN_GROUP}</div>{/if} 
		{/if} 
	{/strip}
