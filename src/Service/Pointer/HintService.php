<?php

namespace App\Service\Pointer;

use Psr\Log\LoggerInterface;
use App\Service\Pointer\HintParser;
use App\Service\Pointer\HintMutator;
use App\Service\Pointer\HintValidator;
use App\Service\Pointer\HintMiddleware;
use App\Service\Pointer\Style\Parser;
use App\Service\Pointer\Style\ContentMutator;
use App\Service\Pointer\Style\RequestMutator;
use App\Service\Pointer\Style\ContentValidator;
use App\Service\Pointer\Style\RequestValidator;

class HintService
{
    /**
     * Return hits for the request
     *
     * @param string $target
     * @param LoggerInterface $logger
     * @return HintParser
     */
    public static function parseHintsRequest(string $target, LoggerInterface $logger): HintParser
    {
        $server = new HintParser($target . '_list');
        $styleValidator = new RequestValidator($logger);
        $hintMiddleware = new HintValidator($styleValidator, $target);
        $styleMutator = new RequestMutator($server);
        $hintMiddleware->linkWith(new HintMutator($styleMutator, $target));
        $server->setMiddleware($hintMiddleware);

        return $server;
    }

    /**
     * Return hints for the content
     *
     * @param string $target
     * @param LoggerInterface $logger
     * @return HintParser
     */
    public static function parseHintsContent(string $target, LoggerInterface $logger): HintParser
    {
        $server = new HintParser($target . '_list_item');
        $styleValidator = new ContentValidator($logger);
        $hintMiddleware = new HintValidator($styleValidator, $target);
        $styleMutator = new ContentMutator();
        $hintMiddleware->linkWith(new HintMutator($styleMutator, $target));
        $server->setMiddleware($hintMiddleware);

        return $server;
    }
}
