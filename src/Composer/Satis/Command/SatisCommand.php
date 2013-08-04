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

use InvalidArgumentException;
use Composer\Command\Command;
use Composer\Json\JsonFile;
use Composer\Util\RemoteFilesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class SatisCommand extends Command
{
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->addArgument('file', InputArgument::OPTIONAL, 'Json config file to use', './satis.json');
        $this->addArgument('output-dir', InputArgument::OPTIONAL, 'The directory to use as the web root.', null);
    }

    /**
     * Read the config from disk, or a remote source
     *
     * @param InputInterface $input
     * @throws InvalidArgumentException If the file could not be found
     * @return array   The config
     */
    public function readConfig(InputInterface $input)
    {
        $configFile = $input->getArgument('file');
        if (preg_match('{^https?://}i', $input)) {
            $rfs = new RemoteFilesystem($this->getIO());
            $contents = $rfs->getContents(parse_url($configFile, PHP_URL_HOST), $configFile, false);
            $config = JsonFile::parseJson($contents, $configFile);
        } else {
            $file = new JsonFile($configFile);
            if (!$file->exists()) {
                throw new InvalidArgumentException('<error>File not found: '.$configFile.'</error>');
            }
            $config = $file->read();
        }

        if (!$outputDir = $input->getArgument('output-dir')) {
            $outputDir = isset($config['output-dir']) ? $config['output-dir'] : './web';
        }
        if (!is_dir($outputDir)) {
            mkdir($outputDir);
        }

        $config['output-dir'] = $outputDir;

        return $config;
    }
}