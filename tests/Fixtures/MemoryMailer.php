<?php declare(strict_types = 1);

namespace Tests\Fixtures;

final class MemoryMailer
{

	/** @var string[] */
	public static array $mails = [];

	public static function mail(string $message, string $email): void
	{
		self::$mails[] = $message;
	}

}
