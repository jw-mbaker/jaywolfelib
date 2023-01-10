<?php declare(strict_types=1);

namespace JayWolfeLib\Annotation;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class Manager
{
	private Reader $annotationReader;

	public function getAnnotationReader(): Reader
	{
		if (null === $this->annotationReader) {
			AnnotationRegistry::registerLoader('class_exists');
			$this->annotationReader = new SimpleAnnotationReader();
		}

		return $this->annotationReader;
	}
}