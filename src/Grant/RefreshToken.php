<?php

namespace Stepapo\OAuth2\Grant;

use Stepapo\OAuth2\Storage\InvalidRefreshTokenException;
use Stepapo\OAuth2\Storage\ITokenFacade;


/**
 * RefreshToken
 * @package Stepapo\OAuth2\Grant
 * @author Drahomír Hanák
 */
class RefreshToken extends GrantType
{
	public function getIdentifier(): string
	{
		return self::REFRESH_TOKEN;
	}


	/**
	 * @throws InvalidRefreshTokenException
	 */
	protected function verifyRequest(): void
	{
		$refreshTokenStorage = $this->token->getToken(ITokenFacade::REFRESH_TOKEN);
		$refreshToken = $this->input->getParameter('refresh_token');

		$refreshTokenStorage->getEntity($refreshToken);
		$refreshTokenStorage->getStorage()->remove($refreshToken);
	}


	protected function generateAccessToken(): array
	{
		$accessTokenStorage = $this->token->getToken(ITokenFacade::ACCESS_TOKEN);
		$refreshTokenStorage = $this->token->getToken(ITokenFacade::REFRESH_TOKEN);

		$accessToken = $accessTokenStorage->create($this->getClient(), $this->user->getId());
		$refreshToken = $refreshTokenStorage->create($this->getClient(), $this->user->getId());

		return [
			'access_token' => $accessToken->getAccessToken(),
			'token_type' => 'bearer',
			'expires_in' => $accessTokenStorage->getLifetime(),
			'refresh_token' => $refreshToken->getRefreshToken()
		];
	}
}
