<?php

declare(strict_types=1);

namespace ADS\Bundle\EventEngineBundle\Command;

use ADS\Bundle\EventEngineBundle\Util;
use EventEngine\DocumentStore\DocumentStore;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\StreamName;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EventEngineDataResetCommand extends Command
{
    /** @var string  */
    protected static $defaultName = 'event-engine:data:reset'; // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint

    private EventStore $eventStore;
    private DocumentStore $documentStore;
    /** @var array<string> */
    private array $aggregates;

    /**
     * @param array<string> $aggregates
     */
    public function __construct(
        EventStore $eventStore,
        DocumentStore $documentStore,
        array $aggregates
    ) {
        $this->eventStore = $eventStore;
        $this->documentStore = $documentStore;
        $this->aggregates = $aggregates;

        parent::__construct();
    }

    protected function configure() : void
    {
        $this->setDescription('Reset all the streams and document stores');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        foreach ($this->aggregates as $aggregate) {
            $documentStore = Util::fromAggregateNameToDocumentStoreName($aggregate);
            $streamName = Util::fromAggregateNameToStreamName($aggregate);
            $streamNameObject = new StreamName($streamName);

            if ($this->eventStore->hasStream($streamNameObject)) {
                $this->eventStore->delete($streamNameObject);
            }

            if (! $this->documentStore->hasCollection($documentStore)) {
                continue;
            }

            $this->documentStore->dropCollection($documentStore);
        }

        /** @var Application $application */
        $application = $this->getApplication();

        $createEventStreams = $application->find('event-engine:event-streams:create');
        $createDocumentStores = $application->find('event-engine:document-stores:create');

        $createEventStreams->run($input, $output);
        $createDocumentStores->run($input, $output);

        return 0;
    }
}
