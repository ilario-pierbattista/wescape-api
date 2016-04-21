<?php
/**
 * Created by PhpStorm.
 * User: ilario
 * Date: 21/04/16
 * Time: 13.00
 */

namespace Wescape\CoreBundle\Test;


use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class WebTestCase extends \Liip\FunctionalTestBundle\Test\WebTestCase
{
    protected function setUp() {
        parent::setUp();

        $content = $this->executeCommand("doctrine:schema:drop", ['--force' => "true"]);
        echo($content);
        $content = $this->executeCommand("doctrine:schema:create");
        echo($content);
    }


    protected function executeCommand($command, array $options = []) {
        $options["--env"] = "test";
        $options["--quiet"] = "true";
        $options["command"] = $command;

        $kernel = $this->getContainer()->get("kernel");
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput($options);
        $output = new BufferedOutput();
        $application->run($input, $output);
        $content = $output->fetch();
        
        return $content;
    }
}