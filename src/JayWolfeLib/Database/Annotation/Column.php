<?php declare(strict_types=1);

namespace JayWolfeLib\Database\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class Column
{
	public ?string $name = null;
	public ?string $type = null;
	public ?int $length = null;
	public ?int $precision = null;
	public ?int $scale = null;
	public bool $unique = false;
	public bool $nullable = false;
	public bool $insertable = true;
	public bool $updatable = true;
	public ?string $enumType = null;
	public array $options = [];
	public ?string $columnDefinition = null;
	public ?string $generated = null;

	public function __construct(
		?string $name = null,
		?string $type = null,
		?int $length = null,
		?int $precision = null,
		?int $scale = null,
		bool $unique = false,
		bool $nullable = false,
		bool $insertable = true,
		bool $updatable = true,
		?string $enumType = null,
		array $options = [],
		?string $columnDefinition = null,
		?string $generated = null
	) {
		$this->name = $name;
		$this->type = $type;
		$this->length = $length;
		$this->precision = $precision;
		$this->scale = $scale;
		$this->unique = $unique;
		$this->nullable = $nullable;
		$this->insertable = $insertable;
		$this->updatable = $updatable;
		$this->enumType = $enumType;
		$this->options = $options;
		$this->columnDefinition = $columnDefinition;
		$this->generated = $generated;
	}
}