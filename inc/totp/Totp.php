<?php
require_once 'FixedBitNotation.php';

class Totp {

    private $codePeriod = 30;
    private $periodSize = 30;
    private $instanceTime;
    private $passCodeLength = 6;
    private $pinModulo = 10 ** 6;

    public function generateSecret(): string {
        return (new FixedBitNotation(5, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', true, true))->encode(random_bytes(10));
    }

    private function hashToInt(string $bytes, int $start): int {
        return unpack('N', substr(substr($bytes, $start), 0, 4))[1];
    }

    public function getCode($secret, /* \DateTimeInterface */$time = null): string {
        if (null === $time) {
            $time = $this->instanceTime;
        }

        if ($time instanceof \DateTimeInterface) {
            $timeForCode = floor(time() / $this->periodSize);
        } else {
            @trigger_error(
                'Passing anything other than null or a DateTimeInterface to $time is deprecated as of 2.0 '.
                'and will not be possible as of 3.0.',
                E_USER_DEPRECATED
            );
            $timeForCode = $time;
        }

        $base32 = new FixedBitNotation(5, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', true, true);
        $secret = $base32->decode($secret);

        $timeForCode = str_pad(pack('N', $timeForCode), 8, \chr(0), STR_PAD_LEFT);

        $hash = hash_hmac('sha1', $timeForCode, $secret, true);
        $offset = \ord(substr($hash, -1));
        $offset &= 0xF;

        $truncatedHash = $this->hashToInt($hash, $offset) & 0x7FFFFFFF;

        return str_pad((string) ($truncatedHash % $this->pinModulo), $this->passCodeLength, '0', STR_PAD_LEFT);
    }

    public function checkCode($secret, $code, $discrepancy = 1): bool {
        /**
         * Discrepancy is the factor of periodSize ($discrepancy * $periodSize) allowed on either side of the
         * given codePeriod. For example, if a code with codePeriod = 60 is generated at 10:00:00, a discrepancy
         * of 1 will allow a periodSize of 30 seconds on either side of the codePeriod resulting in a valid code
         * from 09:59:30 to 10:00:29.
         *
         * The result of each comparison is stored as a timestamp here instead of using a guard clause
         * (https://refactoring.com/catalog/replaceNestedConditionalWithGuardClauses.html). This is to implement
         * constant time comparison to make side-channel attacks harder. See
         * https://cryptocoding.net/index.php/Coding_rules#Compare_secret_strings_in_constant_time for details.
         * Each comparison uses hash_equals() instead of an operator to implement constant time equality comparison
         * for each code.
         */
        $periods = floor($this->codePeriod / $this->periodSize);

        $result = 0;
        for ($i = -$discrepancy; $i < $periods + $discrepancy; ++$i) {
            $dateTime = new \DateTimeImmutable('@'.(time() - ($i * $this->periodSize)));
            $result = hash_equals($this->getCode($secret, $dateTime), $code) ? time() : $result;
        }

        return $result > 0;
    }
}
?>