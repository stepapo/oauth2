<?php

namespace Stepapo\OAuth2\Storage\RefreshTokens;

use Stepapo\OAuth2\IKeyGenerator;
use Stepapo\OAuth2\Storage\ITokenFacade;
use Stepapo\OAuth2\Storage\InvalidRefreshTokenException;
use Stepapo\OAuth2\Storage\Clients\IClient;

/**
 * RefreshToken
 * @package Stepapo\OAuth2\Token
 * @author Drahomír Hanák
 */
class RefreshTokenFacade implements ITokenFacade
{
	public function __construct(
		private int $lifetime,
		private IKeyGenerator $keyGenerator,
		private IRefreshTokenStorage $storage
	) {}


	public function create(IClient $client, string|int|null $userId, array $scope = []): RefreshToken
	{
		$expires = new \DateTimeImmutable('+' . $this->lifetime . ' seconds');
		$refreshToken = new RefreshToken(
			$this->keyGenerator->generate(),
			$expires,
			$client->getId(),
			$userId
		);
		$this->storage->storeRefreshToken($refreshToken);

		return $refreshToken;
	}


	/**
	 * @throws InvalidRefreshTokenException
	 */
	public function getEntity(string $token): ?IRefreshToken
	{
		$entity = $this->storage->getValidRefreshToken($token);
		if (!$entity) {
			$this->storage->removeRefreshToken($token);
			throw new InvalidRefreshTokenException;
		}
		return $entity;
	}


	public function getIdentifier(): string
	{
		return self::REFRESH_TOKEN;
	}


	public function getLifetime(): int
	{
		return $this->lifetime;
	}


	public function getStorage(): IRefreshTokenStorage
	{
		return $this->storage;
	}
}
