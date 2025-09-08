<?php

namespace Stepapo\OAuth2\Storage;

use Stepapo\OAuth2\InvalidStateException;
use Nette\SmartObject;

/**
 * TokenContext
 * @package Stepapo\OAuth2\Token
 * @author Drahomír Hanák
 */
class TokenContext
{
	private array $tokens = [];


	public function addToken(ITokenFacade $token): void
	{
		$this->tokens[$token->getIdentifier()] = $token;
	}

	/**
	 * @throws InvalidStateException
	 */
	public function getToken(string $identifier): ITokenFacade
	{
		if 	(!isset($this->tokens[$identifier])) {
			throw new InvalidStateException('Token called "' . $identifier . '" not found in Token context');
		}
		return $this->tokens[$identifier];
	}

}