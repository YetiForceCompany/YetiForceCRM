<?php
/**
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */

/**
 * Abstract class for connection to bank currency exchange rates.
 */
abstract class Settings_CurrencyUpdate_AbstractBank_Model
{
	// Returns bank name

	abstract public function getName();

	// Returns url sources from where exchange rates are taken from

	abstract public function getSource();

	// Returns list of currencies supported by this bank

	abstract public function getSupportedCurrencies();

	// Returns banks main currency

	abstract public function getMainCurrencyCode();

	/**
	 * Fetch exchange rates.
	 *
	 * @param <Array> $currencies - list of systems active currencies
	 * @param <Date>  $date       - date for which exchange is fetched
	 * @param bool    $cron       - if true then it is fired by server and crms currency conversion rates are updated
	 */
	abstract public function getRates($currencies, $date, $cron = false);
}
