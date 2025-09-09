<?php

namespace Stepapo\OAuth2\Storage\AccessTokens;

use Stepapo\OAuth2\IKeyGenerator;
use Stepapo\OAuth2\Storage\ITokenFacade;
use Stepapo\OAuth2\Storage\InvalidAccessTokenException;
use Stepapo\OAuth2\Storage\Clients\IClient;


/**
 * AccessToken
 * @package Stepapo\OAuth2\Token
 * @author Drahomír Hanák
 */
class AccessTokenFacade implements ITokenFacade
{
	public function __construct(
		private $lifetime,
		private IKeyGenerator $keyGenerator,
		private IAccessTokenStorage $storage,
	) {}


	public function create(IClient $client, string|int|null $userId, array $scope = []): AccessToken
	{
		$accessExpires = new \DateTimeImmutable('+' . $this->lifetime . ' seconds');

		$accessToken = new AccessToken(
			$this->keyGenerator->generate(),
			$accessExpires,
			$client->getId(),
			$userId,
			$scope
		);
		$this->storage->storeAccessToken($accessToken);

		return $accessToken;
	}

	/**
	 * @throws InvalidAccessTokenException
	 */
	public function getEntity(string $token): ?IAccessToken
	{
		$entity = $this->storage->getValidAccessToken($token);
		if (!$entity) {
			$this->storage->removeAccessToken($token);
			throw new InvalidAccessTokenException;
		}
		return $entity;
	}


	public function getIdentifier(): string
	{
		return self::ACCESS_TOKEN;
	}


	public function getLifetime(): int
	{
		return $this->lifetime;
	}


	public function getStorage(): IAccessTokenStorage
	{
		return $this->storage;
	}
}
