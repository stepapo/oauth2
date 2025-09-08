<?php

namespace Stepapo\OAuth2\Storage\AccessTokens;
use Stepapo\OAuth2\InvalidScopeException;

/**
 * Access token storage interface
 * @package Stepapo\OAuth2\Storage
 * @author Drahomír Hanák
 */
interface IAccessTokenStorage
{
	public function storeAccessToken(IAccessToken $accessToken): void;
	public function removeAccessToken(string $accessToken): void;
	public function getValidAccessToken(string $accessToken): ?IAccessToken;
}