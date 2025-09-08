<?php

namespace Stepapo\OAuth2\Http;

use Nette\Http\IRequest;
use Nette\SmartObject;

/**
 * Input parser
 * @package Stepapo\OAuth2\Http
 * @author Drahomír Hanák
 */
class Input implements IInput
{
	private array $data;


	public function __construct(
		private IRequest $request
	) {}

	/**
	 * Get all parameters
	 */
	public function getParameters(): array
	{
		if (!$this->data) {
			if ($this->request->getQuery()) {
				$this->data = $this->request->getQuery();
			} else if ($this->request->getPost()) {
				$this->data = $this->request->getPost();
			} else {
				$this->data = $this->parseRequest(file_get_contents('php://input'));
			}
		}
		return $this->data;
	}

	/**
	 * Get single parameter by key
	 */
	public function getParameter(string $name): string|int|null
	{
		$parameters = $this->getParameters();
		return isset($parameters[$name]) ? $parameters[$name] : null;
	}

	/**
	 * Get authorization token from header - Authorization: Bearer
	 * @return string
	 */
	public function getAuthorization(): ?string
	{
		$authorization = explode(' ', $this->request->getHeader('Authorization') ?: '');
		return isset($authorization[1]) ? $authorization[1] : null;
	}


	/**
	 * Convert client request data to array or traversable
	 * @param string $data
	 * @return array
	 */
	private function parseRequest(string $data): array
	{
		$result = [];
		parse_str($data, $result);
		return $result;
	}


}
