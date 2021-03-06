<?php

declare(strict_types=1);

namespace ADS\Bundle\EventEngineBundle\Command;

use ADS\Bundle\EventEngineBundle\Util;
use EventEngine\DocumentStore\DocumentStore;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EventEngineDocumentStoresCreateCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'event-engine:document-stores:create'; // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint

    private DocumentStore $documentStore;
    /** @var array<string> */
    private array $aggregates;

    /**
     * @param array<string> $aggregates
     */
    public function __construct(
        DocumentStore $documentStore,
        array $aggregates
    ) {
        parent::__construct();
        $this->documentStore = $documentStore;
        $this->aggregates = $aggregates;
    }

    protected function configure() : void
    {
        $this->setDescription('Create all the document stores.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        foreach ($this->aggregates as $aggregate) {
            $documentStore = Util::fromAggregateNameToDocumentStoreName($aggregate);

            if ($this->documentStore->hasCollection($documentStore)) {
                continue;
            }

            $this->documentStore->addCollection($documentStore);
        }

        return 0;
    }
}
