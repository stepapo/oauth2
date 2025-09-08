<?php

namespace Stepapo\OAuth2\Storage\RefreshTokens;

/**
 * IRefreshToken entity
 * @package Stepapo\OAuth2\Storage\RefreshTokens
 * @author Drahomír Hanák
 */
interface IRefreshToken
{
	public function getRefreshToken(): string;
	public function getExpires(): \DateTimeInterface;
	public function getClientId(): string|int;
	public function getUserId(): string|int|null;
}