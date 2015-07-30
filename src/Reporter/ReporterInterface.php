<?php

namespace Ddeboer\DataImport\Reporter;

use Ddeboer\DataImport\Reporter\MessageCollectorInterface;
/**
 * Description of ReporterInterface
 *
 * @author seydu
 */
interface ReporterInterface 
{
    /**
     * 
     * @param MessageCollectorInterface $collector
     */
    public function setMessageCollector(MessageCollectorInterface $collector);
    /**
     * 
     * @return MessageCollectorInterface $collector
     */
    public function getMessageCollector(MessageCollectorInterface $collector);
}
