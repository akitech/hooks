<?php

namespace hooks\MVC\TemplateEngine\Directives;

use Razr\Directive\Directive;
use Razr\Token;
use Razr\TokenStream;

class StringDirective extends Directive
{
    /**
     * Constructor.
     */
    private $escape, $function;


    public function __construct()
    {
        $this->name = 'string';
        $this->escape = false;
        $this->function = "string";
    }

    /**
     * Calls the function with an array of arguments.
     *
     * @param  array $args
     * @return mixed
     */
    public function call(array $args = array())
    {
        return call_user_func_array($this->function, $args);
    }

    /**
     * @{inheritdoc}
     */
    public function parse(TokenStream $stream, Token $token)
    {
        if ($stream->nextIf($this->name) && $stream->expect('(')) {

            $out = sprintf("\$this->getDirective('%s')->call(%s)", $this->name, $stream->test('(') ? 'array' . $this->parser->parseExpression() : '');

            if ($this->escape) {
                $out = sprintf("\$this->escape(%s)", $out);
            }

            return sprintf("echo(%s)", $out);
        }
    }
}