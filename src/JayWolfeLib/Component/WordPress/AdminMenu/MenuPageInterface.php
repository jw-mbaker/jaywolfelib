<?php

namespace JayWolfeLib\Component\WordPress\AdminMenu;

use JayWolfeLib\Component\HandlerInterface;

interface MenuPageInterface extends HandlerInterface
{
	public function slug(): Slug;
	public static function create(array $args): MenuPageInterface;
}