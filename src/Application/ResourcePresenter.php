<?php

namespace Stepapo\OAuth2\Application;

use Stepapo\OAuth2\Http\IInput;
use Stepapo\OAuth2\Storage\AccessTokens\AccessToken;
use Stepapo\OAuth2\Storage\InvalidAccessTokenException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Presenter;

/**
 * OAuth2 secured resource presenter
 * @package Stepapo\OAuth2\Application
 * @author Drahomír Hanák
 */
abstract class ResourcePresenter extends Presenter implements IResourcePresenter
{
	private IInput $input;
	protected AccessToken $accessToken;

	/**
	 * Standard input parser
	 */
	public function injectInput(IInput $input)
	{
		$this->input = $input;
	}

	/**
	 * Access token manager
	 */
	public function injectAccessToken(AccessToken $accessToken)
	{
		$this->accessToken = $accessToken;
	}

	/**
	 * Check presenter requirements
	 * @throws ForbiddenRequestException
	 */
	public function checkRequirements($element): void
	{
		parent::checkRequirements($element);
		$accessToken = $this->input->getAuthorization();
		if (!$accessToken) {
			throw new ForbiddenRequestException('Access token not provided');
		}
		$this->checkAccessToken($accessToken);
	}

	/**
	 * Check if access token is valid
	 * @throws ForbiddenRequestException
	 */
	public function checkAccessToken(string $accessToken): void
	{
		try {
			$this->accessToken->getEntity($accessToken);
		} catch(InvalidAccessTokenException $e) {
			throw new ForbiddenRequestException('Invalid access token provided. Use refresh token to grant new one.', 0, $e);
		}
	}


}