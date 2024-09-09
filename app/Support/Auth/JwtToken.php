<?php

namespace App\Support\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Carbon;
use stdClass;

class JwtToken
{
    public const string ALGORITHM = 'HS256';

    private string $token;
    private Carbon $expiredAt;

    public static function make($token, Carbon $expiredAt): self
    {
        return new self($token, $expiredAt);
    }

    public function __construct(string $token, Carbon $expiredAt)
    {
        $this->token = $token;
        $this->expiredAt = $expiredAt;
    }

    public static function generate(int $userId): self
    {
        $expiredAt = now()->addSeconds(config('jwt.lifetime'));

        $payload = [
            'user_id' => $userId,
            'expires_in' => $expiredAt->timestamp,
        ];

        $token = JWT::encode($payload, config('jwt.secret_key'), self::ALGORITHM);

        return self::make($token, $expiredAt);
    }

    public function getBearer(): string
    {
        return $this->token;
    }

    public function getExpiredAt(): Carbon
    {
        return $this->expiredAt;
    }

    /**
     * @param string $jwt
     * @return stdClass
     */
    public static function decode(string $jwt): stdClass
    {
        return JWT::decode($jwt, new Key(config('jwt.secret_key'), self::ALGORITHM));
    }
}
