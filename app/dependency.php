<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// Register component on container
$container['view'] = function ($c) {
    $settings = $c->get('settings')['view'];
    $view = new \Slim\Views\Twig($settings['template_path'], [
        'debug' => $settings['debug'],
        'cache' => $settings['cache_path']
    ]);
	// Add extensions
    $view->addExtension(new \Slim\Views\TwigExtension(
        $c['router'],
        $c['request']->getUri()
    ));
    $view->addExtension(new \Core\TwigFunction());	
    $view->addExtension(new Twig_Extension_Debug());	
    return $view;
};
$container['errorHandler'] = function ($c) {
    $env = getenv("ENV");
    if ($env != "production") {
        return new Dopesong\Slim\Error\Whoops($c->get('settings')['displayErrorDetails']);
    } else {
        return function ($request, $response, $exception) use ($c) {
            $data = [
                'code'    => $exception->getCode(),
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => explode("\n", $exception->getTraceAsString()),
            ];

            return $c->get('response')->withStatus(500)
                ->withHeader('Content-Type', 'text/html')
                ->write('Whoops, looks like something went wrong.');
        };
    }
};

//Override the default Not Found Handler
$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c->get('response')->withRedirect('/sorry');

    };
};


// 
// Flash messages
$container['flash'] = function ($c) {
    return new \Slim\Flash\Messages;
};


// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};


// error handle
$container['errorHandler'] = function ($c) {
  $env = getenv("ENV");
  return function ($request, $response, $exception) use ($c) {
    $data = [
      'code' => $exception->getCode(),
      'message' => $exception->getMessage(),
      'file' => $exception->getFile(),
      'line' => $exception->getLine(),
      'trace' => explode("\n", $exception->getTraceAsString()),
    ];

    return $c->get('response')->withStatus(500)
             ->withHeader('Content-Type', 'application/json')
             ->write(json_encode($data));
  };
};




# -----------------------------------------------------------------------------
# Action factories Controllers
# -----------------------------------------------------------------------------


$container['Controller\Home'] = function ($c) {
    return new Controller\Home(
        $c->get('view'),
        $c->get('flash')
    );
};

$container['Controller\Login'] = function ($c) {
    return new Controller\Login(
        $c->get('view'),
        $c->get('flash')        
    );
};
