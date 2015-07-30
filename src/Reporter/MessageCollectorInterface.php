<?php

namespace Ddeboer\DataImport\Reporter;

/**
 * Description of MessageCollectorInterface
 *
 * @author seydu
 */
interface MessageCollectorInterface 
{
    /**
     * Adds a message.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function addMessage($level, $message, array $context = array());
    
    /**
     * Add a cell (value) level message.
     *
     * @param string $field
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function addCellMessage($field, $level, $message, array $context = array());
    
}
