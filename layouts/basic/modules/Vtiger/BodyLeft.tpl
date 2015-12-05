{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="COMPANY_DETAILS" value=Vtiger_CompanyDetails_Model::getInstanceById()}
	{assign var="COMPANY_LOGO" value=$COMPANY_DETAILS->getLogo()}
	<div class="container-fluid userDetailsContainer">
		<div class="row">
			<div class="col-md-2 noSpaces">
				<a class="companyLogoContainer" href="index.php">
					<img class="img-responsive logo" src="{$COMPANY_LOGO->get('imagepath')}" title="{$COMPANY_LOGO->get('title')}" alt="{$COMPANY_LOGO->get('alt')}"/>
				</a>
			</div>
			<div class="col-md-10 userDetails">
				<div class="pull-right">
					<ul class="headerLink noSpaces">
						{foreach key=index item=obj from=$HEADER_LINKS}
							{if $obj->linktype == 'HEADERLINK'}
								{assign var="HREF" value='#'}
								{assign var="ICON_PATH" value=$obj->getIconPath()}
								{assign var="LINK" value=$obj->convertToNativeLink()}
								{assign var="GLYPHICON" value=$obj->getGlyphiconIcon()}
								{assign var="TITLE" value=$obj->getLabel()}
								{assign var="CHILD_LINKS" value=$obj->getChildLinks()}
								<li class="dropdown">
									{if !empty($LINK)}
										{assign var="HREF" value=$LINK}
									{/if}
									<a class="dropdown-toggle {$obj->getClassName()}" title="{vtranslate($TITLE,$MODULE)}" {if !empty($CHILD_LINKS)}data-toggle="dropdown"{/if} href="{$HREF}"
									   {if $obj->linkdata && is_array($obj->linkdata)}
										   {foreach item=DATA_VALUE key=DATA_NAME from=$obj->linkdata}
											   data-{$DATA_NAME}="{$DATA_VALUE}" 
										   {/foreach}
									   {/if}>
										{if $GLYPHICON}
											<span class="{$GLYPHICON}" aria-hidden="true"></span>
										{/if}
										{if $ICON_PATH}
											<img src="{$ICON_PATH}" alt="{vtranslate($TITLE,$MODULE)}" title="{vtranslate($TITLE,$MODULE)}" />
										{/if}
									</a>
									{if !empty($CHILD_LINKS)}
										<ul class="dropdown-menu pull-right">
											{foreach key=index item=obj from=$CHILD_LINKS}
												{if $obj->getLabel() eq NULL}
													<li class="divider"></li>
													{else}
														{assign var="id" value=$obj->getId()}
														{assign var="href" value=$obj->getUrl()}
														{assign var="label" value=$obj->getLabel()}
														{assign var="onclick" value=""}
														{if stripos($obj->getUrl(), 'javascript:') === 0}
															{assign var="onclick" value="onclick="|cat:$href}
															{assign var="href" value="javascript:;"}
														{/if}
													<li>
														<a target="{$obj->target}" id="menubar_item_right_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($label)}" {if $label=='Switch to old look'}switchLook{/if} href="{$href}" {$onclick}
														   {if $obj->linkdata && is_array($obj->linkdata)}
															   {foreach item=DATA_VALUE key=DATA_NAME from=$obj->linkdata}
																   data-{$DATA_NAME}="{$DATA_VALUE}" 
															   {/foreach}
														   {/if}>{vtranslate($label,$MODULE)}</a>
													</li>
												{/if}
											{/foreach}
										</ul>
									{/if}
								</li>
							{/if}
						{/foreach}
					</ul>
				</div>
				<div class="pull-left">
					<p class="noSpaces name">{$USER_MODEL->get('first_name')}&nbsp;</p>
					<p class="noSpaces name">{$USER_MODEL->get('last_name')}&nbsp;</p>
					<p class="companyName noSpaces">{$COMPANY_DETAILS->get('organizationname')}&nbsp;</p>
				</div>
			</div>
		</div>
	</div>
	<div class="menuContainer">
		{include file='Menu.tpl'|@vtemplate_path:$MODULE DEVICE=$DEVICE}
	</div>
{/strip}

