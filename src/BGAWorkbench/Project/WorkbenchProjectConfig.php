<?php

namespace BGAWorkbench\Project;

use BGAWorkbench\Utils;
use BGAWorkbench\Utils\FileUtils;
use PhpOption\Option;

class WorkbenchProjectConfig
{
    /**
     * @var \SplFileInfo
     */
    private $directory;

    /**
     * @var boolean
     */
    private $useComposer;

    /**
     * @var string[]
     */
    private $extraSrcPaths;

    /**
     * @var string
     */
    private $testDbNamePrefix;

    /**
     * @var string
     */
    private $testDbUsername;

    /**
     * @var string
     */
    private $testDbPassword;

    /**
     * @var string
     */
    private $linterPhpBin;

    /**
     * @var Option
     */
    private $sftpConfig;

    /**
     * @param \SplFileInfo $directory
     * @param bool $useComposer
     * @param string[] $extraSrcPaths
     * @param string $testDbNamePrefix
     * @param string $testDbUsername
     * @param string $testDbPassword
     * @param string $linterPhpBin
     * @param Option $sftpConfig
     */
    public function __construct(
        \SplFileInfo $directory,
        bool $useComposer,
        array $extraSrcPaths,
        string $testDbNamePrefix,
        string $testDbUsername,
        string $testDbPassword,
        string $linterPhpBin,
        Option $sftpConfig
    ) {

        $this->directory = $directory;
        $this->useComposer = $useComposer;
        $this->extraSrcPaths = $extraSrcPaths;
        $this->testDbNamePrefix = $testDbNamePrefix;
        $this->testDbUsername = $testDbUsername;
        $this->testDbPassword = $testDbPassword;
        $this->linterPhpBin = $linterPhpBin;
        $this->sftpConfig = $sftpConfig;
    }

    /**
     * @return string
     */
    public function getTestDbNamePrefix() : string
    {
        return $this->testDbNamePrefix;
    }

    /**
     * @return string
     */
    public function getTestDbUsername() : string
    {
        return $this->testDbUsername;
    }

    /**
     * @return string
     */
    public function getTestDbPassword() : string
    {
        return $this->testDbPassword;
    }

    /**
     * @return string
     */
    public function getLinterPhpBin() : string
    {
        return $this->linterPhpBin;
    }

    /**
     * @return Option
     */
    public function getDeployConfig() : Option
    {
        return $this->sftpConfig;
    }

    /**
     * @return bool
     */
    public function getUseComposer() : bool
    {
        return $this->useComposer;
    }

    /**
     * @return string[]
     */
    public function getExtraSrcPaths(): array
    {
        return $this->extraSrcPaths;
    }

    /**
     * @return Project
     */
    public function loadProject() : Project
    {
        $versionFile = FileUtils::joinPathToFileInfo($this->directory, 'version.php');

        $GAME_VERSION_PREFIX = 'game_version_';
        $variableName = Utils::getVariableNameFromFile(
            $versionFile,
            function ($name) use ($GAME_VERSION_PREFIX) {
                return strpos($name, $GAME_VERSION_PREFIX) === 0;
            }
        )->getOrThrow(
            new \InvalidArgumentException(
                "File {$versionFile->getPathname()} missing version variable {$GAME_VERSION_PREFIX}_%%project_name%%"
            )
        );
        $projectName = substr($variableName, strlen($GAME_VERSION_PREFIX));

        if ($this->useComposer) {
            return new ComposerProject($this->directory, $projectName, $this->extraSrcPaths);
        }
        return new Project($this->directory, $projectName);
    }
}
