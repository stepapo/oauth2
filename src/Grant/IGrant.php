<?php

namespace Stepapo\OAuth2\Grant;


/**
 * Grant type interface
 * @package Stepapo\OAuth2\Grant
 * @author Drahomír Hanák
 */
interface IGrant
{
	const AUTHORIZATION_CODE = 'authorization_code';
	const CLIENT_CREDENTIALS = 'client_credentials';
	const REFRESH_TOKEN = 'refresh_token';
	const IMPLICIT = 'implicit';
	const PASSWORD = 'password';

	public function getIdentifier(): string;
	public function getAccessToken(): array;
}