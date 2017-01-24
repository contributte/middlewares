<?php

namespace Contributte\Middlewares\Utils;

final class Regex
{

	/**
	 * @param string $subject
	 * @param string $pattern
	 * @param int $flags
	 * @return mixed
	 */
	public static function match($subject, $pattern, $flags = 0)
	{
		$ret = preg_match($pattern, $subject, $m, $flags);

		return $ret === 1 ? $m : NULL;
	}

	/**
	 * @param string $subject
	 * @param string $pattern
	 * @param int $flags
	 * @return mixed
	 */
	public static function matchAll($subject, $pattern, $flags = 0)
	{
		$ret = preg_match_all($pattern, $subject, $m, $flags);

		return $ret !== FALSE ? $m : NULL;
	}

}
