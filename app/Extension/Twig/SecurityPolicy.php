<?php
/**
 * The sandbox security policy.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Extension\Twig;

/**
 * The sandbox security policy instance.
 * This class allows you to white-list some tags, filters, properties, and methods.
 */
class SecurityPolicy
{
	/** @var string[] Allowed tags */
	private $allowedTags = ['if', 'for', 'set'];
	/** @var string[] Allowed filters */
	private $allowedFilters = ['escape', 'lower', 'upper', 'date'];
	/** @var string[] Allowed methods */
	private $allowedMethods = [];
	/** @var string[] Allowed properties */
	private $allowedProperties = [];
	/** @var string[] Allowed functions */
	private $allowedFunctions = ['YFParser'];

	/**
	 * Gets the sandbox security policy.
	 *
	 * @return \Twig\Sandbox\SecurityPolicy
	 */
	public static function getPolicy(): \Twig\Sandbox\SecurityPolicy
	{
		$instance = new self();
		return new \Twig\Sandbox\SecurityPolicy($instance->allowedTags, $instance->allowedFilters, $instance->allowedMethods, $instance->allowedProperties, $instance->allowedFunctions);
	}
}
