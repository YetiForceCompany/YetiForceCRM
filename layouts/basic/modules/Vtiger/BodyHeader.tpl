{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
    {assign var='count' value=0}
	<div class="container-fluid bodyHeader noSpaces commonActionsContainer{if $LEFTPANELHIDE} menuOpen{/if}">
		<div class="row noSpaces">
			<div class="rightHeader paddingRight10">
				<div class="pull-right rightHeaderBtn">
					{assign var=QUICKCREATE_MODULES value=Vtiger_Module_Model::getQuickCreateModules(true)}
					{if !empty($QUICKCREATE_MODULES)}
						<a class="btn btn-default btn-sm popoverTooltip dropdownMenu" data-content="{\App\Language::translate('LBL_QUICK_CREATE')}" href="#">
							<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
						</a>
						<ul class="dropdown-menu dropdown-menu-right commonActionsButtonDropDown">
							<li class="quickCreateModules">
								<div class="panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><strong>{\App\Language::translate('LBL_QUICK_CREATE')}</strong></h4>
									</div>
									<div class="panel-body paddingLRZero">
										{foreach key=NAME item=MODULEMODEL from=$QUICKCREATE_MODULES}
											{assign var='quickCreateModule' value=$MODULEMODEL->isQuickCreateSupported()}
											{assign var='singularLabel' value=$MODULEMODEL->getSingularLabelKey()}
											{if $singularLabel == 'SINGLE_Calendar'}
												{assign var='singularLabel' value='LBL_EVENT_OR_TASK'}
											{/if}	
											{if $quickCreateModule == '1'}
												{if $count % 3 == 0}
													<div class="">
													{/if}
													<div class="col-xs-4{if $count % 3 != 2} paddingRightZero{/if}">
														<a id="menubar_quickCreate_{$NAME}" class="quickCreateModule list-group-item" data-name="{$NAME}" data-url="{$MODULEMODEL->getQuickCreateUrl()}" href="javascript:void(0)" title="{vtranslate($singularLabel,$NAME)}">
															<span>{vtranslate($singularLabel,$NAME)}</span>
														</a>
													</div>
													{if $count % 3 == 2}
													</div>
												{/if}
												{assign var='count' value=$count+1}
											{/if}
										{/foreach}
										{if $count % 3 >= 1}
										</div>
									{/if}
								</div>
							</li>
						</ul>
					{/if}
					{if Users_Privileges_Model::isPermitted('Notification', 'DetailView')}
						<a class="btn btn-default btn-sm isBadge notificationsNotice popoverTooltip {if AppConfig::module('Home', 'AUTO_REFRESH_REMINDERS')}autoRefreshing{/if}" data-content="{\App\Language::translate('LBL_NOTIFICATIONS')}" href="index.php?module=Notification&view=List">
							<span class="glyphicon glyphicon-bell" aria-hidden="true"></span>
							<span class="badge hide">0</span>
						</a>
					{/if}
					{if $CHAT_ACTIVE}
						<a class="btn btn-default btn-sm headerLinkChat popoverTooltip" data-content="{\App\Language::translate('LBL_CHAT')}" href="#">
							<span class="glyphicon glyphicon-comment" aria-hidden="true"></span>
						</a>
					{/if}
					{if $REMINDER_ACTIVE}
						<a class="btn btn-default btn-sm isBadge remindersNotice popoverTooltip {if AppConfig::module('Calendar', 'AUTO_REFRESH_REMINDERS')}autoRefreshing{/if}" data-content="{\App\Language::translate('LBL_REMINDER')}" href="#">
							<span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
							<span class="badge bgDanger hide">0</span>
						</a>
					{/if}
					<a class="btn btn-default btn-sm showHistoryBtn popoverTooltip dropdownMenu" data-content="{vtranslate('LBL_PAGES_HISTORY')}" href="#">
						<i class="fa fa-history" aria-hidden="true"></i>
					</a>
					{foreach key=index item=obj from=$MENU_HEADER_LINKS}
						{if $obj->linktype == 'HEADERLINK'}
							{assign var="HREF" value='#'}
							{assign var="ICON_PATH" value=$obj->getIconPath()}
							{assign var="LINK" value=$obj->convertToNativeLink()}
							{assign var="GLYPHICON" value=$obj->getGlyphiconIcon()}
							{assign var="TITLE" value=$obj->getLabel()}
							{assign var="CHILD_LINKS" value=$obj->getChildLinks()}
							{if !empty($LINK)}
								{assign var="HREF" value=$LINK}
							{/if}
							<a class="btn btn-default btn-sm popoverTooltip {$obj->getClassName()} {if !empty($CHILD_LINKS)}dropdownMenu{/if}" data-content="{\App\Language::translate($TITLE)}" href="{$HREF}"
							   {if isset($obj->linkdata) && $obj->linkdata && is_array($obj->linkdata)}
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
								<ul class="dropdown-menu">
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
						{/if}
					{/foreach}
				</div>
				{if AppConfig::performance('GLOBAL_SEARCH')}
					<div class="pull-left selectSearch">
						<div class="input-group globalSearchInput">
							<span class="input-group-btn">
								<select class="chzn-select basicSearchModulesList form-control col-md-5" title="{\App\Language::translate('LBL_SEARCH_MODULE')}">
									<option value="">{\App\Language::translate('LBL_ALL_RECORDS')}</option>
									{foreach key=SEARCHABLE_MODULE item=fieldObject from=$SEARCHABLE_MODULES}
										{if isset($SEARCHED_MODULE) && $SEARCHED_MODULE eq $SEARCHABLE_MODULE && $SEARCHED_MODULE !== 'All'}
											<option value="{$SEARCHABLE_MODULE}" selected>{\App\Language::translate($SEARCHABLE_MODULE,$SEARCHABLE_MODULE)}</option>
										{else}
											<option value="{$SEARCHABLE_MODULE}">{\App\Language::translate($SEARCHABLE_MODULE,$SEARCHABLE_MODULE)}</option>
										{/if}
									{/foreach}
								</select>
							</span>
							<input type="text" class="form-control globalSearchValue" title="{\App\Language::translate('LBL_GLOBAL_SEARCH')}" placeholder="{\App\Language::translate('LBL_GLOBAL_SEARCH')}" results="10" data-operator="contains" />
							<span class="input-group-btn">
								<button class="btn btn-default searchIcon" type="button">
									<span class="glyphicon glyphicon-search"></span>
								</button>
								{if AppConfig::search('GLOBAL_SEARCH_OPERATOR')}
									<div class="btn-group">
										<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<span class="glyphicon glyphicon-screenshot"></span>
										</button>
										<ul class="dropdown-menu globalSearchOperator">
											<li class="active"><a href="#" data-operator="contains">{\App\Language::translate('contains')}</a></li>
											<li><a href="#" data-operator="starts">{\App\Language::translate('starts with')}</a></li>
											<li><a href="#" data-operator="ends">{\App\Language::translate('ends with')}</a></li>
										</ul>
									</div>
								{/if}
								<button class="btn btn-default globalSearch" title="{\App\Language::translate('LBL_ADVANCE_SEARCH')}" type="button">
									<span class="glyphicon glyphicon-th-large"></span>
								</button>
							</span>
						</div>
					</div>
				{/if}
				<div class="pull-right rightHeaderBtnMenu">
					<div class="quickAction">
						<a class="btn btn-default btn-sm" href="#">
							<span aria-hidden="true" class="glyphicon glyphicon-menu-hamburger"></span>
						</a>
					</div>
				</div>
				<div class="pull-right actionMenuBtn">
					<div class="quickAction">
						<a class="btn btn-default btn-sm" href="#">
							<span aria-hidden="true" class="glyphicon glyphicon-tasks"></span>
						</a>
					</div>
				</div>
				{if AppConfig::performance('GLOBAL_SEARCH')}
					<div class="pull-left searchMenuBtn">
						<div class="quickAction">
							<a class="btn btn-default btn-sm" href="#">
								<span aria-hidden="true" class="glyphicon glyphicon-search"></span>
							</a>
						</div>
					</div>
				{/if}
				{if !Settings_ModuleManager_Library_Model::checkLibrary('roundcube')}
					<div class="pull-right">
						{assign var=CONFIG value=Settings_Mail_Config_Model::getConfig('mailIcon')}
						{if $CONFIG['showMailIcon']=='true' && App\Privilege::isPermitted('OSSMail')}
							{assign var=AUTOLOGINUSERS value=OSSMail_Autologin_Model::getAutologinUsers()}
							{if count($AUTOLOGINUSERS) > 0}
								{assign var=MAIN_MAIL value=OSSMail_Module_Model::getDefaultMailAccount($AUTOLOGINUSERS)}
								<div class="headerLinksMails" id="OSSMailBoxInfo" {if $CONFIG['showNumberUnreadEmails']=='true'}data-numberunreademails="true" data-interval="{$CONFIG['timeCheckingMail']}"{/if}>
									<div class="btn-group">
										{if count($AUTOLOGINUSERS) eq 1}
											<a type="button" class="btn btn-sm btn-default" title="{$MAIN_MAIL.username}" href="index.php?module=OSSMail&view=index">
												<div class="hidden-xs">
													{$ITEM.username}
													<span class="mail_user_name">{$MAIN_MAIL.username}</span>
													<span data-id="{$MAIN_MAIL.rcuser_id}" class="noMails"></span>
												</div>
												<div class="visible-xs-block">
													<span class="glyphicon glyphicon-list-alt"></span>
												</div>
											</a>
										{elseif $CONFIG['showMailAccounts']=='true'}
											<select class="form-control" title="{vtranslate('LBL_SEARCH_MODULE', $MODULE_NAME)}">
												{foreach key=KEY item=ITEM from=$AUTOLOGINUSERS}
													<option value="{$KEY}" {if $ITEM.active}selected{/if} data-id="{$KEY}" data-nomail="" class="noMails">
														{$ITEM.username}
													</option>
												{/foreach}
											</select>
										{/if}
									</div>
								</div>
							{/if}
						{/if}
					</div>
				{/if}
			</div>
		</div>
	</div>
{/strip}
