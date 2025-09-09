<?php

namespace Stepapo\OAuth2\Storage\NDB;

use Stepapo\OAuth2\Storage\RefreshTokens\IRefreshTokenStorage;
use Stepapo\OAuth2\Storage\RefreshTokens\IRefreshToken;
use Stepapo\OAuth2\Storage\RefreshTokens\RefreshToken;
use Nette\Database\Context;
use Nette\Database\SqlLiteral;


/**
 * Nette database RefreshToken storage
 * @package Stepapo\OAuth2\Storage\RefreshTokens
 * @author Drahomír Hanák
 */
class RefreshTokenStorage implements IRefreshTokenStorage
{
	public function __construct(
		private Context $context
	) {}


	protected function getTable(): \Nette\Database\Table\Selection
	{
		return $this->context->table('oauth_refresh_token');
	}


	public function storeRefreshToken(IRefreshToken $refreshToken): void
	{
		$this->getTable()->insert([
			'refresh_token' => $refreshToken->getRefreshToken(),
			'client_id' => $refreshToken->getClientId(),
			'user_id' => $refreshToken->getUserId(),
			'expires' => $refreshToken->getExpires()
		]);
	}


	public function removeRefreshToken(string $refreshToken): void
	{
		$this->getTable()->where(['refresh_token' => $refreshToken])->delete();
	}


	public function getValidRefreshToken(string $refreshToken): ?IRefreshToken
	{
		$row = $this->getTable()
			->where(['refresh_token' => $refreshToken])
			->where(new SqlLiteral('TIMEDIFF(expires, NOW()) >= 0'))
			->fetch();

		if (!$row) return null;

		return new RefreshToken(
			$row['refresh_token'],
			new \DateTime($row['expires']),
			$row['client_id'],
			$row['user_id']
		);
	}
}
