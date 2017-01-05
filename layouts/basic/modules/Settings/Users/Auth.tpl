{*<!--
/*+***********************************************************************************************************************************
* The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
* in compliance with the License.
* Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
* See the License for the specific language governing rights and limitations under the License.
* The Original Code is YetiForce.
* The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
* All Rights Reserved.
*************************************************************************************************************************************/
-->*}
{strip}
	<div class=" usersAuth">
		<div class="widget_header row">
			<div class="col-md-12">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
				{vtranslate('LBL_AUTHORIZATION_DESCRIPTION', $QUALIFIED_MODULE)}
			</div>
		</div>
		<div>
			<div class="contents tabbable">
				<ul class="nav nav-tabs layoutTabs massEditTabs">
					<li class="active"><a data-toggle="tab" href="#ldap"><strong>{vtranslate('LBL_LDAP_AUTH', $QUALIFIED_MODULE)}</strong></a></li>
				</ul>
				<div class="tab-content layoutContent" style="padding-top: 10px;">
					{assign var=CONFIG value=$MODULE_MODEL->getConfig('ldap')}
					<div class="tab-pane active" id="ldap">
						<div class="alert alert-info alert-dismissible col-md-5" role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
							</button>
							<p>{vtranslate('LBL_LDAP_RECOMMENDED_INFO', $QUALIFIED_MODULE)}</p>
						</div>
						<div class="clearfix"></div>
						<div class="row">
							<div class="col-md-1 col-sm-1 col-xs-2 pagination-centered">
								<input class="configField" type="checkbox" name="active" id="ldapActive" data-type="ldap" value="1" {if $CONFIG['active']=='true'}checked=""{/if}>
							</div>
							<div class="col-md-11 col-sm-11 col-xs-10">
								<label for="ldapActive">{vtranslate('LBL_ACTIVE_LDAP_AUTH', $QUALIFIED_MODULE)}</label>
							</div>
						</div>
						<hr />
						<div class="row m">
							<div class="col-md-2">
								<label for="showMailIcon">{vtranslate('LBL_LDAP_SERVER', $QUALIFIED_MODULE)}</label>
							</div>
							<div class="col-md-8">
								<input class="configField form-control" title="{vtranslate('LBL_LDAP_SERVER', $QUALIFIED_MODULE)}" type="text" name="server" data-type="ldap" value="{$CONFIG['server']}">
							</div>
						</div>
						<div class="row paddingTop20">
							<div class="col-md-2">
								<label for="showMailIcon">{vtranslate('LBL_LDAP_DOMAIN', $QUALIFIED_MODULE)}</label>
							</div>
							<div class="col-md-8">
								<div class="input-group">
									<input class="configField form-control" title="{vtranslate('LBL_LDAP_DOMAIN', $QUALIFIED_MODULE)}" type="text" name="domain" data-type="ldap" value="{$CONFIG['domain']}">
									<span class="input-group-addon popoverTooltip" data-content="@testlab.local (DC=testlab,DC=local)" id="basic-addon2">
										<span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
									</span>
								</div>
							</div>
						</div>
						<div class="row paddingTop20">
							<div class="col-md-2">
								<label for="showMailIcon">{vtranslate('LBL_LDAP_PORT', $QUALIFIED_MODULE)}</label>
							</div>
							<div class="col-md-8">
								<input class="configField form-control" title="{vtranslate('LBL_LDAP_PORT', $QUALIFIED_MODULE)}" type="text" name="port" data-type="ldap" value="{$CONFIG['port']}">
							</div>
						</div>
						<div class="row paddingTop20">
							<div class="col-md-2">
								<label for="showMailIcon">{vtranslate('LBL_LDAP_USERS', $QUALIFIED_MODULE)}:</label>
							</div>
							<div class="col-md-8">
								<select multiple="" name="users" class="select2 configField form-control" data-type="ldap" style="width: 100%;">
									{foreach key=KEY item=USER from=App\Fields\Owner::getAllUsers()}
										<option value="{$USER['id']}" {if in_array($USER['id'], $CONFIG['users'])} selected {/if}>{$USER['fullName']}</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>
				</div>	
			</div>
		</div>
	</div>
{/strip}
