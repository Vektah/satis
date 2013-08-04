<?php
/*
 * This file is part of Satis.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *     Nils Adermann <naderman@naderman.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\Satis\Command;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpWeb extends SatisCommand
{
    protected function configure()
    {
        $this->setName('dump-web');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->readConfig($input);
        $outputDir = $config['output-dir'];

        // Check if the twig template has been overridden in config.
        if (isset($config['twig-template'])) {
            $template = $config['twig-template'];
        } else {
            $template = realpath(__DIR__ . '/../../../../views/index.html.twig');
        }

        $name = isset($config['name']) ? $config['name'] : 'Satis Cache';
        $embedData = isset($config['embed-data']) ? $config['embed-data'] : false;

        $templateDir = dirname($template);
        $templateName = basename($template);

        // Javascript components pulled in by composer.
        $componentDir = realpath(__DIR__ . '/../../../../components');

        $output->writeln('<info>Writing web view</info>');
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem($templateDir));

        copy("$componentDir/handlebars/handlebars-built.js", "$outputDir/handlebars-built.js");
        copy("$componentDir/jquery/jquery.min.js", "$outputDir/jquery.min.js");
        copy("$templateDir/styles.css", "$outputDir/styles.css");
        $context =array(
            'name' => $name,
            // If embedding, strip whitespace from packages json...
            'data' => $embedData ? json_encode(json_decode(file_get_contents("$outputDir/packages.json"))) : null
        );

        $content = $twig->render($templateName, $context);

        file_put_contents("$outputDir/index.html", $content);
    }
}
