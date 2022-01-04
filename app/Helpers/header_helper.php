<?php

use Firebase\JWT\JWT;

function getHeaderJWT($header)
{
    $token = explode(' ', $header)[1];
    $decoded = JWT::decode($token, getenv('SECRET_KEY_JWT'), ['HS256']);
    return $decoded;
}
