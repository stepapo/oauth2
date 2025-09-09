<?php

namespace Stepapo\OAuth2\Storage\AuthorizationCodes;


/**
 * Base AuthorizationCode entity
 * @package Stepapo\OAuth2\Storage\AuthorizationCodes
 * @author Drahomír Hanák
 */
class AuthorizationCode implements IAuthorizationCode
{
	public function __construct(
		private string $authorizationCode,
		private \DateTimeInterface $expires,
		private string|int $clientId,
		private string|int|null $userId,
		private array $scope
	) {}


	public function getAuthorizationCode(): string
	{
		return $this->authorizationCode;
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
