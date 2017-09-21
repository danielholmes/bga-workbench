<?php

namespace BGAWorkbench\Project;

use BGAWorkbench\Builder\BuildInstruction;
use BGAWorkbench\Builder\CopyFiles;
use BGAWorkbench\Utils;
use BGAWorkbench\Utils\FileUtils;
use Illuminate\Filesystem\Filesystem;
use Nette\Reflection\AnnotationsParser;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use PhpOption\Option;
use Functional as F;

class Project
{
    /**
     * @var \SplFileInfo
     */
    private $directory;

    /**
     * @var string
     */
    private $name;

    /**
     * @param \SplFileInfo $directory
     * @param string $name
     */
    public function __construct(\SplFileInfo $directory, string $name)
    {
        $this->directory = $directory;
        $this->name = $name;
    }

    /**
     * @return BuildInstruction[]
     */
    public function getBuildInstructions()
    {
        $fileSystem = new Filesystem();
        $copyFiles = F\map(
            $this->getRootRequiredFiles(),
            function (SplFileInfo $file) use ($fileSystem) {
                if ($file == $this->getGameProjectFile()) {
                    return $this->createGameProjectFileBuildInstruction($fileSystem, $file);
                }
                return new CopyFiles($fileSystem, $file);
            }
        );
        $copyDirs = F\map(
            F\filter(
                $this->getStandardDirectories(),
                function (\SplFileInfo $dir) use ($fileSystem) {
                    return $fileSystem->exists($dir->getPathname());
                }
            ),
            function (SplFileInfo $dir) use ($fileSystem) {
                return new CopyFiles($fileSystem, $dir);
            }
        );
        return array_merge($copyFiles, $copyDirs);
    }

    /**
     * @param Filesystem $fileSystem
     * @param SplFileInfo $file
     * @return BuildInstruction
     */
    protected function createGameProjectFileBuildInstruction(Filesystem $fileSystem, SplFileInfo $file)
    {
        return new CopyFiles($fileSystem, $file);
    }

    /**
     * @return \SplFileInfo
     */
    public function getDirectory() : \SplFileInfo
    {
        return $this->directory;
    }

    /**
     * @return SplFileInfo
     */
    public function getBuildDirectory() : SplFileInfo
    {
        return $this->getProjectFile('build');
    }

    /**
     * @return SplFileInfo
     */
    public function getDistDirectory() : SplFileInfo
    {
        return $this->getProjectFile('dist');
    }

    /**
     * @return SplFileInfo[]
     */
    public function getDistFiles() : array
    {
        return $this->getPathFiles($this->getDistDirectory(), $this->getDistDirectory(), []);
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getGameProjectFileRelativePathname() : string
    {
        return "{$this->name}.game.php";
    }

    /**
     * @return SplFileInfo
     */
    public function getGameProjectFile() : SplFileInfo
    {
        return $this->getProjectFile($this->getGameProjectFileRelativePathname());
    }

    /**
     * @return string
     */
    public function getActionProjectFileRelativePathname() : string
    {
        return "{$this->name}.action.php";
    }

    /**
     * @return string
     */
    public function getGameinfosProjectFileRelativePathname() : string
    {
        return "gameinfos.inc.php";
    }

    /**
     * @return string
     */
    private function getDbModelSqlRelativePathname() : string
    {
        return "dbmodel.sql";
    }

    /**
     * @return SplFileInfo
     */
    public function getDbModelSqlFile() : SplFileInfo
    {
        return $this->getProjectFile($this->getDbModelSqlRelativePathname());
    }

    /**
     * @return string
     */
    private function getStatesFileName() : string
    {
        return 'states.inc.php';
    }

    /**
     * @return SplFileInfo[]
     */
    private function getStandardDirectories() : array
    {
        return F\map(
            ['img', 'misc', 'modules'],
            function ($name) {
                return $this->getProjectFile($name);
            }
        );
    }

    /**
     * @return array
     */
    private function getRootRequiredFiles() : array
    {
        return F\map(
            [
                $this->getActionProjectFileRelativePathname(),
                $this->getGameProjectFileRelativePathname(),
                "{$this->name}.view.php",
                "{$this->name}.css",
                "{$this->name}.js",
                "{$this->name}_{$this->name}.tpl",
                $this->getDbModelSqlRelativePathname(),
                $this->getGameinfosProjectFileRelativePathname(),
                "gameoptions.inc.php",
                "material.inc.php",
                $this->getStatesFileName(),
                "stats.inc.php",
                "version.php"
            ],
            function ($name) {
                return $this->getProjectFile($name);
            }
        );
    }

    /**
     * @return SplFileInfo[]
     */
    public function getRequiredFiles() : array
    {
        return array_merge(
            $this->getRootRequiredFiles(),
            F\map(
                [
                    "img" . DIRECTORY_SEPARATOR . "game_box.png",
                    "img" . DIRECTORY_SEPARATOR . "game_box75.png",
                    "img" . DIRECTORY_SEPARATOR . "game_box180.png",
                    "img" . DIRECTORY_SEPARATOR . "game_icon.png",
                    "img" . DIRECTORY_SEPARATOR . "publisher.png"
                ],
                function ($name) {
                    return $this->getProjectFile($name);
                }
            )
        );
    }

    /**
     * @return SplFileInfo[]
     */
    public function getDevelopmentPhpFiles() : array
    {
        return F\filter(
            $this->getDevelopmentSourceFiles(),
            function (SplFileInfo $file) {
                return $file->getExtension() === 'php';
            }
        );
    }

    /**
     * @return SplFileInfo[]
     */
    private function getDevelopmentSourceFiles() : array
    {
        return F\reduce_left(
            $this->getBuildInputPaths(),
            function (SplFileInfo $file, $i, $all, array $current) {
                if ($file->isFile()) {
                    return array_merge($current, [$file]);
                }

                return array_merge($current, $this->getPathFiles($this->directory, $file, $this->getRequiredFiles()));
            },
            []
        );
    }

    /**
     * @param \SplFileInfo $root
     * @param SplFileInfo $file
     * @param SplFileInfo[] $exclude
     * @return SplFileInfo[]
     */
    private function getPathFiles(\SplFileInfo $root, SplFileInfo $file, array $exclude) : array
    {
        $finder = Finder::create()
            ->in($file->getPathname())
            ->files();
        foreach ($exclude as $excludeFile) {
            if ($excludeFile->getRelativePath() === $file->getRelativePathname()) {
                $finder = $finder->notName($excludeFile->getBasename());
            }
        }

        return F\map(
            array_values(iterator_to_array($finder)),
            function (SplFileInfo $file) use ($root) {
                return FileUtils::createRelativeFileFromExisting($root, $file);
            }
        );
    }

    /**
     * @return SplFileInfo[]
     */
    public function getBuildInputPaths() : array
    {
        return F\unique(
            F\flat_map(
                $this->getBuildInstructions(),
                function (BuildInstruction $instruction) {
                    return $instruction->getInputPaths();
                }
            ),
            null,
            false
        );
    }

    /**
     * @return array
     */
    public function getStates() : array
    {
        return $this->getFileVariableValue($this->getStatesFileName(), 'machinestates')
            ->getOrThrow(new \RuntimeException("Couldn't find states"));
    }

    /**
     * @param string $fileName
     * @param string|callable $predicate
     * @return Option
     */
    public function getFileVariableValue(string $fileName, $predicate) : Option
    {
        return Utils::getVariableValueFromFile($this->getProjectFile($fileName), $predicate);
    }

    /**
     * @param string $relativePath
     * @return SplFileInfo
     */
    protected function getProjectFile($relativePath) : SplFileInfo
    {
        return FileUtils::createRelativeFileFromSubPath($this->directory, $relativePath);
    }

    /**
     * @param \SplFileInfo $file
     * @return SplFileInfo
     */
    public function absoluteToProjectRelativeFile(\SplFileInfo $file) : SplFileInfo
    {
        return FileUtils::createRelativeFileFromExisting($this->directory, $file);
    }

    /**
     * @param \SplFileInfo $file
     * @return SplFileInfo
     */
    public function absoluteToDistRelativeFile(\SplFileInfo $file) : SplFileInfo
    {
        return FileUtils::createRelativeFileFromExisting($this->getDistDirectory(), $file);
    }

    /**
     * @return array
     */
    public function getGameInfos() : array
    {
        return Utils::getVariableValueFromFile(
            $this->getProjectFile($this->getGameinfosProjectFileRelativePathname()),
            'gameinfos'
        )->get();
    }

    /**
     * @return \Table
     */
    public function createGameTableInstance() : \Table
    {
        return $this->createInstanceFromClassInFile($this->getGameProjectFileRelativePathname(), 'Table');
    }

    /**
     * @return \APP_GameAction
     */
    public function createActionInstance() : \APP_GameAction
    {
        return $this->createInstanceFromClassInFile($this->getActionProjectFileRelativePathname(), 'APP_GameAction');
    }

    /**
     * @param string $relativePathname
     * @param string $class
     * @return mixed
     */
    private function createInstanceFromClassInFile(string $relativePathname, string $class)
    {
        $gameFilepath = $this->getProjectFile($relativePathname)->getPathname();
        require_once($gameFilepath);
        $tableClasses = F\filter(
            F\map(
                array_keys(AnnotationsParser::parsePhp(file_get_contents($gameFilepath))),
                function ($className) {
                    return new \ReflectionClass($className);
                }
            ),
            function (\ReflectionClass $refClass) use ($class) {
                return $refClass->getParentClass()->getName() === $class;
            }
        );
        $numTableClasses = count($tableClasses);
        if ($numTableClasses !== 1) {
            throw new \RuntimeException(
                "Expected exactly one Table classes in game file {$gameFilepath}, found exactly {$numTableClasses}"
            );
        }
        return $tableClasses[0]->newInstance();
    }
}
