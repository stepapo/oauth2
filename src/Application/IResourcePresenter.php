<?php

namespace Stepapo\OAuth2\Application;

use Nette\Application\IPresenter;

/**
 * OAuth2 resource server presenter
 * @package Stepapo\OAuth2\Application
 * @author Drahomír Hanák
 */
interface IResourcePresenter extends IPresenter
{
	/**
	 * Check if access token is valid
	 */
	public function checkAccessToken(string $accessToken): void;
}