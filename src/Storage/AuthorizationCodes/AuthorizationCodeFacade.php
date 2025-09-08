<?php

namespace Stepapo\OAuth2\Storage\AuthorizationCodes;

use Stepapo\OAuth2\IKeyGenerator;
use Stepapo\OAuth2\Storage\ITokenFacade;
use Stepapo\OAuth2\Storage\InvalidAuthorizationCodeException;
use Stepapo\OAuth2\Storage\Clients\IClient;
use Nette\SmartObject;

/**
 * AuthorizationCode
 * @package Stepapo\OAuth2\Token
 * @author Drahomír Hanák
 */
class AuthorizationCodeFacade implements ITokenFacade
{
	public function __construct(
		private int $lifetime,
		private IKeyGenerator $keyGenerator,
		private IAuthorizationCodeStorage $storage
	) {}


	public function create(IClient $client, string|int $userId, array $scope = []): AuthorizationCode
	{
		$accessExpires = new \DateTimeImmutable('+' . $this->lifetime . ' seconds');
		$accessExpires = $accessExpires->modify('+' . $this->lifetime . ' seconds');

		$authorizationCode = new AuthorizationCode(
			$this->keyGenerator->generate(),
			$accessExpires,
			$client->getId(),
			$userId,
			$scope
		);
		$this->storage->store($authorizationCode);

		return $authorizationCode;
	}


	/**
	 * @throws InvalidAuthorizationCodeException
	 */
	public function getEntity(string $token): ?IAuthorizationCode
	{
		$entity = $this->storage->getValidAuthorizationCode($token);
		if (!$entity) {
			$this->storage->remove($token);
			throw new InvalidAuthorizationCodeException;
		}
		return $entity;
	}


	public function getIdentifier(): string
	{
		return self::AUTHORIZATION_CODE;
	}


	public function getLifetime(): int
	{
		return $this->lifetime;
	}


	public function getStorage(): IAuthorizationCodeStorage
	{
		return $this->storage;
	}
}