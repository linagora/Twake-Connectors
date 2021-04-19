<?php


namespace BuiltInConnectors\Connectors\Rss\Command;

use Common\Commands\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RssCheckCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName("twake:connectors:rss")
            ->setDescription("Run rss connector even checker");
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $service = $this->getContainer()->get('connectors.rss.event');
        $service->checkRss();
    }

}