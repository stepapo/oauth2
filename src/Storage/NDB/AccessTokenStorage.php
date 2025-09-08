<?php

namespace Stepapo\OAuth2\Storage\NDB;

use Stepapo\OAuth2\InvalidScopeException;
use Stepapo\OAuth2\Storage\AccessTokens\AccessToken;
use Stepapo\OAuth2\Storage\AccessTokens\IAccessTokenStorage;
use Stepapo\OAuth2\Storage\AccessTokens\IAccessToken;
use Nette\Database\Context;
use Nette\Database\SqlLiteral;
use Nette\Database\Table\ActiveRow;
use Nette\SmartObject;

/**
 * AccessTokenStorage
 * @package Stepapo\OAuth2\Storage\AccessTokens
 * @author Drahomír Hanák
 */
class AccessTokenStorage implements IAccessTokenStorage
{
	public function __construct(private Context $context)
	{}

	/**
	 * Get authorization code table
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getTable()
	{
		return $this->context->table('oauth_access_token');
	}

	/**
	 * Get scope table
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getScopeTable()
	{
		return $this->context->table('oauth_access_token_scope');
	}

	/******************** IAccessTokenStorage ********************/

	/**
	 * Store access token
	 * @param IAccessToken $accessToken
	 * @throws InvalidScopeException
	 */
	public function storeAccessToken(IAccessToken $accessToken): void
	{
		$connection = $this->getTable()->getConnection();
		$connection->beginTransaction();
		$this->getTable()->insert([
			'access_token' => $accessToken->getAccessToken(),
			'client_id' => $accessToken->getClientId(),
			'user_id' => $accessToken->getUserId(),
			'expires' => $accessToken->getExpires()
		]);

		try {
			foreach ($accessToken->getScope() as $scope) {
				$this->getScopeTable()->insert([
					'access_token' => $accessToken->getAccessToken(),
					'scope_name' => $scope
				]);
			}
		} catch (\PDOException $e) {
			// MySQL error 1452 - Cannot add or update a child row: a foreign key constraint fails
			if (in_array(1452, $e->errorInfo)) {
				throw new InvalidScopeException;
			}
			throw $e;
		}
		$connection->commit();
	}

	/**
	 * Remove access token
	 * @param string $accessToken
	 */
	public function removeAccessToken($accessToken): void
	{
		$this->getTable()->where(['access_token' => $accessToken])->delete();
	}

	/**
	 * Get valid access token
	 * @param string $accessToken
	 * @return IAccessToken|null
	 */
	public function getValidAccessToken($accessToken): ?IAccessToken
	{
		/** @var ActiveRow $row */
		$row = $this->getTable()
			->where(['access_token' => $accessToken])
			->where(new SqlLiteral('TIMEDIFF(expires, NOW()) >= 0'))
			->fetch();

		if (!$row) return null;

		$scopes = $this->getScopeTable()
			->where(['access_token' => $accessToken])
			->fetchPairs('scope_name');

		return new AccessToken(
			$row['access_token'],
			new \DateTime($row['expires']),
			$row['client_id'],
			$row['user_id'],
			array_keys($scopes)
		);
	}


}