<?php

namespace Stepapo\OAuth2\Application;

use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;
use Stepapo\OAuth2\Http\IInput;
use Stepapo\OAuth2\Storage\AccessTokens\AccessTokenFacade;
use Stepapo\OAuth2\Storage\InvalidAccessTokenException;

/**
 * OAuth2 secured resource presenter
 * @package Stepapo\OAuth2\Application
 * @author Drahomír Hanák
 */
abstract class ResourcePresenter extends Presenter implements IResourcePresenter
{
	#[Inject] public IInput $input;
	#[Inject] public AccessTokenFacade $accessTokenFacade;


	/**
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
	 * @throws ForbiddenRequestException
	 */
	public function checkAccessToken(string $accessToken): void
	{
		try {
			$this->accessTokenFacade->getEntity($accessToken);
		} catch(InvalidAccessTokenException $e) {
			throw new ForbiddenRequestException('Invalid access token provided. Use refresh token to grant new one.', 0, $e);
		}
	}


}