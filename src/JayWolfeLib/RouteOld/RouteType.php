<?php

namespace JayWolfeLib\Route;

class RouteType
{
	public const ANY = 'any';

	public const API = 'api';

	public const ADMIN = 'admin';

	public const ADMIN_WITH_POSSIBLE_AJAX = 'admin_with_possible_ajax';

	public const AJAX = 'ajax';

	public const CRON = 'cron';

	public const FRONTEND = 'frontend';

	public const FRONTEND_WITH_POSSIBLE_AJAX = 'frontend_with_possible_ajax';

	public const LATE_FRONTEND = 'late_frontend';

	public const LATE_FRONTEND_WITH_POSSIBLE_AJAX = 'late_frontend_with_possible_ajax';
}