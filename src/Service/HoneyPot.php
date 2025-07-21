<?php

namespace App\Service;

use Symfony\Component\Validator\Constraints\IsNull;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class HoneyPot extends IsNull
{
    public const HONEYPOT = 'ya6jA8ygGji0QS4Ga04cqUF3h33pcFQrB352sxT5O5kraolmHg';

    protected static $errorNames = [
        self::HONEYPOT => 'HONEYPOT',
    ];
}