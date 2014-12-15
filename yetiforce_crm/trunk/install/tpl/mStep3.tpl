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
{if $ERRORTEXT neq ''}
	<div class="row-fluid main-container">
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
				<a class="btn btn-large btn-primary" href="../index.php" >{vtranslate('LBL_BACK','Install')}</a> 
			</div>
		</div>
	</div>
{else}
	<input type=hidden id="step" value="m3" />
	<input type=hidden name="migrationURL" value="{$MIGRATIONURL}" />
	<div class="row-fluid main-container">
		<div class="inner-container">
			<div class="startContainer">
				<h2>{vtranslate('LBL_MIGRATION_SYSTEM', 'Install')}</h2><br />
				<div class="row-fluid">
					<div class="span12">
						<div class="progress_info" style="text-align: center;">0%</div>
					</div>
				</div>
				<div class="row-fluid">
					<div class="span12">
						<div class="progress progress-striped active">
							<div class="bar" style="width: 0%;"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="endContainer hide">
				<div class="inner-container">
					<h2>{vtranslate('LBL_MIGRATION_SYSTEM_FINISH', 'Install')}</h2><br />
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<div class="logs well" style="overflow:scroll; height:350px;"></div>
				</div>
			</div>
			<div class="row-fluid endContainer hide">
				<div class="span12">
					<div class="button-container">
						<a href="../index.php" class="btn btn-large btn-primary">
							{vtranslate('LBL_END_MIGRATION','Install')}
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
{/if}