<?php

namespace BGAWorkbench\External;

use BGAWorkbench\Project\ConfigFileConfiguration;
use BGAWorkbench\Project\DeployConfig;
use BGAWorkbench\Project\WorkbenchProjectConfig;
use BGAWorkbench\Utils\FileUtils;
use PhpOption\Option;
use PhpOption\Some;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class WorkbenchProjectConfigSerialiser
{
    const FILENAME = 'bgaproject.yml';

    /**
     * @param \SplFileInfo $directory
     * @return bool
     */
    public static function configExists(\SplFileInfo $directory) : bool
    {
        return self::getConfigContents($directory)->isDefined();
    }

    /**
     * @param \SplFileInfo $directory
     * @return Option
     */
    private static function getConfigContents(\SplFileInfo $directory) : Option
    {
        $filepath = FileUtils::joinPath($directory, self::FILENAME);
        $content = new Some(@file_get_contents($filepath));
        return $content->filter(function($content) {
            return $content !== false;
        });
    }

    /**
     * @return WorkbenchProjectConfig
     */
    public static function readFromCwd() : WorkbenchProjectConfig
    {
        return self::readFromDirectory(new \SplFileInfo(getcwd()));
    }

    /**
     * @param \SplFileInfo $directory
     * @return WorkbenchProjectConfig
     */
    public static function readFromDirectory(\SplFileInfo $directory) : WorkbenchProjectConfig
    {
        $rawContent = self::getConfigContents($directory)
            ->getOrThrow(new \InvalidArgumentException("Couldn't read project config in {$directory->getPathname()}"));

        try {
            $rawConfig = Yaml::parse($rawContent);
        } catch (ParseException $e) {
            throw new \InvalidArgumentException("Invalid YAML in file {$directory->getPathname()}", 0, $e);
        }

        return self::read($directory, $rawConfig);
    }

    /**
     * @param \SplFileInfo $directory
     * @param array $rawConfig
     * @return WorkbenchProjectConfig
     */
    private static function read(\SplFileInfo $directory, array $rawConfig) : WorkbenchProjectConfig
    {
        $processor = new Processor();
        $processed = $processor->processConfiguration(new ConfigFileConfiguration(), [$rawConfig]);
        return new WorkbenchProjectConfig(
            $directory,
            $processed['useComposer'],
            $processed['extraSrc'],
            $processed['testDb']['namePrefix'],
            $processed['testDb']['user'],
            $processed['testDb']['pass'],
            $processed['linterPhpBin'],
            Option::fromValue(isset($processed['sftp']) ? $processed['sftp'] : null)->map(function (array $raw) {
                return new DeployConfig($raw['host'], $raw['user'], $raw['pass']);
            })
        );
    }

    /**
     * @param \SplFileInfo $directory
     * @param array $rawConfig
     */
    public static function writeToDirectory(\SplFileInfo $directory, array $rawConfig)
    {
        $processor = new Processor();
        $processed = $processor->processConfiguration(new ConfigFileConfiguration(), [$rawConfig]);

        $content = Yaml::dump($processed, 2, 4, Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE);
        $result = @file_put_contents(FileUtils::joinPath($directory->getPathname(), self::FILENAME), $content);
        if ($result === false) {
            throw new \InvalidArgumentException('Error writing config file');
        }
    }
}
