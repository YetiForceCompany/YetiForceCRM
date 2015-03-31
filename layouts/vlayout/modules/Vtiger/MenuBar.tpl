{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}



<style>
	ul.nav.modulesList > li > ul {
		display:none;
	}

	ul.nav.modulesList > li:hover > ul {
		display:block;
	}
	.navbar .nav > li > a.OSSMenuBlock, .dropdown-menu li a:hover{
		color: #ffffff !important;
	}
	.navbar .nav > li > a.OSSMenuBlock{
		font-weight: 800;
	}
	.scrollable-menu {
		height: 550%;
		max-height: 600px;
		overflow-x: hidden;
	}
	.menuVSpace { margin-top: 10px; }
	.clipText { overflow:hidden; text-overflow:ellipsis; }

	#headerLinks{
		margin-left: 0;
	}
	@media(max-width: 960px){
		#dropdown-headerLinksBig{
			margin-right: 15px !important;
		}
		.mainContainer{
			margin-top: 125px !important;
		}
		.commonActionsContainer .actionsContainer{
			height: 94px !important;
		}
	}
	@media (max-width: 1160px) {
		.btn-navbar {
			display: block !important;
		}
		#headerLinksBig{
			display: none !important;
		}
		.headerLinksAJAXChat{
			margin-top: 6px !important;
		}
	}
	@media screen and (max-width: 1000px){
		#companyLogo-container{
			display: block !important;
		}
	}
	#dropdown-headerLinksBig{
		margin-right: 5px;
		position: absolute;
		top: 7px;
		right: 70px;
	}
	#OSSMail-more-other a,
	.chat-other{
		padding-left: 0 !important;
	}
	.chat-other{
		color:black !important;
	}
	#headerLinksCompact .qCreate{
		margin-top: 5px !important;

	}

</style>

{strip}
	{assign var="topMenus" value=$MENU_STRUCTURE['structure']}
	{assign var="icons" value=$MENU_STRUCTURE['icons']}
	{*{var_dump($topMenus)} {exit;}*}
	<div class="navbar" id="topMenus">
		<div class="navbar-inner" id="nav-inner">
			<div class="menuBar row-fluid">
				<div class="span7" id="largeNavDiv">
					<ul id="largeNav" class="nav modulesList collapsed">
						<li class="tabs">
							<a class="alignMiddle {if $MODULE eq 'Home'} selected {/if}" href="{$HOME_MODULE_MODEL->getDefaultUrl()}"><img src="{vimage_path('home.png')}" alt="{vtranslate('LBL_HOME',$moduleName)}" title="{vtranslate('LBL_HOME',$moduleName)}" /></a>
						</li>
						{foreach key=moduleName item=moduleModel from=$topMenus}

							<li class="dropdown hide blockli" id="{str_replace(' ', '_', $moduleName)}">
								<a class="dropdown-toggle OSSMenuBlock" data-toggle="dropdown" href="{$moduleName}">

									{if !empty($icons[$moduleName]['picon'])}
										<img style="max-width:{$icons[$moduleName]['iconf']}px; vertical-align: middle; max-height:{$icons[$moduleName]['icons']}px" src="{$icons[$moduleName]['picon']}" alt=""/>&nbsp;
									{/if}
									{vtranslate($moduleName, 'OSSMenuManager')}
								</a>
								{if count($moduleModel) gt 0}
									<ul class="dropdown-menu userName" style="max-height:700px; margin-top:0px;" >
										{foreach from=$moduleModel item=module}
											{if $module.link|strpos:'*etykieta*' === 0}
												{$module.link=$module.link|replace:'*etykieta*':''}
												<li>
													{if strlen($module.link) gt 0}
														{if $module.link|strpos:'*_blank*' === 0}
															<a class="menuLinkClass etykietaUrl moduleColor_{$module.mod}" href="{$module.link}" target="_blank">
																{if !empty($module.locationiconname)}
																	<img style="max-width: {$module.sizeicon_first}px; max-height:{$module.sizeicon_second}px; vertical-align: middle" src="{$module.locationiconname}" alt="{$module.locationiconname}"/>&nbsp;
																{/if}
																{vtranslate($module.name, $module.name)}</a>
														{else}
															<a class="menuLinkClass etykietaUrl moduleColor_{$module.mod}" href="{$module.link}">
																{if !empty($module.locationiconname)}
																	<img style="max-width: {$module.sizeicon_first}px; max-height:{$module.sizeicon_second}px; vertical-align: middle; color: grey;" src="{$module.locationiconname}" alt="{$module.locationiconname}"/>&nbsp;
																{/if}
																{vtranslate($module.name, $module.name)}</a>
														{/if}
													{else}
														<a class="menuLinkClass etykietaUrl moduleColor_{$module.mod}">
															{if !empty($module.locationiconname)}
																<img style="max-width: {$module.sizeicon_first}px; max-height:{$module.sizeicon_second}px; vertical-align: middle; color: grey;" src="{$module.locationiconname}" alt="{$module.locationiconname}"/>&nbsp;
															{/if}
															{vtranslate($module.name, $module.name)}</a>
													{/if}
												</li>
											{else if $module.link eq '*separator*'}
												<li class="divider"></li>
											{else if $module.link|strpos:"javascript:" === 0 || $module.link|strpos:"jQuery" === 0}
												<li>
													<a class="menuLinkClass moduleColor_{$module.mod}" {if $module.color}style="color: #{$module.color}!important;"{/if} href="#" onclick="{$module.link} return false;">
														{if !empty($module.locationiconname)}
															<img style="max-width: {$module.sizeicon_first}px; max-height:{$module.sizeicon_second}px; vertical-align: middle" src="{$module.locationiconname}" alt="{$module.locationiconname}"/>&nbsp;
														{/if}
														{vtranslate($module.name, $module.name)}</a></li>
											{else}
												{if $module.link|strpos:"*_blank*" === 0}
													{$module.link=$module.link|replace:'*_blank*':''}
													<li><a class="menuLinkClass moduleColor_{$module.mod}" {if $module.color}style="color: #{$module.color}!important;"{/if} href="{$module.link}" target="_blank">
															{if !empty($module.locationiconname)}
																<img style="max-width: {$module.sizeicon_first}px; max-height:{$module.sizeicon_second}px; vertical-align: middle" src="{$module.locationiconname}" alt="{$module.locationiconname}"/>&nbsp;
															{/if}
															{vtranslate($module.name, $module.name)}</a></li>
												{else if $module.link|strpos:"index" === 0 || $module.link|strpos:"http://" === 0 || $module.link|strpos:"https://" === 0 || $module.link|strpos:"www" === 0}

													<li><a class="menuLinkClass moduleColor_{$module.mod}" {if $module.color}style="color: #{$module.color}!important;"{/if} href="{$module.link}">
															{if !empty($module.locationiconname)}
																<img style="max-width: {$module.sizeicon_first}px; max-height:{$module.sizeicon_second}px; vertical-align: middle" src="{$module.locationiconname}" alt="{$module.locationiconname}"/>
															{/if}
															&nbsp;{vtranslate($module.name, $module.name)}</a></li>
												{/if}
											{/if}
										{/foreach}
									</ul>
								{/if}
							</li>
						{/foreach}
					</ul>
					<ul class="nav" id="commonMoreMenu">
						<li class="dropdown" id="moreMenu">
							<a class="dropdown-toggle" data-toggle="dropdown" href="#moreMenu">
								<strong>{vtranslate('LBL_OTHER', 'OSSMenuManager')}&nbsp;</strong>
								<b class="caret"></b>
							</a>
							<div class="dropdown-menu moreMenus scrollable-menu" style="width: 10em;">
								{foreach key=parent item=moduleList from=$topMenus name=more}
									{if $smarty.foreach.more.index % 1 == 0}
										<div class="row-fluid">
									{/if}
									{assign var=SPAN_CLASS value=span12}
									<span id="{str_replace(' ', '_', $parent)}_other" class="{$SPAN_CLASS} {if $smarty.foreach.more.index > 0} menuVSpace{/if} clipText">
										<strong>{vtranslate($parent, 'OSSMenuManager')}</strong><hr>
										{foreach key=moduleName item=moduleModel from=$moduleList}
											{if $moduleModel.link|strpos:'*etykieta*' === 0}
												{$moduleModel.link=$moduleModel.link|replace:'*etykieta*':''}
												<label class="moduleNames">
													{if strlen($moduleModel.link) gt 0}
														{if $moduleModel.link|strpos:'*_blank*' === 0}
															<a class="menuLinkClass etykietaUrl moduleColor_{$moduleModel.mod}" href="{$moduleModel.link}" target="_blank">
																{if !empty($moduleModel.locationiconname)}
																	<img style="max-width: {$moduleModel.sizeicon_first}px; max-height:{$moduleModel.sizeicon_second}px; vertical-align: middle" src="{$moduleModel.locationiconname}" alt="{$moduleModel.locationiconname}"/>&nbsp;
																{/if}
																{vtranslate($moduleModel.name, $moduleModel.name)}</a>
														{else}
															<a class="menuLinkClass etykietaUrl moduleColor_{$moduleModel.mod}" {if $moduleModel.color}style="color: #{$moduleModel.color}!important;"{/if} href="{$moduleModel.link}">
																{if !empty($moduleModel.locationiconname)}
																	<img style="max-width: {$moduleModel.sizeicon_first}px; max-height:{$moduleModel.sizeicon_second}px; vertical-align: middle" src="{$moduleModel.locationiconname}" alt="{$moduleModel.locationiconname}"/>&nbsp;
																{/if}
																{vtranslate($moduleModel.name, $moduleModel.name)}</a>
														{/if}
													{else}
														<a class="menuLinkClass etykietaUrl moduleColor_{$moduleModel.mod}" {if $moduleModel.color}style="color: #{$moduleModel.color}!important;"{/if}>
															{if !empty($moduleModel.locationiconname)}
																<img style="max-width: {$moduleModel.sizeicon_first}px; max-height:{$moduleModel.sizeicon_second}px; vertical-align: middle" src="{$moduleModel.locationiconname}" alt="{$moduleModel.locationiconname}"/>&nbsp;
															{/if}
															{vtranslate($moduleModel.name, $moduleModel.name)}</a>
													{/if}
												</label>
											{else if $moduleModel.link eq '*separator*'}
												<label class="divider"></label>
											{else if $moduleModel.link|strpos:"javascript:" === 0 || $moduleModel.link|strpos:"jQuery" === 0}
												<label class="moduleNames">
												<a class="menuLinkClass" href="#" onclick="{$moduleModel.link} return false;">
													{vtranslate($moduleModel.name, $moduleModel.name)}</a>
											</label>
											{else}
												{if $moduleModel.link|strpos:"*_blank*" === 0}
												{$moduleModel.link=$moduleModel.link|replace:'*_blank*':''}
												<label class="moduleNames">
													<a class="menuLinkClass moduleColor_{$moduleModel.mod}" {if $moduleModel.color}style="color: #{$moduleModel.color}!important;"{/if} href="{$moduleModel.link}" target="_blank">
														{if !empty($moduleModel.locationiconname)}
															<img style="max-width: {$moduleModel.sizeicon_first}px; max-height:{$moduleModel.sizeicon_second}px; vertical-align: middle" src="{$moduleModel.locationiconname}" alt="{$moduleModel.locationiconname}"/>&nbsp;
														{/if}
														{vtranslate($moduleModel.name, $moduleModel.name)}</a>
												</label>
												{else if $moduleModel.link|strpos:"index" === 0 || $moduleModel.link|strpos:"http://" === 0 || $moduleModel.link|strpos:"https://" === 0 || $moduleModel.link|strpos:"www" === 0}
													<label class="moduleNames">
													<a class="menuLinkClass moduleColor_{$moduleModel.mod}" {if $moduleModel.color}style="color: #{$moduleModel.color}!important;"{/if} href="{$moduleModel.link}">
														{if !empty($moduleModel.locationiconname)}
															<img style="max-width: {$moduleModel.sizeicon_first}px; max-height:{$moduleModel.sizeicon_second}px; vertical-align: middle" src="{$moduleModel.locationiconname}" alt="{$moduleModel.locationiconname}"/>
														{/if}
														&nbsp;{vtranslate($moduleModel.name, $moduleModel.name)}</a>
												</label>
											{/if}
											{/if}
										{/foreach}
									</span>
									{if $smarty.foreach.more.last OR ($smarty.foreach.more.index+1) % 1 == 0}
										</div>
									{/if}
								{/foreach}
									<div id="OSSMail-more" class="row-fluid">
										<span id="OSSMail-more-other">
											<hr>
											<a class="menuLinkClass" href="index.php?module=OSSMail&view=index"><strong>{vtranslate('LBL_MAIL', 'OSSMenuManager')} </strong></a>
										</span>
									</div>
									{if $WORKTIME}
									<div class="row-fluid">
										<span id="worktime-other">
											<hr>
											{$WORKTIME}
										</span>
									</div>	
									{/if}	
							</div>
						</li>
					</ul>
				</div>
				<div class="span5 row-fluid" id="headerLinks">
					<span id="headerLinksBig" class="pull-right headerLinksContainer">
						{if $PAINTEDICON eq 1}
							<span class="dropdown span settingIcons">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#">
									<img src="{vimage_path('theme_brush.png')}" alt="theme roller" title="Theme Roller" />
								</a>
								<ul class="dropdown-menu themeMenuContainer">
									<div id="themeContainer">
										{assign var=COUNTER value=0}
										{assign var=THEMES_LIST value=Vtiger_Theme::getAllSkins()}
										<div class="row-fluid themeMenu">
											{foreach key=SKIN_NAME item=SKIN_COLOR from=$THEMES_LIST}
											{if $COUNTER eq 3}
										</div>
										<div class="row-fluid themeMenu">
											{assign var=COUNTER value=1}
											{else}
											{assign var=COUNTER value=$COUNTER+1}
											{/if}
											<div class="span4 themeElement {if $USER_MODEL->get('theme') eq $SKIN_NAME}themeSelected{/if}" data-skin-name="{$SKIN_NAME}" title="{ucfirst($SKIN_NAME)}" style="background-color:{$SKIN_COLOR};"></div>
											{/foreach}
										</div>
									</div>
									<div id="progressDiv"></div>
								</ul>
							</span>
						{/if}
						{foreach key=index item=obj from=$HEADER_LINKS}
							{assign var="src" value=$obj->getIconPath()}
							{assign var="icon" value=$obj->getIcon()}
							{assign var="title" value=$obj->getLabel()}
							{assign var="childLinks" value=$obj->getChildLinks()}
							<span class="dropdown span{if !empty($src)} settingIcons {/if}">
								{if !empty($src)}
									<a id="menubar_item_right_{$title}" class="dropdown-toggle" data-toggle="dropdown" href="#"><img src="{$src}" alt="{vtranslate($title,$MODULE)}" title="{vtranslate($title,$MODULE)}" /></a>
									{else}
										{assign var=title value=$USER_MODEL->get('first_name')}
										{if empty($title)}
									{assign var=title value=$USER_MODEL->get('last_name')}
								{/if}
									<span class="dropdown-toggle" data-toggle="dropdown" href="#">
                                        <a id="menubar_item_right_{$title}"  class="userName textOverflowEllipsis" title="{$title}"><strong>{$title}</strong>&nbsp;<i class="caret"></i> </a> </span>
								{/if}
								{if !empty($childLinks)}
									<ul class="dropdown-menu pull-right">
										{foreach key=index item=obj from=$childLinks}
											{if $obj->getLabel() eq NULL}
												<li class="divider">&nbsp;</li>
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
													<a target="{$obj->target}" id="menubar_item_right_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($label)}" {if $label=='Switch to old look'}switchLook{/if} href="{$href}" {$onclick}>{vtranslate($label,$MODULE)}</a>
												</li>
											{/if}
										{/foreach}
									</ul>
								{/if}
							</span>
						{/foreach}
					</span>
					{if $CHAT_ACTIVE eq true}
						<span class="pull-right headerLinksContainer headerLinksAJAXChat">
							<span class="span">
								<a class="ChatIcon" href="#"><img src="layouts/vlayout/skins/images/chat.png" alt="chat_icon"/></a>
							</span>
						</span>
					{/if}
					<span class="pull-right headerLinksContainer headerLinksWorkTime" style="color: #ffffff;">
						<span class="span">
							{$WORKTIME}
						</span>
					</span>
					{assign var=CONFIG value=Settings_Mail_Config_Model::getConfig('mailIcon')}
					{assign var=AUTOLOGINUSERS value=OSSMail_Autologin_Model::getAutologinUsers()}
					{if $CONFIG['showMailIcon']=='true' && count($AUTOLOGINUSERS) > 0}
						<span class="pull-right headerLinksContainer headerLinksMails" id="OSSMailBoxInfo" {if $CONFIG['showNumberUnreadEmails']=='true'}data-numberunreademails="true" data-interval="{$CONFIG['timeCheckingMail']}"{/if} style="width: 270px;  margin-top: -5px;">
							<div class="btn-group pull-right" style="margin-top: 0;">
								{assign var=MAIN_MAIL value=OSSMail_Module_Model::getDefaultMailAccount($AUTOLOGINUSERS)}
								<a class="btn btn-small mainMail" href="index.php?module=OSSMail&view=index">{$MAIN_MAIL.username} <span class="noMails_{$MAIN_MAIL.rcuser_id}"></span></a>
								{if $CONFIG['showMailAccounts']=='true'}
									<button class="btn btn-small dropdown-toggle" data-toggle="dropdown">
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu">
										{foreach key=KEY item=ITEM from=$AUTOLOGINUSERS}
											<li data-id="{$KEY}" {if $ITEM.active}selested{/if}><a href="#">{$ITEM.username} <span class="noMails"></span></a></li>
										{/foreach}
									</ul>
								{/if}
							</div>
						</span>
					{/if}
					<div id="headerLinksCompact">
						<span class="btn-group dropdown qCreate cursorPointer">
							<img style="float:right;" src="{vimage_path('btnAdd_white.png')}" class="" alt="{vtranslate('LBL_QUICK_CREATE',$MODULE)}" title="{vtranslate('LBL_QUICK_CREATE',$MODULE)}" data-toggle="dropdown"/>
							<ul class="dropdown-menu dropdownStyles pull-right commonActionsButtonDropDown">
								<li class="title"><strong>{vtranslate('Quick Create',$MODULE)}</strong></li><hr/>
								<li id="compactquickCreate">
									<div class="CompactQC">
										{foreach key=moduleName item=moduleModel from=$MENUS}
											{if $moduleModel->isPermitted('EditView')}
												{assign var='quickCreateModule' value=$moduleModel->isQuickCreateSupported()}
												{assign var='singularLabel' value=$moduleModel->getSingularLabelKey()}
												{if $quickCreateModule == '1'}
													<a class="quickCreateModule" data-name="{$moduleModel->getName()}"
													   data-url="{$moduleModel->getQuickCreateUrl()}" href="javascript:void(0)">{vtranslate($singularLabel,$moduleName)}</a>
												{/if}
											{/if}
										{/foreach}
									</div>
								</li>
							</ul>
						</span>
						<span id="dropdown-headerLinksBig" class="dropdown">
							<a class="dropdown-toggle btn-navbar" data-toggle="dropdown" href="#">
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</a>
							<ul class="dropdown-menu pull-right">
								{foreach key=index item=obj from=$HEADER_LINKS name="compactIndex"}
									{assign var="src" value=$obj->getIconPath()}
									{assign var="icon" value=$obj->getIcon()}
									{assign var="title" value=$obj->getLabel()}
									{assign var="childLinks" value=$obj->getChildLinks()}
									{if $smarty.foreach.compactIndex.index neq 0}
										<li class="divider">&nbsp;</li>
									{/if}
									{foreach key=index item=obj from=$childLinks}
										{assign var="id" value=$obj->getId()}
										{assign var="href" value=$obj->getUrl()}
										{assign var="label" value=$obj->getLabel()}
										{assign var="onclick" value=""}
										{if stripos($obj->getUrl(), 'javascript:') === 0}
											{assign var="onclick" value="onclick="|cat:$href}
											{assign var="href" value="javascript:;"}
										{/if}
										<li>
											<a target="{$obj->target}" id="menubar_item_right_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($label)}" {if $label=='Switch to old look'}switchLook{/if} href="{$href}" {$onclick}>{vtranslate($label,$MODULE)}</a>
										</li>

									{/foreach}

								{/foreach}
							</ul>
						</span>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	{assign var="announcement" value=$ANNOUNCEMENT->get('announcement')}
	<div class="announcement" id="announcement">
		<marquee direction="left" scrolldelay="10" scrollamount="3" behavior="scroll" class="marStyle" onmouseover="javascript:stop();" onmouseout="javascript:start();">{if isset($announcement)}{$announcement}{else}{vtranslate('LBL_NO_ANNOUNCEMENTS',$MODULE)}{/if}</marquee>
	</div>
	<input type='hidden' value="{$MODULE}" id='module' name='module'/>
	<input type="hidden" value="{$PARENT_MODULE}" id="parent" name='parent' />
	<input type='hidden' value="{$VIEW}" id='view' name='view'/>
{literal}
	<script>
		jQuery( function() {
			jQuery( ".OSSMenuBlock" ).hover(
					function() {
						jQuery(this).dropdown('toggle');
					}, function() {
						jQuery(this).dropdown('toggle');
					}
			);

			jQuery('#commonMoreMenu').hide();
			refreshMenu();
		});

		jQuery( window ).resize(function() {
			refreshMenu();
		});

		function refreshMenu() {
			var largeNav = jQuery( '#largeNavDiv' ).width();
			var tabsWidth = jQuery( 'li.tabs' ).width() + 15;
			var windowWidth = jQuery(window).width();
			jQuery('#largeNavDiv').find('li.blockli').each( function() {
				jQuery(this).hide();
			});
			jQuery('#commonMoreMenu').show();
			jQuery('#commonMoreMenu').find('span').each( function() {
				jQuery(this).show();
			});

			var elemendthWidth = tabsWidth + jQuery('#commonMoreMenu').width();
			if ( windowWidth > 400 ) {
				jQuery('#largeNavDiv').find('li.blockli').each( function() {
					var eWidth = jQuery(this).width();
					if ( elemendthWidth + eWidth < largeNav-60 ) {
						elemendthWidth += eWidth;
						jQuery(this).show();
						var id = jQuery(this).attr('id');
						jQuery('#commonMoreMenu').find('[id="'+id+'_other"]').hide();
					}
				});
			}
			if(windowWidth < 1715){
				$('.headerLinksWorkTime').hide();
				$('#worktime-other').show();
			}
			else{
				$('.headerLinksWorkTime').show();
				$('#worktime-other').hide();
			}
			if(windowWidth < 1340){
				
				$('#OSSMailBoxInfo').hide();
				$('#OSSMail-more-other').show();
			}else{
				$('#OSSMailBoxInfo').show();
				$('#OSSMail-more-other').hide();
				
			}
			
			if(windowWidth <= 1160){
				$('#chat-more-other').show();
				$('#menubar_quickCreate').hide();
			}
			else{
				$('#chat-more-other').hide();
				$('#menubar_quickCreate').show();
				$('.headerLinksAJAXChat').show();

			}
			if(windowWidth <= 1025)
				$('#headerLinksBig').hide();
			else
				$('#headerLinksBig').show();

			var visibleSpans = 0;
			jQuery('#commonMoreMenu').find('span').each( function() {
				if ( jQuery(this).css('display') !== 'none' )
					visibleSpans++;
			});

			if ( visibleSpans == 0 )
				jQuery('#commonMoreMenu').hide();
		};
	</script>
{/literal}
{/strip}