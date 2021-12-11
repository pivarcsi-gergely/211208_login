<?php

use Petrik\Loginapp\Middlewares\AuthMiddleware;
use Petrik\Loginapp\User;
use Petrik\Loginapp\Token;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

return function (Slim\App $app) {
    $app->get('/', function (Request $request, Response $response, $args) {
        $response->getBody()->write("Hello world!");
        return $response;
    });
    $app->post('/register', function (Request $request, Response $response) {
        $userData = json_decode($request->getBody(), true);
        $user = new User();
        $user->email = $userData['email'];
        $user->password = password_hash($userData['password'], PASSWORD_DEFAULT);
        $user->save();
        $response->getBody()->write($user->toJson());
        return $response->withHeader('Content-Type', 'application/json')->withStatus(204);
    });

    $app->post('/login', function (Request $request, Response $response, $args) {
        $loginData = json_decode($request->getBody(), true);
        //$loginData validálás!
        $email = $loginData['email'];
        $password = $loginData['password'];
        $user = User::where('email', $email)->firstOrFail();
        if (!password_verify($password, $user->password)) {
            $response->getBody()->write(json_encode([
                'Hibauzenet' => 'Hibas email vagy jelszo!'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        } else {
            $token = new Token();
            $token->user_id = $user->id;
            $token->token = bin2hex(random_bytes(16));
            //Check, hogy a token nem létezik az ab-ban, pl. lehetne unique stb.
            $token->save();
            $response->getBody()->write(json_encode([
                'email' => $user->email,
                'token' => $token->token
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    });

    $app->group('/api', function (RouteCollectorProxy $group) {
        $group->get('/hello', function (Request $request, Response $response, $args) {
            $response->getBody()->write(json_encode([
                'Hello' => 'Móni',
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        });
    })->add(new AuthMiddleware($app->getResponseFactory()));
};
