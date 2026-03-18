<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Domain\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

abstract class Action
{
    protected LoggerInterface $logger;

    protected Request $request;

    protected Response $response;

    protected array $args;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            return $this->action();
        } catch (DomainRecordNotFoundException $e) {
            throw new \RuntimeException($e->getMessage(), 404, $e);
        }
    }

    /**
     * @throws DomainRecordNotFoundException
     * @throws \InvalidArgumentException
     */
    abstract protected function action(): Response;

    /**
     * @return array|object
     */
    protected function getFormData()
    {
        return $this->request->getParsedBody();
    }

    /**
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function resolveArg(string $name)
    {
        if (!isset($this->args[$name])) {
            throw new \InvalidArgumentException("Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    /**
     * @param array|object|null $data
     */
    protected function respondWithData($data = null, int $statusCode = 200): Response
    {
        $payload = new ActionPayload($statusCode, $data);

        return $this->respond($payload);
    }

    protected function respond(ActionPayload $payload): Response
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        if (!$json) {
            $message = \json_last_error_msg();
            $data = $payload->getData();
            $id = null;
            foreach ($data['data'] as $line) {
                if ($line->getDescription() != mb_convert_encoding($line->getDescription(), 'UTF-8', 'UTF-8')) {
                    $id = $line->getId();
                }
            }
            $json = json_encode(['error' => 'An error occurred ' . $message . ' line: ' . $id], JSON_PRETTY_PRINT);
        }
        $this->response->getBody()->write($json);

        return $this->response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus($payload->getStatusCode());
    }

    protected function redirect(string $url): Response
    {
        return $this->response
            ->withHeader('Location', $url)
            ->withStatus(302);
    }
}
