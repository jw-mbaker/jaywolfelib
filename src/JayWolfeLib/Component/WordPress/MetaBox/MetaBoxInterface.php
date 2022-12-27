<?php

namespace JayWolfeLib\Component\WordPress\MetaBox;

use JayWolfeLib\Component\HandlerInterface;
use JayWolfeLib\Component\ParameterInterface;
use JayWolfeLib\Component\ObjectHash\ObjectHashInterface;

interface MetaBoxInterface extends
	HandlerInterface,
	ParameterInterface,
	ObjectHashInterface
{
	public function meta_id(): string;
	public function title(): string;
}