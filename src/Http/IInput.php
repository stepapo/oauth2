<?php

namespace Stepapo\OAuth2\Http;


/**
 * Request input data interface
 * @package Stepapo\OAuth2\Http
 * @author Drahomír Hanák
 */
interface IInput
{
	public function getParameters(): array;
	public function getParameter(string $name): string|int|null;
	public function getAuthorization(): ?string;
}
