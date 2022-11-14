<?php

namespace JayWolfeLib\Views;

interface ViewInterface
{
	public function render(string $template, array $args = [], ?string $template_path = null);
}