<?php

namespace Stepapo\OAuth2\DI;

use Stepapo\OAuth2\Grant\AuthorizationCode;
use Stepapo\OAuth2\Grant\ClientCredentials;
use Stepapo\OAuth2\Grant\GrantContext;
use Stepapo\OAuth2\Grant\Implicit;
use Stepapo\OAuth2\Grant\Password;
use Stepapo\OAuth2\Grant\RefreshToken;
use Stepapo\OAuth2\Http\Input;
use Stepapo\OAuth2\KeyGenerator;
use Stepapo\OAuth2\Storage\AccessTokens\AccessTokenFacade;
use Stepapo\OAuth2\Storage\AuthorizationCodes\AuthorizationCodeFacade;
use Stepapo\OAuth2\Storage\NDB\AccessTokenStorage;
use Stepapo\OAuth2\Storage\NDB\AuthorizationCodeStorage;
use Stepapo\OAuth2\Storage\NDB\ClientStorage;
use Stepapo\OAuth2\Storage\NDB\RefreshTokenStorage;
use Stepapo\OAuth2\Storage\RefreshTokens\RefreshTokenFacade;
use Stepapo\OAuth2\Storage\TokenContext;
use Nette\Configurator;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\Definition;
use Nette\DI\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * OAuth2 compiler extension
 * @package Stepapo\OAuth2\DI
 * @author Drahomír Hanák
 */
class OAuth2Extension extends CompilerExtension
{
	protected $storages = [
		'ndb' => [
			'accessTokenStorage' => 'Stepapo\OAuth2\Storage\NDB\AccessTokenStorage',
			'authorizationCodeStorage' => 'Stepapo\OAuth2\Storage\NDB\AuthorizationCodeStorage',
			'clientStorage' => 'Stepapo\OAuth2\Storage\NDB\ClientStorage',
			'refreshTokenStorage' => 'Stepapo\OAuth2\Storage\NDB\RefreshTokenStorage',
		],
		'dibi' => [
			'accessTokenStorage' => 'Stepapo\OAuth2\Storage\Dibi\AccessTokenStorage',
			'authorizationCodeStorage' => 'Stepapo\OAuth2\Storage\Dibi\AuthorizationCodeStorage',
			'clientStorage' => 'Stepapo\OAuth2\Storage\Dibi\ClientStorage',
			'refreshTokenStorage' => 'Stepapo\OAuth2\Storage\Dibi\RefreshTokenStorage',
		],
	];
	

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'accessTokenStorage' => Expect::string(AccessTokenStorage::class),
			'authorizationCodeStorage' => Expect::string(AuthorizationCodeStorage::class),
			'clientStorage' => Expect::string(ClientStorage::class),
			'refreshTokenStorage' => Expect::string(RefreshTokenStorage::class),
			'accessTokenLifetime' => Expect::int(3600), // 1 hour
			'refreshTokenLifetime' => Expect::int(36000), // 10 hours
			'authorizationCodeLifetime' => Expect::int(360), // 6 minutes
			'storage' => Expect::string('ndb'),
		]);
	}
	

	/**
	 * Load DI configuration
	 */
	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		// Library common
		$container->addDefinition($this->prefix('keyGenerator'))
			->setType(KeyGenerator::class);
		$container->addDefinition($this->prefix('input'))
			->setType(Input::class);
		// Grant types
		$container->addDefinition($this->prefix('authorizationCodeGrant'))
			->setType(AuthorizationCode::class);
		$container->addDefinition($this->prefix('refreshTokenGrant'))
			->setType(RefreshToken::class);
		$container->addDefinition($this->prefix('passwordGrant'))
			->setType(Password::class);
		$container->addDefinition($this->prefix('implicitGrant'))
			->setType(Implicit::class);
		$container->addDefinition($this->prefix('clientCredentialsGrant'))
			->setType(ClientCredentials::class);
		$container->addDefinition($this->prefix('grantContext'))
			->setType(GrantContext::class)
			->addSetup('$service->addGrantType(?)', [$this->prefix('@authorizationCodeGrant')])
			->addSetup('$service->addGrantType(?)', [$this->prefix('@refreshTokenGrant')])
			->addSetup('$service->addGrantType(?)', [$this->prefix('@passwordGrant')])
			->addSetup('$service->addGrantType(?)', [$this->prefix('@implicitGrant')])
			->addSetup('$service->addGrantType(?)', [$this->prefix('@clientCredentialsGrant')]);
		// Tokens
		$container->addDefinition($this->prefix('accessToken'))
			->setType(AccessTokenFacade::class)
			->setArguments([$this->config->accessTokenLifetime]);
		$container->addDefinition($this->prefix('refreshToken'))
			->setType(RefreshTokenFacade::class)
			->setArguments([$this->config->refreshTokenLifetime]);
		$container->addDefinition($this->prefix('authorizationCode'))
			->setType(AuthorizationCodeFacade::class)
			->setArguments([$this->config->authorizationCodeLifetime]);

		$container->addDefinition('tokenContext')
			->setType(TokenContext::class)
			->addSetup('$service->addToken(?)', [$this->prefix('@accessToken')])
			->addSetup('$service->addToken(?)', [$this->prefix('@refreshToken')])
			->addSetup('$service->addToken(?)', [$this->prefix('@authorizationCode')]);

		// Default fallback value
		$storageIndex = 'ndb';

		// Nette database Storage
		if (strtoupper($this->config->storage) == 'NDB' || (is_null($this->config->storage) && $this->getByType($container, 'Nette\Database\Context'))) {
			$storageIndex = 'ndb';
		} elseif (strtoupper($this->config->storage) == 'DIBI' || (is_null($this->config->storage) && $this->getByType($container, 'DibiConnection'))) {
			$storageIndex = 'dibi';
		}

		$container->addDefinition($this->prefix('accessTokenStorage'))
			->setType($this->config->accessTokenStorage ?: $this->storages[$storageIndex]['accessTokenStorage']);
		$container->addDefinition($this->prefix('refreshTokenStorage'))
			->setType($this->config->refreshTokenStorage ?: $this->storages[$storageIndex]['refreshTokenStorage']);
		$container->addDefinition($this->prefix('authorizationCodeStorage'))
			->setType($this->config->authorizationCodeStorage ?: $this->storages[$storageIndex]['authorizationCodeStorage']);
		$container->addDefinition($this->prefix('clientStorage'))
			->setType($this->config->clientStorage ?: $this->storages[$storageIndex]['clientStorage']);
	}


	private function getByType(ContainerBuilder $container, string $type): ?Definition
	{
		$definitions = $container->getDefinitions();
		foreach ($definitions as $definition) {
			if ($definition->class === $type) {
				return $definition;
			}
		}
		return null;
	}
}
