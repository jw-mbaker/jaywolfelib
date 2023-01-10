<?php declare(strict_types=1);

namespace JayWolfeLib\Annotation;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target("CLASS")
 */
final class Table
{
	public ?string $name;
	public ?string $schema;
	public ?array $indexes;
	public ?array $uniqueConstraints;
	public array $options = [];

	public function __construct(
		?string $name = null,
		?string $schema = null,
		?array $indexes = null,
		?array $uniqueConstraints = null,
		array $options = []
	) {
		$this->name = $name;
		$this->schema = $schema;
		$this->indexes = $indexes;
		$this->uniqueConstraints = $uniqueConstraints;
		$this->options = $options;
	}
}