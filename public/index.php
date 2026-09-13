<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use NationStates\Services\NationService;
use NationStates\Controllers\NationController;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

// Middleware
$app->addErrorMiddleware(true, true, true);

// Services
$nationService = new NationService();

// Controllers
$nationController = new NationController($nationService);

// Routes
$app->post('/api/nations', [$nationController, 'create']);
$app->get('/api/nations', [$nationController, 'list']);
$app->get('/api/nations/{slug}', [$nationController, 'show']);
$app->get('/api/users/{userId}/nations', [$nationController, 'userNations']);
$app->put('/api/nations/{slug}', [$nationController, 'update']);
$app->post('/api/nations/{slug}/simulate-day', [$nationController, 'simulateDay']);

// Home endpoint
$app->get('/', function ($request, $response) {
    $response->getBody()->write(json_encode([
        'name' => 'NationStates Game API',
        'version' => '1.0.0',
        'endpoints' => [
            'POST /api/nations' => 'Create a new nation',
            'GET /api/nations' => 'List all nations',
            'GET /api/nations/{slug}' => 'Get nation details',
            'GET /api/users/{userId}/nations' => 'Get user\'s nations',
            'PUT /api/nations/{slug}' => 'Update nation',
            'POST /api/nations/{slug}/simulate-day' => 'Simulate one day',
        ],
    ]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
