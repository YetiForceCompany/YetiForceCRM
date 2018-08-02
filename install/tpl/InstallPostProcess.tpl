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
						<div class="vtFooter mb-5 pb-5 pb-sm-0">
							<p>
								{\App\Language::translate('POWEREDBY')} {$YETIFORCE_VERSION} &nbsp;
								&copy; 2004 - {date('Y')}&nbsp;&nbsp;
								<em><a href="https://yetiforce.com" target="_blank" rel="noreferrer">yetiforce.com</a></em>
							</p>
						</div>
					</footer>
					{include file='JSResources.tpl'}
				</div>
			</div>
		</body>
	</html>
{/strip}
