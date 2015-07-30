<?php

namespace Ddeboer\DataImport\Reporter;

/**
 * Description of MessageCollector
 *
 * @author seydu
 */
class MessageCollector implements MessageCollectorInterface 
{
    private $rowMessages = array();
    private $cellMessages = array();
    private $rawData;
    public function __construct($data)
    {
        $this->rawData = $data;
    }

    public function addMessage($level, $message, array $context = array())
    {
        $this->rowMessages[$level][] = new Message($level, $message, $context);
    }
    
    /**
     * Add a message.
     *
     * @param string $column
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function addCellMessage($column, $level, $message, array $context = array())
    {
        $this->cellMessages[$column][$level][] = new Message($level, $message, $context);
    }
    
    public function getMessages()
    {
        return $this->messages;
    }
    
    public function getCellMessages()
    {
        return $this->cellMessages;
    }
    
}
