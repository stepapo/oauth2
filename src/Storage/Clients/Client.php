<?php

namespace Stepapo\OAuth2\Storage\Clients;

use Nette\SmartObject;

/**
 * OAuth2 base client caret
 * @package Stepapo\OAuth2\Storage\Entity
 * @author Drahomír Hanák
 */
class Client implements IClient
{
	public function __construct(
		private string|int $id,
		private string $secret,
		private ?string $redirectUrl = null
	) {}


	public function getId(): int|string
	{
		return $this->id;
	}


	public function getRedirectUrl(): ?string
	{
		return $this->redirectUrl;
	}


	public function getSecret(): string
	{
		return $this->secret;
	}

}