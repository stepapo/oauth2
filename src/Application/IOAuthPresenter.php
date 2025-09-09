<?php

namespace Stepapo\OAuth2\Application;

use Nette\Application\IPresenter;


/**
 * OAuth2 authorization server presenter
 * @package Stepapo\OAuth2\Application
 * @author Drahomír Hanák
 */
interface IOAuthPresenter extends IPresenter
{
	public function issueAuthorizationCode(string $responseType, string $redirectUrl, ?string $scope = null): void;
	public function issueAccessToken(): void;
}