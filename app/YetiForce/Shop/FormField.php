<?php
/**
 * YetiForce shop form field file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Klaudia Łozowska <k.lozowska@yetiforce.com>
 */

namespace App\YetiForce\Shop;

/**
 * YetiForce shop form field class.
 */
class FormField
{
	/**
	 * Name
	 *
	 * @var string
	 */
	private string $name;

	/**
	 * Label
	 *
	 * @var ?string
	 */
	private ?string $label;

	/**
	 * Type
	 *
	 * @var string
	 */
	private string $type;

	/**
	 * Field attributes
	 *
	 * @var array
	 */
	private array $attributes = [];

	/**
	 * Construct
	 *
	 * @param string $name
	 * @param ?string $label
	 * @param string $type
	 */
	public function __construct(string $name, ?string $label, string $type)
	{
		$this->name = $name;
		$this->label = $label;
		$this->type = $type;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Get label
	 *
	 * @return ?string
	 */
	public function getLabel(): ?string
	{
		return $this->label;
	}

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * Get field attributes
	 *
	 * @return array
	 */
	public function getAttributes(): array
	{
		return $this->attributes;
	}

	/**
	 * Add field attribute
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return void
	 */
	public function addAttribute(string $name, string $value): void
	{
		$this->attributes[$name] = $value;
	}
}
