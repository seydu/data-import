<?php

namespace Ddeboer\DataImport\Reporter;

/**
 * Represents a message
 *
 * @author seydu
 */
class Message
{
    private $level;
    private $message;
    private $context;
    public function __construct($level, $message, array $context = array())
    {
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
    }
    function setLevel($level) 
    {
        $this->level = $level;
    }

    function setMessage($message) 
    {
        $this->message = $message;
    }

    function setContext($context) 
    {
        $this->context = $context;
    }    
}
