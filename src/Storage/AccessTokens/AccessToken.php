<?php

namespace Stepapo\OAuth2\Storage\AccessTokens;

use Nette\Utils\DateTime;


/**
 * Base AccessToken entity
 * @package Stepapo\OAuth2\Storage\AccessTokens
 * @author Drahomír Hanák
 */
class AccessToken implements IAccessToken
{
	public function __construct(
		private string $accessToken,
		private \DateTimeImmutable $expires,
		private string|int $clientId,
		private string|int|null $userId,
		private array $scope
	) {}


	public function getAccessToken(): string
	{
		return $this->accessToken;
	}


	public function getClientId(): int|string
	{
		return $this->clientId;
	}


	public function getUserId(): int|string|null
	{
		return $this->userId;
	}


	public function getExpires(): \DateTimeInterface
	{
		return $this->expires;
	}


	public function getScope(): array
	{
		return $this->scope;
	}

}