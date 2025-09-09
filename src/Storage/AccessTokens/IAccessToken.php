<?php

namespace Stepapo\OAuth2\Storage\AccessTokens;


/**
 * IAccessToken entity
 * @package Stepapo\OAuth2\Storage\AccessTokens
 * @author Drahomír Hanák
 */
interface IAccessToken
{
	public function getAccessToken(): string;
	public function getExpires(): \DateTimeInterface;
	public function getClientId(): string|int;
	public function getUserId(): string|int|null;
	public function getScope(): array;
}
