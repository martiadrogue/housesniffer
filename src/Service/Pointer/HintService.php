<?php

namespace App\Service\Pointer;

use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;
use App\Service\Pointer\HintParser;
use App\Service\Pointer\HintMutator;
use App\Service\Pointer\HintValidator;
use App\Service\Pointer\HintMiddleware;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\Stopwatch\Stopwatch;
use App\Service\Pointer\Style\ContentParser;
use App\Service\Pointer\Style\RequestParser;
use App\Service\Pointer\Style\ContentMutator;
use App\Service\Pointer\Style\RequestMutator;
use App\Service\Pointer\Style\ContentValidator;
use App\Service\Pointer\Style\RequestValidator;

class HintService
{
    private const PATH = "config/hints/";
    private string $target;
    private HintValidator $hintValidator;
    private HintMiddleware $hintMiddleware;

    public function __construct(string $target, HintMiddleware $hintMiddleware)
    {
        $this->target = $target;
        $this->hintMiddleware = $hintMiddleware;
    }

    /**
     * Return hits for the request
     *
     * @param string $target
     * @param integer $page
     * @return mixed[]
     */
    public static function parseHintsRequest(string $target, LoggerInterface $logger): HintParser
    {
        $styleParser = new RequestParser();
        $styleValidator = new RequestValidator($logger);
        $server = new HintParser($styleParser, $target);
        $styleMutator = new RequestMutator($server);

        $hintMiddleware = new HintValidator($styleValidator, $target);
        $hintMiddleware->linkWith(new HintMutator($styleMutator, $target));
        $server->setMiddleware($hintMiddleware);

        return $server;
    }

    /**
     * Return hints for the content
     *
     * @param string $target
     * @return mixed[]
     */
    public static function parseHintsContent(string $target, LoggerInterface $logger): HintParser
    {
        $styleParser = new ContentParser();
        $styleValidator = new ContentValidator($logger);
        $styleMutator = new ContentMutator();

        $server = new HintParser($styleParser, $target);
        $hintMiddleware = new HintValidator($styleValidator, $target);
        $hintMiddleware->linkWith(new HintMutator($styleMutator, $target));
        $server->setMiddleware($hintMiddleware);

        return $server;
    }
}
