<?php declare(strict_types = 1);

namespace Contributte\Middlewares\Utils;

final class Regex
{

	/**
	 * @return mixed
	 */
	public static function match(string $subject, string $pattern, int $flags = 0)
	{
		$ret = preg_match($pattern, $subject, $m, $flags);

		return $ret === 1 ? $m : null;
	}

	/**
	 * @return mixed
	 */
	public static function matchAll(string $subject, string $pattern, int $flags = 0)
	{
		$ret = preg_match_all($pattern, $subject, $m, $flags);

		return $ret !== false ? $m : null;
	}

}
