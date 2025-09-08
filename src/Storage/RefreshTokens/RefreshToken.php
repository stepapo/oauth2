<?php

namespace Stepapo\OAuth2\Storage\RefreshTokens;

use Nette\SmartObject;

/**
 * RefreshToken
 * @package Stepapo\OAuth2\Storage\RefreshTokens
 * @author Drahomír Hanák
 */
class RefreshToken implements IRefreshToken
{
	public function __construct(
		private string $refreshToken,
		private \DateTimeImmutable $expires,
		private string|int $clientId,
		private string|int|null $userId
	) {}


	public function getRefreshToken(): string
	{
		return $this->refreshToken;
	}


	public function getExpires(): \DateTimeInterface
	{
		return $this->expires;
	}


	public function getClientId(): string|int
	{
		return $this->clientId;
	}


	public function getUserId(): int|string|null
	{
		return $this->userId;
	}

}