<?php

namespace Stepapo\OAuth2;

use Nette\SmartObject;

/**
 * KeyGenerator
 * @package Stepapo\OAuth2
 * @author Drahomír Hanák
 */
class KeyGenerator implements IKeyGenerator
{
	/** Key generator algorithm */
	const ALGORITHM = 'sha256';

	/**
	 * Generate random token
	 * @param int $length
	 * @return string
	 */
	public function generate($length = 40)
	{
		$bytes = openssl_random_pseudo_bytes($length);
		return hash(self::ALGORITHM, $bytes);
	}

}