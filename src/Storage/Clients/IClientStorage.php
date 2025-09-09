<?php

namespace Stepapo\OAuth2\Storage\Clients;


/**
 * Client manager interface
 * @package Stepapo\OAuth2\DataSource
 * @author Drahomír Hanák
 */
interface IClientStorage
{
	public function getClient(string $clientId, ?string $clientSecret = null): ?IClient;
	public function canUseGrantType(string $clientId, string $grantType): bool;
}
