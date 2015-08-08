<?php

namespace Ddeboer\DataImport\Exception;

/**
 * @author David de Boer <david@ddeboer.nl>
 */
class DuplicateHeadersException extends ReaderException
{
    private $duplicates;
    /**
     * @param array $duplicates
     */
    public function __construct(array $duplicates)
    {
        parent::__construct(sprintf('File contains duplicate headers: %s', implode($duplicates, ', ')));
        $this->duplicates = $duplicates;
    }
    
    public function getDuplicates()
    {
        return $this->duplicates;
    }
}
