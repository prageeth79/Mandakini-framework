<?php
namespace app\core\middlewares;

use app\core\Application;
use app\core\exceptions\ForbiddenException;
use app\core\util\Csrf;

class CsrfMiddleware extends BaseMiddleware {

    /**
     * HTTP methods that require CSRF protection.
     */
    protected array $protectedMethods = ['post', 'put', 'patch', 'delete'];

    /**
     * Action names in the controller to exclude from CSRF verification.
     */
    protected array $actions = [];

    public function __construct(array $actions = []) {
        $this->actions = $actions; // e.g. ['webhook_endpoint']
    }

    public function execute() {
        $request = Application::$app->request;
        $currentAction = Application::$app->controller->action ?? '';

        // Skip excluded actions
        if (!empty($this->actions) && in_array($currentAction, $this->actions)) {
            return;
        }

        // Only validate state-changing HTTP requests
        if (in_array(strtolower($request->method()), $this->protectedMethods, true)) {
            $body = $request->getBody();
            
            // Extract token from POST payload or request header (for AJAX)
            $token = $body['csrf_token'] ?? $this->getHeaderToken();

            if (!Csrf::validate($token)) {
                throw new ForbiddenException("CSRF token validation failed.");
            }
        }
    }

    /**
     * Extract token from HTTP request headers.
     */
    private function getHeaderToken(): ?string {
        $headers = getallheaders();
        return $headers['X-CSRF-TOKEN'] ?? $headers['x-csrf-token'] ?? null;
    }
}