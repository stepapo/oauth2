<?php

namespace Stepapo\OAuth2\Storage\AuthorizationCodes;


/**
 * IAuthorizationCode
 * @package Stepapo\OAuth2\Storage\AuthorizationCodes
 * @author Drahomír Hanák
 */
interface IAuthorizationCode
{
	public function getAuthorizationCode(): string;
	public function getExpires(): \DateTimeInterface;
	public function getClientId(): string|int;
	public function getUserId(): string|int|null;
	public function getScope(): array;
}
