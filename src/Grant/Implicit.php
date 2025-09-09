<?php

namespace Stepapo\OAuth2\Grant;

use Stepapo\OAuth2\Storage\ITokenFacade;


/**
 * Implicit grant type
 * @package Stepapo\OAuth2\Grant
 * @author Drahomír Hanák
 */
class Implicit extends GrantType
{
	public function getIdentifier(): string
	{
		return self::IMPLICIT;
	}


	protected function verifyRequest(): void
	{
	}


	protected function generateAccessToken(): array
	{
		$accessTokenStorage = $this->token->getToken(ITokenFacade::ACCESS_TOKEN);
		$accessToken = $accessTokenStorage->create($this->getClient(), $this->user->getId(), $this->getScope());

		return [
			'access_token' => $accessToken->getAccessToken(),
			'expires_in' => $accessTokenStorage->getLifetime(),
			'token_type' => 'bearer'
		];
	}
}
