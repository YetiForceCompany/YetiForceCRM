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
{strip}
					<br>
					<footer class="noprint text-center">
						<div class="vtFooter">
							<p>
								{\App\Language::translate('POWEREDBY')} {$YETIFORCE_VERSION} &nbsp;
								&copy; 2004 - {date('Y')}&nbsp;&nbsp;
								<a href="https://yetiforce.com" target="_blank" rel="noreferrer">yetiforce.com</a>
								&nbsp;|&nbsp;
								<a href="#" onclick="window.open('../licenses/License.html', 'License', 'height=615,width=875').moveTo(110, 120)">{\App\Language::translate('LBL_READ_LICENSE')}</a>
							</p>
						</div>
					</footer>
					{include file='JSResources.tpl'}
				</div>
			</div>
		</body>
	</html>
{/strip}
