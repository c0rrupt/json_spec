<?php

namespace JsonSpec\PhpSpec;

use JsonSpec\Matcher\MatcherOptions;

class MatcherOptionsFactory
{

    private $excludedKeys;

    /**
     * @param array $excludedKeys
     */
    public function __construct(array $excludedKeys = array())
    {
        $this->excludedKeys = $excludedKeys;
    }

    /**
     * @return MatcherOptions
     */
    public function createConfiguration()
    {
        return new MatcherOptions($this->excludedKeys);
    }

}