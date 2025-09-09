<?php

namespace Stepapo\OAuth2\Storage\NDB;

use Stepapo\OAuth2\Storage\Clients\IClientStorage;
use Stepapo\OAuth2\Storage\Clients\IClient;
use Stepapo\OAuth2\Storage\Clients\Client;
use Nette\Database\Context;


/**
 * Nette database client storage
 * @package Stepapo\OAuth2\Storage\Clients
 * @author Drahomír Hanák
 */
class ClientStorage implements IClientStorage
{
	public function __construct(
		private Context $context
	) {}


	protected function getTable(): \Nette\Database\Table\Selection
	{
		return $this->context->table('oauth_client');
	}


	public function getClient(string|int $clientId, ?string $clientSecret = null): ?IClient
	{
		if (!$clientId) return null;

		$selection = $this->getTable()->where(['client_id' => $clientId]);
		if ($clientSecret) {
			$selection->where(['secret' => $clientSecret]);
		}
		$data = $selection->fetch();
		if (!$data) return null;
		return new Client($data['client_id'], $data['secret'], $data['redirect_url']);
	}


	public function canUseGrantType(string $clientId, string $grantType): bool
	{
		$result = $this->getTable()->getConnection()->query('
			SELECT g.name
			FROM oauth_client_grant AS cg
			RIGHT JOIN oauth_grant AS g ON cg.grant_id = cg.grant_id AND g.name = ?
			WHERE cg.client_id = ?
		', $grantType, $clientId);
		return (bool)$result->fetch();
	}
}
