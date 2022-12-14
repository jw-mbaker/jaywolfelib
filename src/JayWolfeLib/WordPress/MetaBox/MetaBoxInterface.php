<?php declare(strict_types=1);

namespace JayWolfeLib\WordPress\MetaBox;

use JayWolfeLib\Invoker\HandlerInterface;

interface MetaBoxInterface extends HandlerInterface
{
	public const META_ID = 'meta_id';
	public const TITLE = 'title';
	public const SCREEN = 'screen';
	public const CONTEXT = 'context';
	public const PRIORITY = 'priority';
	public const CALLBACK_ARGS = 'callback_args';

	public function metaId(): string;
	public function title(): string;
	public function screen();
	public function context(): string;
	public function priority(): string;
	public function callbackArgs(): ?array;
}