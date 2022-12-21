<?php

namespace JayWolfeLib\Component\Routing;

interface RouteCollectionInterface extends \IteratorAggregate, \Countable
{
	public function addRoute(RouteInterface $route);
}