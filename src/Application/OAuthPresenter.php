<?php

namespace Stepapo\OAuth2\Application;

use Nette\DI\Attributes\Inject;
use Stepapo\OAuth2\Grant\GrantContext;
use Stepapo\OAuth2\Grant\IGrant;
use Stepapo\OAuth2\Grant\InvalidGrantTypeException;
use Stepapo\OAuth2\InvalidGrantException;
use Stepapo\OAuth2\InvalidRequestException;
use Stepapo\OAuth2\InvalidStateException;
use Stepapo\OAuth2\OAuthException;
use Stepapo\OAuth2\Storage\Clients\IClient;
use Stepapo\OAuth2\Storage\AuthorizationCodes\AuthorizationCodeFacade;
use Stepapo\OAuth2\Storage\InvalidAuthorizationCodeException;
use Stepapo\OAuth2\Grant\GrantType;
use Stepapo\OAuth2\Storage\Clients\IClientStorage;
use Stepapo\OAuth2\Storage\TokenException;
use Stepapo\OAuth2\UnauthorizedClientException;
use Stepapo\OAuth2\UnsupportedResponseTypeException;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Presenter;
use Nette\Http\Url;


/**
 * OauthPresenter
 * @package Stepapo\OAuth2\Application
 * @author Drahomír Hanák
 */
class OAuthPresenter extends Presenter implements IOAuthPresenter
{
	#[Inject] public GrantContext $grantContext;
	#[Inject] public AuthorizationCodeFacade $authorizationCode;
	#[Inject] public IClientStorage $clientStorage;
	protected ?IClient $client;


	protected function startup()
	{
		parent::startup();
		$this->client = $this->clientStorage->getClient(
			$this->getParameter(GrantType::CLIENT_ID_KEY) ?: '',
			$this->getParameter(GrantType::CLIENT_SECRET_KEY)
		);
	}

	/**
	 * @throws UnsupportedResponseTypeException
	 */
	public function getGrantType(): IGrant
	{
		$request = $this->getHttpRequest();
		$grantType = $request->getPost(GrantType::GRANT_TYPE_KEY);
		try {
			return $this->grantContext->getGrantType($grantType);
		} catch (InvalidStateException $e) {
			throw new UnsupportedResponseTypeException('Trying to use unknown grant type ' . $grantType, $e);
		}
	}


	public function oauthError(OAuthException $exception)
	{
		$error = [
			'error' => $exception->getKey(),
			'error_description' => $exception->getMessage()
		];
		$this->oauthResponse($error, $this->getParameter('redirect_uri'), $exception->getCode());
	}


	public function oauthResponse(array|\Traversable $data, ?string $redirectUrl = null, int $code = 200): void
	{
		if ($data instanceof \Traversable) {
			$data = iterator_to_array($data);
		}
		$data = (array)$data;

		// Redirect, if there is URL
		if ($redirectUrl !== null) {
			$url = new Url($redirectUrl);
			if ($this->getParameter('response_type') == 'token') {
				$url->setFragment(http_build_query($data));
			} else {
				$url->appendQuery($data);
			}
			$this->redirectUrl($url);
		}

		// else send JSON response
		foreach ($data as $key => $value) {
			$this->payload->$key = $value;
		}
		$this->getHttpResponse()->setCode($code);
		$this->sendResponse(new JsonResponse($this->payload));
	}


	public function issueAuthorizationCode(string $responseType, string $redirectUrl, ?string $scope = null, ?string $state = null): void
	{
		try {
			if ($responseType !== 'code') {
				throw new UnsupportedResponseTypeException;
			}
			if (!isset($this->client) || !$this->client->getId()) {
				throw new UnauthorizedClientException;
			}

			$scope = array_filter(explode(',', str_replace(' ', ',', $scope)));
			$code = $this->authorizationCode->create($this->client, $this->user->getId(), $scope);
			$data = [
				'code' => $code->getAuthorizationCode()
			];
			if (!empty($state)) {
				$data['state'] = $state;
			}
			$this->oauthResponse($data, $redirectUrl);
		} catch (OAuthException $e) {
			$this->oauthError($e);
		} catch (TokenException $e) {
			$this->oauthError(new InvalidGrantException);
		}
	}

	/**
	 * @throws InvalidAuthorizationCodeException
	 * @throws InvalidGrantTypeException
	 * @throws InvalidStateException
	 */
	public function issueAccessToken(?string $grantType = null, ?string $redirectUrl = null): void
	{
		try {
			if ($grantType !== null) {
				$grantType = $this->grantContext->getGrantType($grantType);
			} else {
				$grantType = $this->getGrantType();
			}

			$response = $grantType->getAccessToken();
			$this->oauthResponse($response, $redirectUrl);
		} catch (OAuthException $e) {
			$this->oauthError($e);
		} catch (TokenException $e) {
			$this->oauthError(new InvalidGrantException);
		}
	}
}
