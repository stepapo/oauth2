<?php

namespace Stepapo\OAuth2\Storage\NDB;

use Stepapo\OAuth2\InvalidScopeException;
use Stepapo\OAuth2\Storage\AuthorizationCodes\AuthorizationCode;
use Stepapo\OAuth2\Storage\AuthorizationCodes\IAuthorizationCodeStorage;
use Stepapo\OAuth2\Storage\AuthorizationCodes\IAuthorizationCode;
use Nette\Database\Context;
use Nette\Database\SqlLiteral;
use Nette\Database\Table\ActiveRow;

/**
 * AuthorizationCode
 * @package Stepapo\OAuth2\Storage\AuthorizationCodes
 * @author Drahomír Hanák
 */
class AuthorizationCodeStorage implements IAuthorizationCodeStorage
{
	public function __construct(
		private Context $context
	) {}


	protected function getTable(): \Nette\Database\Table\Selection
	{
		return $this->context->table('oauth_authorization_code');
	}


	protected function getScopeTable(): \Nette\Database\Table\Selection
	{
		return $this->context->table('oauth_authorization_code_scope');
	}


	/**
	 * @throws InvalidScopeException
	 */
	public function storeAuthorizationCode(IAuthorizationCode $authorizationCode): void
	{

		$this->getTable()->insert([
			'authorization_code' => $authorizationCode->getAuthorizationCode(),
			'client_id' => $authorizationCode->getClientId(),
			'user_id' => $authorizationCode->getUserId(),
			'expires' => $authorizationCode->getExpires()
		]);

		$connection = $this->getTable()->getConnection();
		$connection->beginTransaction();
		try {
			foreach ($authorizationCode->getScope() as $scope) {
				$this->getScopeTable()->insert([
					'authorization_code' => $authorizationCode->getAuthorizationCode(),
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


	public function removeAuthorizationCode(string $authorizationCode): void
	{
		$this->getTable()->where(['authorization_code' => $authorizationCode])->delete();
	}


	public function getValidAuthorizationCode(string $authorizationCode): ?IAuthorizationCode
	{
		/** @var ActiveRow $row */
		$row = $this->getTable()
			->where(['authorization_code' => $authorizationCode])
			->where(new SqlLiteral('TIMEDIFF(expires, NOW()) >= 0'))
			->fetch();

		if (!$row) return null;

		$scopes = $this->getScopeTable()
			->where(['authorization_code' => $authorizationCode])
			->fetchPairs('scope_name');

		return new AuthorizationCode(
			$row['authorization_code'],
			new \DateTime($row['expires']),
			$row['client_id'],
			$row['user_id'],
			array_keys($scopes)
		);
	}
}
