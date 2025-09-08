<?php

namespace Stepapo\OAuth2\Storage\NDB;

use Stepapo\OAuth2\InvalidScopeException;
use Stepapo\OAuth2\Storage\AuthorizationCodes\AuthorizationCode;
use Stepapo\OAuth2\Storage\AuthorizationCodes\IAuthorizationCodeStorage;
use Stepapo\OAuth2\Storage\AuthorizationCodes\IAuthorizationCode;
use Nette\Database\Context;
use Nette\Database\SqlLiteral;
use Nette\Database\Table\ActiveRow;
use Nette\SmartObject;

/**
 * AuthorizationCode
 * @package Stepapo\OAuth2\Storage\AuthorizationCodes
 * @author Drahomír Hanák
 */
class AuthorizationCodeStorage implements IAuthorizationCodeStorage
{
	public function __construct(private Context $context)
	{}

	/**
	 * Get authorization code table
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getTable()
	{
		return $this->context->table('oauth_authorization_code');
	}

	/**
	 * Get scope table
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getScopeTable()
	{
		return $this->context->table('oauth_authorization_code_scope');
	}

	/******************** IAuthorizationCodeStorage ********************/

	/**
	 * Store authorization code
	 * @param IAuthorizationCode $authorizationCode
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

	/**
	 * Remove authorization code
	 * @param string $authorizationCode
	 * @return void
	 */
	public function removeAuthorizationCode($authorizationCode): void
	{
		$this->getTable()->where(['authorization_code' => $authorizationCode])->delete();
	}

	/**
	 * Validate authorization code
	 * @param string $authorizationCode
	 * @return IAuthorizationCode
	 */
	public function getValidAuthorizationCode($authorizationCode): ?IAuthorizationCode
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