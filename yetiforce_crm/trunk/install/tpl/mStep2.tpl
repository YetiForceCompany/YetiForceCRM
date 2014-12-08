{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************************************************************/
-->*}
<div class="row-fluid main-container">
	<div class="inner-container">
		<h2>{vtranslate('LBL_MIGRATION_HEADER', 'Install')}</h2>
		<form class="form-horizontal" name="step2" method="post" action="Install.php">
			<input type=hidden name="mode" value="mStep3" />
			<input type=hidden name="lang" value="{$LANG}" />
			<div class="row-fluid">
				<div class="span12 inner-container">
					<p>{vtranslate('LBL_MIGRATION_DESC', 'Install')}</p>
					<div>
						<input type="checkbox" id="checkBox1" name="checkBox1" required /><div class="chkbox"></div> {vtranslate('LBL_HAVE_BACKUP_DATABASE', 'Install')}</a>
					</div>
					<br />
					<div>
						<input type="checkbox" id="checkBox2" name="checkBox2" required /><div class="chkbox"></div> {vtranslate('LBL_HAVE_BACKUP_FILES', 'Install')}</a>
					</div>
					<br />
					<div class="row-fluid">
						<div class="span2">{vtranslate('LBL_SYSTEM_FOR_MIGRATION', 'Install')}</div>
						<div class="span5">
							<select class="span5" name="system" id="old_version" required>
								<option value="" selected="">{vtranslate('LBL_SELECT', 'Install')}</option>
								{foreach key=KEY item=ITEM from=$SCHEMALISTS}
									<option value="{$KEY}">{$ITEM}</option>
								{/foreach}
							</select>
						</div>
					</div>
					<br />
					<div class="row-fluid">
						<div class="span2">{vtranslate('LBL_SOURCE_DIRECTORY', 'Install')}</div>
						<div class="span5"><input type="text" value="" name="source_directory" id="source_directory" class="span5" required placeholder="{vtranslate('LBL_EXAMPLE_DIRECTORY', 'Install')}: {$EXAMPLE_DIRECTORY}"/></div>		
					</div>
					<br />
					<div class="row-fluid">
						<div class="span2">{vtranslate('LBL_LOGIN', 'Install')}</div>
						<div class="span5"><input type="text" value="" name="username" id="username" class="span5" required /></div>
					</div>
					<br />
					<div class="row-fluid">
						<div class="span2">{vtranslate('LBL_PASSWORD', 'Install')}</div>
						<div class="span5"><input type="password" value="" name="password" id="password" class="span5" required /></div>
					</div>
					<br />
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<div class="button-container">
						<input id="agree" type="submit" class="btn btn-large btn-primary" value="{vtranslate('LBL_START_MIGRATION', 'Install')}"/>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>