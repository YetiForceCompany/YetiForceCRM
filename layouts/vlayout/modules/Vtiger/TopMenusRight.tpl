{strip}
	{if $PAINTEDICON eq 1}
		<ul class="settingIcons nav navbar-nav navbar-right">
			<li class="dropdown">
				<a data-toggle="dropdown" class="dropdown-toggle" href="#">
					<img src="{vimage_path('theme_brush.png')}" alt="theme roller" title="Theme Roller" />
				</a>
				<ul class="dropdown-menu themeMenuContainer">
					<div id="themeContainer">
						{assign var=COUNTER value=0}
						{assign var=THEMES_LIST value=Vtiger_Theme::getAllSkins()}
						<div class="row themeMenu">
							{foreach key=SKIN_NAME item=SKIN_COLOR from=$THEMES_LIST}
								{if $COUNTER eq 3}
								</div>
								<div class="row themeMenu">
									{assign var=COUNTER value=1}
								{else}
									{assign var=COUNTER value=$COUNTER+1}
								{/if}
								<div class="col-md-4 themeElement {if $USER_MODEL->get('theme') eq $SKIN_NAME}themeSelected{/if}" data-skin-name="{$SKIN_NAME}" title="{ucfirst($SKIN_NAME)}" style="background-color:{$SKIN_COLOR};"></div>
							{/foreach}
						</div>
					</div>
					<div id="progressDiv"></div>
				</ul>
			</li>
		</ul>
	{/if}
	<ul class="settingIcons nav navbar-nav navbar-right">
		{if $USER_MODEL->isAdminUser()}
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#" title="{vtranslate('LBL_SYSTEM_SETTINGS',$MODULE)}">				
					<img src="layouts/vlayout/skins/twilight/images/setting.png" alt="{vtranslate('LBL_SYSTEM_SETTINGS',$MODULE)}" title="{vtranslate('LBL_SYSTEM_SETTINGS',$MODULE)}" />
				</a>
				<ul class="dropdown-menu pull-right">
					<li>
						<a target="" id="menubar_item_right_LBL_SYSTEM_SETTINGS"  href="?module=Vtiger&amp;parent=Settings&amp;view=Index" >
							{vtranslate('LBL_SYSTEM_SETTINGS',$MODULE)}
						</a>
					</li>
					<li>
						<a target="" id="menubar_item_right_LBL_MANAGE_USERS"  href="?module=Users&amp;parent=Settings&amp;view=List" >
							{vtranslate('LBL_MANAGE_USERS',$MODULE)}
						</a>
					</li>
				</ul>
			</li>
		{/if}
		<li class="dropdown">
			<a class="dropdown-toggle" data-toggle="dropdown" href="#" title="{vtranslate('LBL_SYSTEM_SETTINGS',$MODULE)}">				
				{$USER_MODEL->getDisplayName()}
			</a>
			<ul class="dropdown-menu pull-right">
				<li>
					<a target="" id="menubar_item_right_LBL_SIGN_OUT"  href="?module=Users&amp;parent=Settings&amp;action=Logout" >						
						{vtranslate('LBL_SIGN_OUT',$MODULE)}
					</a>
				</li>
			</ul>
		<li>
	</ul>
	{foreach key=index item=obj from=$HEADER_LINKS}
		{assign var="TITLE" value=$obj->getLabel()}
		{if $obj->linktype == 'HEADERLINK' && !in_array($TITLE,['LBL_SYSTEM_SETTINGS','LBL_SIGN_OUT'])}
			{assign var="HREF" value='#'}
			{assign var="ICON_PATH" value=$obj->getIconPath()}
			{assign var="LINK" value=$obj->convertToNativeLink()}
			{assign var="GLYPHICON" value=$obj->getGlyphiconIcon()}
			{if stripos($obj->getUrl(), 'javascript:') === 0}
				{assign var="onclick" value="onclick="|cat:$href}
				{assign var="href" value="javascript:;"}
			{/if}
			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown">
					{if !empty($LINK)}
						{assign var="HREF" value=$LINK}
					{/if}
					<a class="dropdown-toggle btn {$obj->getClassName()}" title="{vtranslate($TITLE,$MODULE)}" href="{$HREF}"
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
				</li>
			</ul>
		{/if}
	{/foreach}
	<ul class="headerLinksContainer nav navbar-nav navbar-right">
		<li>
			<div class="remindersNotice">
				<span class="glyphicon glyphicon-bell" aria-hidden="true"></span>
				<span class="badge hide">0</span>
			</div>
		</li>
	</ul>
	{if $CHAT_ACTIVE eq true}
		<ul class="headerLinksContainer headerLinksAJAXChat nav navbar-nav navbar-right">
			<li>
				<a class="ChatIcon" href="#" title="{vtranslate('LBL_CHAT', $MODULE)}"><img src="layouts/vlayout/skins/images/chat.png" alt="chat_icon"/></a>
			</li>
		</ul>
	{/if}
	{assign var=CONFIG value=Settings_Mail_Config_Model::getConfig('mailIcon')}
	{assign var=AUTOLOGINUSERS value=OSSMail_Autologin_Model::getAutologinUsers()}
	{if $CONFIG['showMailIcon']=='true' && count($AUTOLOGINUSERS) > 0}
		{assign var=MAIN_MAIL value=OSSMail_Module_Model::getDefaultMailAccount($AUTOLOGINUSERS)}
		<div class="nav navbar-nav navbar-right headerLinksContainer headerLinksMails" id="OSSMailBoxInfo" {if $CONFIG['showNumberUnreadEmails']=='true'}data-numberunreademails="true" data-interval="{$CONFIG['timeCheckingMail']}"{/if}>
			<div class="btn-group">
				<a type="button" class="btn btn-sm btn-default" title="{$MAIN_MAIL.username}" href="index.php?module=OSSMail&view=index">
					{$ITEM.username}
					<span class="mail_user_name">{$MAIN_MAIL.username}</span>
					<span class="noMails_{$MAIN_MAIL.rcuser_id}"></span>
				</a>
				{if $CONFIG['showMailAccounts']=='true'}
					<button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span class="caret"></span>
						<span class="sr-only">Toggle Dropdown</span>
					</button>
					<ul class="dropdown-menu" role="menu">
						{foreach key=KEY item=ITEM from=$AUTOLOGINUSERS}
							<li data-id="{$KEY}" {if $ITEM.active}selested{/if}>
								<a href="#">
									{$ITEM.username} <span class="noMails"></span>
								</a>
							</li>
						{/foreach}
					</ul>
				{/if}
			</div>
		</div>
	{/if}		
{/strip}
