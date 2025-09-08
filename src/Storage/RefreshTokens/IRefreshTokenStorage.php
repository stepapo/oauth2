<?php

namespace Stepapo\OAuth2\Storage\RefreshTokens;

/**
 * IRefreshTokenStorage
 * @package Stepapo\OAuth2\Storage\RefreshTokens
 * @author Drahomír Hanák
 */
interface IRefreshTokenStorage
{
	public function storeRefreshToken(IRefreshToken $refreshToken): void;
	public function removeRefreshToken(string $refreshToken): void;
	public function getValidRefreshToken($refreshToken): ?IRefreshToken;
}
