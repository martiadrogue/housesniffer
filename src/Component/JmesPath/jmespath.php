<?php

namespace App\Component\JmesPath;

if (!function_exists(__NAMESPACE__ . '\search')) {
    /**
     * Returns data from the input array that matches a JMESPath expression.
     *
     * @param string $expression Expression to search.
     * @param mixed $data Data to search.
     *
     * @return mixed
     */
    function search($expression, $data)
    {
        $content =  \jmespath\search($expression, $data);

        return $content ?? '';
    }
}
