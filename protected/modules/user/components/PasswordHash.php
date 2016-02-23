<?php
/**
 * Represents hashed password
 */

namespace user\components;

use KoKoKo\assert\Assert;

class PasswordHash
{
    private $hash;

    public function __construct($hash)
    {
        Assert::assert($hash, 'hash')->string();

        if (count(explode('$', $hash)) !== 4) {
            // TODO: better format validation
            throw new \InvalidArgumentException('Wrong hash format');
        }

        $this->hash = $hash;
    }

    public static function from($password)
    {
        Assert::assert($password, 'password')->string();

        $hash = self::hashPassword($password);

        return new self($hash);
    }

    public function getValue()
    {
        return $this->hash;
    }

    /**
     * Verify other, not hashed password
     *
     * @param  string $password
     *
     * @return bool
     */
    public function verify($password)
    {
        Assert::assert($password, 'password')->string();

        $hash = $this->hashPassword(
            $password,
            $this->getSalt()
        );

        return $hash === $this->hash;
    }

    /**
     * has function from Django
     */
    private static function hashPassword($password, $salt = false)
    {
        $algorythm = 'pbkdf2_sha256';
        $iterations = 10000;
        if (!$salt) {
            $salt = self::generateRandStr(12);
        }

        $hash = self::pbkdf2('sha256', $password, $salt, $iterations, false, true);
        $hash = base64_encode($hash);
        return $algorythm . '$' . $iterations . '$' . $salt . '$' . $hash;
    }

    /**
     * Implementation of the PBKDF2 key derivation function as described in
     * RFC 2898.
     *
     * @param string $PRF Hash algorithm.
     * @param string $P Password.
     * @param string $S Salt.
     * @param int $c Iteration count.
     * @param mixed $dkLen Derived key length (in octets). If $dkLen is FALSE
     *                     then length will be set to $PRF output length (in
     *                     octets).
     * @param bool $raw_output When set to TRUE, outputs raw binary data. FALSE
     *                         outputs lowercase hexits.
     * @return mixed Derived key or FALSE if $dkLen > (2^32 - 1) * hLen (hLen
     *               denotes the length in octets of $PRF output).
     */
    private static function pbkdf2($PRF, $P, $S, $c, $dkLen = false, $raw_output = false)
    {
        //default $hLen is $PRF output length
        $hLen = strlen(hash($PRF, '', true));
        if ($dkLen === false) {
            $dkLen = $hLen;
        }

        if ($dkLen <= (pow(2, 32) - 1) * $hLen) {
            $DK = '';

            //create key
            for ($block = 1; $block <= $dkLen; $block++) {
                //initial hash for this block
                $ib = $h = hash_hmac($PRF, $S . pack('N', $block), $P, true);

                //perform block iterations
                for ($i = 1; $i < $c; $i++) {
                    $ib ^= ($h = hash_hmac($PRF, $h, $P, true));
                }

                //append iterated block
                $DK .= $ib;
            }

            $DK = substr($DK, 0, $dkLen);
            if (!$raw_output) {
                $DK = bin2hex($DK);
            }

            return $DK;
        } else {
            throw new \DomainException('Derived key too long');
        }
    }

    private static function generateRandStr($length)
    {
        return substr(str_shuffle(
            str_repeat(
                'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
                5
            )
        ), 0, $length);
    }

    /**
     *  Возвращает соль для текущего юзера
     */
    private function getSalt()
    {
        list($alg, $iterCount, $salt, $hash) = explode('$', $this->hash);
        return $salt;
    }
}
