<?php

namespace Stepapo\OAuth2\Storage\AuthorizationCodes;
use Stepapo\OAuth2\InvalidScopeException;

/**
 * IAuthorizationCodeStorage
 * @package Stepapo\OAuth2\Storage\AuthorizationCodes
 * @author Drahomír Hanák
 */
interface IAuthorizationCodeStorage
{
	public function storeAuthorizationCode(IAuthorizationCode $authorizationCode): void;
	public function removeAuthorizationCode(string $authorizationCode): void;
	public function getValidAuthorizationCode(string $authorizationCode): ?IAuthorizationCode;
}
