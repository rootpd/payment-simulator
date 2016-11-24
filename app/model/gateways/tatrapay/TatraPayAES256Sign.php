<?php

namespace App\Gateways\Tatrapay;

class TatraPayAES256Sign
{
    private $sharedSecret;

    private $mid;

    private $amt;

    private $curr;

    private $vs;

    private $cs;

    private $rurl;

    public function __construct($sharedSecret, $mid, $amt, $curr, $vs, $cs, $rurl)
    {
        if (strlen($sharedSecret) == 64) {
            $sharedSecret = pack('H*', $sharedSecret);
        }

        $this->sharedSecret = $sharedSecret;
        $this->mid = $mid;
        $this->amt = $amt;
        $this->curr = $curr;
        $this->vs = $vs;
        $this->cs = $cs;
        $this->rurl = $rurl;
    }

    public function sign()
    {
        $base = "{$this->mid}{$this->amt}{$this->curr}{$this->vs}{$this->cs}{$this->rurl}";

        $bytesHash = sha1($base, TRUE);

        // vezmeme prvych 16 bytov
        $bytesHash = substr($bytesHash, 0, 16);

        $aes = mcrypt_module_open (MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($aes), MCRYPT_RAND);
        mcrypt_generic_init($aes, $this->sharedSecret, $iv);
        $bytesSign = mcrypt_generic($aes, $bytesHash);
        mcrypt_generic_deinit($aes);
        mcrypt_module_close($aes);

        $sign = strtoupper(bin2hex($bytesSign));

        return $sign;
    }

    public function returnUrlSign($result)
    {
        $base = "{$this->vs}{$result}";

        $bytesHash = sha1($base, TRUE);

        // vezmeme prvych 16 bytov
        $bytesHash = substr($bytesHash, 0, 16);

        $aes = mcrypt_module_open (MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($aes), MCRYPT_RAND);
        mcrypt_generic_init($aes, $this->sharedSecret, $iv);
        $bytesSign = mcrypt_generic($aes, $bytesHash);
        mcrypt_generic_deinit($aes);
        mcrypt_module_close($aes);

        $sign = strtoupper(bin2hex($bytesSign));

        return $this->rurl . "?VS={$this->vs}&RES={$result}&SIGN={$sign}";
    }
}