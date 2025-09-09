<?php

namespace Stepapo\OAuth2\Storage\Clients;


/**
 * OAuth2 client entity
 * @package Stepapo\OAuth2\Storage\Entity
 * @author Drahomír Hanák
 */
interface IClient
{
	public function getId(): string|int;
	public function getSecret(): string|int;
	public function getRedirectUrl(): ?string;
}