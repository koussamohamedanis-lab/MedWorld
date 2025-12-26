<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JWTHelper
{
    private static function getSecretKey()
    {
        return env('JWT_SECRET', 'medworld');
    }

    private static function getExpiration()
    {
        return env('JWT_EXPIRATION', 1440);
    }

    public static function generateToken($userId, $userType)
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + (self::getExpiration() * 60);

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'userId' => $userId,
            'userType' => $userType,
        ];

        return JWT::encode($payload, self::getSecretKey(), 'HS256');
    }

    public static function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key(self::getSecretKey(), 'HS256'));
            return (object) [
                'userId' => $decoded->userId,
                'userType' => $decoded->userType,
            ];
        } catch (Exception $e) {
            return null;
        }
    }
}
