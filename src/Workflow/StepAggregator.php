<?php

namespace Ddeboer\DataImport\Workflow;

use Ddeboer\DataImport\Exception;
use Ddeboer\DataImport\Exception\UnexpectedTypeException;
use Ddeboer\DataImport\Reader;
use Ddeboer\DataImport\Result;
use Ddeboer\DataImport\Step;
use Ddeboer\DataImport\Step\PriorityStep;
use Ddeboer\DataImport\Workflow;
use Ddeboer\DataImport\Writer;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * A mediator between a reader and one or more writers and converters
 *
 * @author David de Boer <david@ddeboer.nl>
 */
class StepAggregator implements Workflow, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * Identifier for the Import/Export
     *
     * @var string|null
     */
    private $name = null;

    /**
     * @var boolean
     */
    private $skipItemOnFailure = false;

    /**
     * @var \SplPriorityQueue
     */
    private $steps;

    /**
     * @var Writer[]
     */
    private $writers = [];

    /**
     * @var boolean
     */
    protected $shouldStop = false;

    /**
     * @param Reader $reader
     * @param string $name
     */
    public function __construct(Reader $reader, $name = null)
    {
        $this->name = $name;
        $this->reader = $reader;

        // Defaults
        $this->logger = new NullLogger();
        $this->steps = new \SplPriorityQueue();
    }

    /**
     * Add a step to the current workflow
     *
     * @param Step         $step
     * @param integer|null $priority
     *
     * @return $this
     */
    public function addStep(Step $step, $priority = null)
    {
        $priority = null === $priority && $step instanceof PriorityStep ? $step->getPriority() : null;
        $priority = null === $priority ? 0 : $priority;

        $this->steps->insert($step, $priority);

        return $this;
    }

    /**
     * Add a new writer to the current workflow
     *
     * @param Writer $writer
     *
     * @return $this
     */
    public function addWriter(Writer $writer)
    {
        array_push($this->writers, $writer);

        return $this;
    }

    /**
     * 
     * @param string $name
     * @param \DateTime $startTime
     * @param \DateTime $endTime
     * @param integer $count
     * @param \SplObjectStorage $exceptions
     * @param array $items
     * @return Result
     */
    protected function createResult($name, $startTime, $endTime, $count, $exceptions, $items)
    {
        return new Result($name, $startTime, $endTime, $count, $exceptions);
    }
    
    protected function writeItem($item)
    {
        foreach ($this->writers as $writer) {
            $writer->writeItem($item);
        }
    }

    /**
     * 
     * @param Result $result
     */
    protected function finish(Result $result)
    {
        foreach ($this->writers as $writer) {
            $writer->finish();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        $count          = 0;
        $exceptions     = new \SplObjectStorage();
        $processedItems = array();
        $startTime      = new \DateTime;

        foreach ($this->writers as $writer) {
            $writer->prepare();
        }

        if (is_callable('pcntl_signal')) {
            pcntl_signal(SIGTERM, array($this, 'stop'));
            pcntl_signal(SIGINT, array($this, 'stop'));
        }

        // Read all items
        foreach ($this->reader as $index => $item) {
            $processedItems[$index] = $item;

            if (is_callable('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }

            if ($this->shouldStop) {
                break;
            }

            try {
                foreach (clone $this->steps as $step) {
                    if (false === $step->process($item)) {
                        continue 2;
                    }
                }

                if (!is_array($item) && !($item instanceof \ArrayAccess && $item instanceof \Traversable)) {
                    throw new UnexpectedTypeException($item, 'array');
                }

                $this->writeItem($item);
            } catch(Exception $e) {
                if (!$this->skipItemOnFailure) {
                    throw $e;
                }

                $exceptions->attach($e, $index);
                $this->logger->error($e->getMessage());
            }

            $count++;
        }

        $result = $this->createResult(
            $this->name, 
            $startTime, 
            new \DateTime, 
            $count, 
            $exceptions, 
            $processedItems
        );

        $this->finish($result);
        return $result;
    }

    /**
     * Stops processing and force return Result from process() function
     */
    public function stop()
    {
        $this->shouldStop = true;
    }

    /**
     * Sets the value which determines whether the item should be skipped when error occures
     *
     * @param boolean $skipItemOnFailure When true skip current item on process exception and log the error
     *
     * @return $this
     */
    public function setSkipItemOnFailure($skipItemOnFailure)
    {
        $this->skipItemOnFailure = $skipItemOnFailure;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * 
     * @return Writer[]
     */
    protected function getWriters()
    {
        return $this->writers;
    }
}
