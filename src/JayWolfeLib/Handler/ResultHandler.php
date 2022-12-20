<?php

namespace JayWolfeLib\Handler;

class ResultHandler
{
	use HandlerTrait;

	public function handle(Result $result, \WP $wp, bool $parse_request): bool
	{
		$handler_result = $parse_request;

		if ($result->matched()) {
			$parse_request = false;
			$orig_handler = $result->handler();
			$handler = $this->build_handler($orig_handler, $result->dependencies());
			$template = $result->template();
			$vars = $result->vars();
			$matches = $result->matches();

			if (is_callable($template)) {
				$template = $template($vars, $wp, $matches);
			}

			if (!(is_string($template) || $template === 'false')) {
				$template = '';
			}

			if (is_callable($handler)) {
				$handler_result = call_user_func($handler);
			}

			$this->set_template($template);

			if (is_bool($handler_result)) {
				$parse_request = $handler_result;
			}

			if (!$parse_request) {
				remove_filter('template_redirect', 'redirect_canonical');

				return false;
			}
		}

		return $parse_request;
	}

	private function set_template($template)
	{
		if (is_string($template) && $template) {
			if (!pathinfo($template, PATHINFO_EXTENSION)) {
				$template .= '.php';
			}

			$template = is_file($template) ? $template : locate_template([$template], false);
			if (!$template) {
				$template = '';
			}
		}

		if ($template === '' || !(is_string($template) || $template === false)) {
			return;
		}

		$types = [
			'404',
            'search',
            'front_page',
            'home',
            'archive',
            'taxonomy',
            'attachment',
            'single',
            'page',
            'singular',
            'category',
            'tag',
            'author',
            'date',
            'paged',
            'index'
		];

		$return = function() use ($template) {
			if (current_filter() === 'template_include') {
				remove_all_filters('template_include');
			}

			return $template;
		};

		$template_setter = $template !== false ? $return : '__return_true';

		array_walk($types, function($type) use ($template_setter) {
			add_filter("{$type}_template", $template_setter);
		});

		add_filter('template_include', $return, -1);
	}
}