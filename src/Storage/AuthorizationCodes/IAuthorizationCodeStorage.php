<?php

namespace Stepapo\OAuth2\Storage\AuthorizationCodes;


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
