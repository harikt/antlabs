<?php

namespace App\EventListener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Turns every exception under /api into { "errors": { "field": ["message"] } }.
 */
// Priority -64: after Symfony logs the exception (0), before its HTML error page (-128).
#[AsEventListener(event: KernelEvents::EXCEPTION, priority: -64)]
final class ApiExceptionListener
{
    private readonly CamelCaseToSnakeCaseNameConverter $nameConverter;

    public function __construct(
        #[Autowire('%kernel.debug%')] private readonly bool $debug,
    ) {
        $this->nameConverter = new CamelCaseToSnakeCaseNameConverter();
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();
        $previous = $exception->getPrevious();

        if ($previous instanceof ValidationFailedException) {
            $errors = [];
            foreach ($previous->getViolations() as $violation) {
                $field = $this->nameConverter->normalize($violation->getPropertyPath()) ?: 'body';
                $errors[$field][] = $violation->getMessage();
            }
            $status = Response::HTTP_UNPROCESSABLE_ENTITY;
        } elseif ($exception instanceof NotFoundHttpException) {
            $errors = $request->attributes->has('id')
                ? ['id' => ['Task not found.']]
                : ['route' => ['Route not found.']];
            $status = Response::HTTP_NOT_FOUND;
        } elseif ($exception instanceof HttpExceptionInterface) {
            $errors = ['request' => [$exception->getMessage()]];
            $status = $exception->getStatusCode();
        } else {
            $errors = ['server' => [$this->debug ? $exception->getMessage() : 'Internal server error.']];
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $event->setResponse(new JsonResponse(['errors' => $errors], $status));
    }
}
