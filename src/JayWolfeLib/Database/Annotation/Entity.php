<?php declare(strict_types=1);

namespace JayWolfeLib\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 * @NamedArgumentConstructor
 */
final class Entity
{
	public ?string $repositoryClass;

	public function __construct(?string $repositoryClass = null)
	{
		$this->repositoryClass = $repositoryClass;
	}
}