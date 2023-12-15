<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Utils;

final class Regex
{

	/**
	 * @param 0|PREG_OFFSET_CAPTURE|PREG_UNMATCHED_AS_NULL $flags
	 */
	public static function match(string $subject, string $pattern, int $flags = 0): mixed
	{
		$ret = preg_match($pattern, $subject, $m, $flags);

		return $ret === 1 ? $m : null;
	}

	/**
	 * @param 0|PREG_OFFSET_CAPTURE|PREG_UNMATCHED_AS_NULL $flags
	 */
	public static function matchAll(string $subject, string $pattern, int $flags = 0): mixed
	{
		$ret = preg_match_all($pattern, $subject, $m, $flags);

		return $ret !== false ? $m : null;
	}

}
