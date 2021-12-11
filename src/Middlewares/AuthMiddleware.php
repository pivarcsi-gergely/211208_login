<?php

namespace Petrik\Loginapp\Middlewares;

use Error;
use Petrik\Loginapp\Token;
use Petrik\Loginapp\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\ResponseFactory as ResponseFactory;

class AuthMiddleware
{
    private $responseFactory;

    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $auth = $request->getHeader('Authorization');
        if (count($auth) !== 1) {
            $response = $this->responseFactory->createResponse();
            $response->getBody()->write(json_encode([
                "Error message:" => "Wrong Authorization header!"
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        } else {
            $authArr = mb_split(' ', $auth[0]);
            if ($authArr[0] !== 'Bearer') {
                $response = $this->responseFactory->createResponse();
                $response->getBody()->write(json_encode([
                    "Error message:" => "Not supported authentication method!"
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
            } else {
                $tokenStr = $authArr[1];
                $token = Token::where('token', $tokenStr)->firstOrFail();
            }
        }

        $user = User::where('id', $token->user_id)->firstOrFail();
        //User kikeresése, eltárolása
        if ($user->admin == 0) {
            $response = $this->responseFactory->createResponse();
            $response->getBody()->write(json_encode([
                "Error message:" => "The user is not an admin!"
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        return $handler->handle($request);
    }
}
