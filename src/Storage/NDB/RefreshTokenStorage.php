<?php

namespace Stepapo\OAuth2\Storage\NDB;

use Stepapo\OAuth2\Storage\RefreshTokens\IRefreshTokenStorage;
use Stepapo\OAuth2\Storage\RefreshTokens\IRefreshToken;
use Stepapo\OAuth2\Storage\RefreshTokens\RefreshToken;
use Nette\Database\Context;
use Nette\Database\SqlLiteral;
use Nette\SmartObject;

/**
 * Nette database RefreshToken storage
 * @package Stepapo\OAuth2\Storage\RefreshTokens
 * @author Drahomír Hanák
 */
class RefreshTokenStorage implements IRefreshTokenStorage
{
	/** @var Context */
	private $context;

	public function __construct(Context $context)
	{
		$this->context = $context;
	}

	/**
	 * Get authorization code table
	 * @return \Nette\Database\Table\Selection
	 */
	protected function getTable()
	{
		return $this->context->table('oauth_refresh_token');
	}

	/******************** IRefreshTokenStorage ********************/

	/**
	 * Store refresh token
	 * @param IRefreshToken $refreshToken
	 */
	public function storeRefreshToken(IRefreshToken $refreshToken): void
	{
		$this->getTable()->insert([
			'refresh_token' => $refreshToken->getRefreshToken(),
			'client_id' => $refreshToken->getClientId(),
			'user_id' => $refreshToken->getUserId(),
			'expires' => $refreshToken->getExpires()
		]);
	}

	/**
	 * Remove refresh token
	 * @param string $refreshToken
	 */
	public function removeRefreshToken($refreshToken): void
	{
		$this->getTable()->where(['refresh_token' => $refreshToken])->delete();
	}

	/**
	 * Get valid refresh token
	 * @param string $refreshToken
	 * @return IRefreshToken|null
	 */
	public function getValidRefreshToken($refreshToken): ?IRefreshToken
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