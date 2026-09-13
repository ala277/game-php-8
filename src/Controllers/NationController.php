<?php

declare(strict_types=1);

namespace NationStates\Controllers;

use NationStates\Services\NationService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use JsonException;

class NationController
{
    public function __construct(private NationService $nationService) {}

    public function create(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = json_decode($request->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
            
            $name = $data['name'] ?? null;
            $userId = $data['userId'] ?? null;

            if (!$name || !$userId) {
                return $this->jsonResponse($response, ['error' => 'Missing required fields'], 400);
            }

            $nation = $this->nationService->createNation($name, $userId);

            return $this->jsonResponse($response, $nation->toArray(), 201);
        } catch (JsonException $e) {
            return $this->jsonResponse($response, ['error' => 'Invalid JSON'], 400);
        }
    }

    public function show(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $slug = $args['slug'] ?? null;
        $nation = $this->nationService->getNation($slug);

        if (!$nation) {
            return $this->jsonResponse($response, ['error' => 'Nation not found'], 404);
        }

        return $this->jsonResponse($response, $nation->toArray());
    }

    public function list(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $nations = $this->nationService->getAllNations();
        $data = array_map(fn($n) => $n->toArray(), $nations);

        return $this->jsonResponse($response, $data);
    }

    public function userNations(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $userId = (int)($args['userId'] ?? 0);
        $nations = $this->nationService->getNationsByUser($userId);
        $data = array_map(fn($n) => $n->toArray(), $nations);

        return $this->jsonResponse($response, $data);
    }

    public function update(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $slug = $args['slug'] ?? null;
            $nation = $this->nationService->getNation($slug);

            if (!$nation) {
                return $this->jsonResponse($response, ['error' => 'Nation not found'], 404);
            }

            $data = json_decode($request->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);

            if (isset($data['government'])) {
                $nation->setGovernment($data['government']);
            }
            if (isset($data['taxRate'])) {
                $nation->setTaxRate((float)$data['taxRate']);
            }
            if (isset($data['happiness'])) {
                $nation->setHappiness((float)$data['happiness']);
            }

            $this->nationService->updateNation($nation);

            return $this->jsonResponse($response, $nation->toArray());
        } catch (JsonException $e) {
            return $this->jsonResponse($response, ['error' => 'Invalid JSON'], 400);
        }
    }

    public function simulateDay(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $slug = $args['slug'] ?? null;
        $nation = $this->nationService->getNation($slug);

        if (!$nation) {
            return $this->jsonResponse($response, ['error' => 'Nation not found'], 404);
        }

        $nation = $this->nationService->simulateDailyUpdate($nation);

        return $this->jsonResponse($response, [
            'message' => 'Day simulated successfully',
            'nation' => $nation->toArray(),
        ]);
    }

    private function jsonResponse(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response = $response->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
        return $response;
    }
}
