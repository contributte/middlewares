<?php declare(strict_types = 1);

namespace Tests\Fixtures;

final class MemoryMailer
{

	/** @var string[] */
	public static $mails = [];

	public static function mail($message, string $email): void
	{
		self::$mails[] = $message;
	}

}
