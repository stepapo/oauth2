<?php

namespace Stepapo\OAuth2\Grant;

use Stepapo\OAuth2\InvalidRequestException;
use Stepapo\OAuth2\InvalidStateException;
use Stepapo\OAuth2\Storage\ITokenFacade;
use Nette\Security\AuthenticationException;


/**
 * Password grant type
 * @package Stepapo\OAuth2\Grant
 * @author Drahomír Hanák
 */
class Password extends GrantType
{
	public function getIdentifier(): string
	{
		return self::PASSWORD;
	}

	/**
	 * @throws InvalidStateException
	 * @throws AuthenticationException
	 */
	protected function verifyRequest(): void
	{
		$password = $this->input->getParameter('password');
		$username = $this->input->getParameter('username');
		if (!$password || !$username) {
			throw new InvalidStateException;
		}

		try {
			$this->user->login($username, $password);
		} catch(AuthenticationException $e) {
			throw new InvalidRequestException('Wrong user credentials', $e);
		}
	}


	protected function generateAccessToken(): array
	{
		$accessTokenStorage = $this->token->getToken(ITokenFacade::ACCESS_TOKEN);
		$refreshTokenStorage = $this->token->getToken(ITokenFacade::REFRESH_TOKEN);

		$accessToken = $accessTokenStorage->create($this->getClient(), $this->user->getId(), $this->getScope());
		$refreshToken = $refreshTokenStorage->create($this->getClient(), $this->user->getId(), $this->getScope());

		return [
			'access_token' => $accessToken->getAccessToken(),
			'expires_in' => $accessTokenStorage->getLifetime(),
			'token_type' => 'bearer',
			'refresh_token' => $refreshToken->getRefreshToken()
		];
	}
}
