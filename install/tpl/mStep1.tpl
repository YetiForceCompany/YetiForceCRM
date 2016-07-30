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
{strip}
	<div class="row main-container">
		<div class="inner-container">
			<h2>{vtranslate('LBL_MIGRATION_HEADER', 'Install')}</h2>
			<form class="form-horizontal" name="step1" method="post" action="Install.php">
				<input type="hidden" name="mode" value="mStep2" />
				<input type="hidden" name="lang" value="{$LANG}" />
				<div class="row">
					<div>
						<div class="col-md-10 inner-container">
							<p>{vtranslate('LBL_DESCRIPTION_CONDITIONS', 'Install')}</p>
						</div>
						<div class="pull-right col-md-2">
							<input type="checkbox" id="checkBox3" name="checkBox3"  required /><div class="chkbox"></div> {vtranslate('LBL_ACCEPT', 'Install')}</a>
						</div>
						<div class="clearfix"></div><hr /><br />
					</div>
				</div>
				<div class="row">
					<div>
						<div class="button-container">
							<input id="agree" type="submit" class="btn btn-sm btn-primary" value="{vtranslate('LBL_NEXT', 'Install')}"/>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}
