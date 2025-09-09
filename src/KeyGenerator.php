<?php

namespace Stepapo\OAuth2;


/**
 * KeyGenerator
 * @package Stepapo\OAuth2
 * @author Drahomír Hanák
 */
class KeyGenerator implements IKeyGenerator
{
	const ALGORITHM = 'sha256';


	public function generate(int $length = 40): string
	{
		$bytes = openssl_random_pseudo_bytes($length);
		return hash(self::ALGORITHM, $bytes);
	}
}
