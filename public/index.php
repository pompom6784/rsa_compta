<?php
declare(strict_types=1);

session_start();
use App\Application\Handlers\HttpErrorHandler;
use App\Application\Handlers\ShutdownHandler;
use App\Application\ResponseEmitter\ResponseEmitter;
use App\Application\Settings\SettingsInterface;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Twig\Extra\Intl\IntlExtension;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

if (false) { // Should be set to true in production
	$containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}

// Set up settings
$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

// Set up repositories
$repositories = require __DIR__ . '/../app/repositories.php';
$repositories($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();
$callableResolver = $app->getCallableResolver();

// Register middleware
$middleware = require __DIR__ . '/../app/middleware.php';
$middleware($app);

// Add Twig-View Middleware
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
$twig->addExtension(new IntlExtension());
$twig->getEnvironment()->addGlobal('current_year', $_SESSION['current_year'] ?? null);
$app->add(TwigMiddleware::create($app, $twig));

$container->set('view', $twig);

$container->set(EntityManager::class, static function (Container $c): EntityManager {
	/** @var array $settings */
	$settings = $c->get(SettingsInterface::class);

	// Use the ArrayAdapter or the FilesystemAdapter depending on the value of the 'dev_mode' setting
	// You can substitute the FilesystemAdapter for any other cache you prefer from the symfony/cache library
	$cache = $settings->get('doctrine')['dev_mode'] ?
			DoctrineProvider::wrap(new ArrayAdapter()) :
			DoctrineProvider::wrap(new FilesystemAdapter(directory: $settings->get('doctrine')['cache_dir']));

	$config = Setup::createAttributeMetadataConfiguration(
			$settings->get('doctrine')['metadata_dirs'],
			$settings->get('doctrine')['dev_mode'],
			null,
			$cache
	);

	return EntityManager::create($settings->get('doctrine')['connection'], $config);
});

// Register routes
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

// Register services
$services = require __DIR__ . '/../app/services.php';
$services($container);

/** @var SettingsInterface $settings */
$settings = $container->get(SettingsInterface::class);

$displayErrorDetails = $settings->get('displayErrorDetails');
$logError = $settings->get('logError');
$logErrorDetails = $settings->get('logErrorDetails');

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Create Error Handler
$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);

// Create Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Body Parsing Middleware
$app->addBodyParsingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, $logError, $logErrorDetails);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// Run App & Emit Response
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
