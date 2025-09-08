<?php

namespace Stepapo\OAuth2\Grant;

use Stepapo\OAuth2\Http\IInput;
use Stepapo\OAuth2\InvalidClientException;
use Stepapo\OAuth2\Storage\Clients\IClient;
use Stepapo\OAuth2\Storage\Clients\IClientStorage;
use Stepapo\OAuth2\Storage\AccessToken;
use Stepapo\OAuth2\Storage\RefreshTokenFacade;
use Stepapo\OAuth2\InvalidStateException;
use Stepapo\OAuth2\Storage\TokenContext;
use Stepapo\OAuth2\UnauthorizedClientException;
use Nette\SmartObject;
use Nette\Security\User;

/**
 * GrantType
 * @package Stepapo\OAuth2\Grant
 * @author Drahomír Hanák
 *
 * @property-read string $identifier
 */
abstract class GrantType implements IGrant
{
	const SCOPE_KEY = 'scope';
	const CLIENT_ID_KEY = 'client_id';
	const CLIENT_SECRET_KEY = 'client_secret';
	const GRANT_TYPE_KEY = 'grant_type';

	/** @var IClient */
	private $client;


	public function __construct(
		protected IInput $input,
		protected TokenContext $token,
		private IClientStorage $clientStorage,
		protected User $user
	) {}

	/**
	 * Get client
	 */
	protected function getClient(): IClient
	{
		if (!$this->client) {
			$clientId = $this->input->getParameter(self::CLIENT_ID_KEY);
			$clientSecret = $this->input->getParameter(self::CLIENT_SECRET_KEY);
			$this->client = $this->clientStorage->getClient($clientId, $clientSecret);
		}
		return $this->client;
	}

	/**
	 * Get scope as array - allowed separators: ',' AND ' '
	 */
	protected function getScope(): array
	{
		$scope = $this->input->getParameter(self::SCOPE_KEY) ?: '';
		return !is_array($scope) ?
			array_filter(explode(',', str_replace(' ', ',', $scope))) :
			$scope;
	}

	/****************** IGrant interface ******************/

	/**
	 * Get access token
	 * @throws UnauthorizedClientException
	 */
	public final function getAccessToken(): array
	{
		if (!$this->getClient()) {
			throw new UnauthorizedClientException('Client is not found');
		}

		$this->verifyGrantType();
		$this->verifyRequest();
		return $this->generateAccessToken();
	}

	/****************** Access token template methods ******************/

	/**
	 * Verify grant type
	 * @throws UnauthorizedClientException
	 * @throws InvalidGrantTypeException
	 */
	protected function verifyGrantType(): void
	{
		$grantType = $this->input->getParameter(self::GRANT_TYPE_KEY);
		if (!$grantType) {
			throw new InvalidGrantTypeException;
		}

		if (!$this->clientStorage->canUseGrantType($this->getClient()->getId(), $grantType)) {
			throw new UnauthorizedClientException;
		}
	}

	/**
	 * Verify request
	 */
	protected abstract function verifyRequest(): void;

	/**
	 * Generate access token
	 */
	protected abstract function generateAccessToken(): array;

}
