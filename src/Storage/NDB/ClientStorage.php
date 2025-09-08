<?php

namespace Stepapo\OAuth2\Storage\NDB;

use Stepapo\OAuth2\Storage\Clients\IClientStorage;
use Stepapo\OAuth2\Storage\Clients\IClient;
use Stepapo\OAuth2\Storage\Clients\Client;
use Nette\Database\Context;
use Nette\SmartObject;

/**
 * Nette database client storage
 * @package Stepapo\OAuth2\Storage\Clients
 * @author Drahomír Hanák
 */
class ClientStorage implements IClientStorage
{
	/** @var Context */
	private $context;

	public function __construct(Context $context)
	{
		$this->context = $context;
	}

	/**
	 * Get client table selection
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getTable()
	{
		return $this->context->table('oauth_client');
	}

	/**
	 * Find client by ID and/or secret key
	 * @param string $clientId
	 * @param string|null $clientSecret
	 * @return IClient
	 */
	public function getClient($clientId, $clientSecret = null): ?IClient
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

	/**
	 * Can client use given grant type
	 * @param string $clientId
	 * @param string $grantType
	 * @return bool
	 */
	public function canUseGrantType($clientId, $grantType): bool
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