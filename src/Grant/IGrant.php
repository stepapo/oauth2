<?php

namespace Stepapo\OAuth2\Grant;

use Nette\Http\IRequest;

/**
 * Grant type interface
 * @package Stepapo\OAuth2\Grant
 * @author Drahomír Hanák
 */
interface IGrant
{

	/** Grant types defined in specification */
	const AUTHORIZATION_CODE = 'authorization_code';
	const CLIENT_CREDENTIALS = 'client_credentials';
	const REFRESH_TOKEN = 'refresh_token';
	const IMPLICIT = 'implicit';
	const PASSWORD = 'password';

	/**
	 * Get identifier string to this grant type
	 */
	public function getIdentifier(): string;

	/**
	 * Get access token
	 */
	public function getAccessToken(): array;

}