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
	{if $ERRORTEXT neq ''}
		<div class="row main-container">
			<div class="inner-container">
				<div>
					<h3>{vtranslate('LBL_MIGRATION_ERROR', 'Install')}</h3>
				</div>
				<div>
					<h5>{vtranslate($ERRORTEXT, 'Install')}</h5>
				</div>
			</div>
			<div class="inner-container">
				<div>
					<a class="btn btn-sm btn-primary" href="../index.php" >{vtranslate('LBL_BACK','Install')}</a> 
				</div>
			</div>
		</div>
	{/if}
{/strip}
