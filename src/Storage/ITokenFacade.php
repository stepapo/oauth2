<?php

namespace Stepapo\OAuth2\Storage;

use Stepapo\OAuth2\Storage\Clients\IClient;

/**
 * ITokenFacade
 * @package Stepapo\OAuth2\Token
 * @author Drahomír Hanák
 */
interface ITokenFacade
{
	const ACCESS_TOKEN = 'access_token';
	const REFRESH_TOKEN = 'refresh_token';
	const AUTHORIZATION_CODE = 'authorization_code';

	public function create(IClient $client, string|int $userId, array $scope = []): mixed;
	public function getEntity(string $token): mixed;
	public function getIdentifier(): string;
}