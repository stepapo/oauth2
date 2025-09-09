<?php

namespace Stepapo\OAuth2;


/**
 * IKeyGenerator
 * @package Stepapo\OAuth2
 * @author Drahomír Hanák
 */
interface IKeyGenerator
{
	public function generate(int $length = 40): string;
}
