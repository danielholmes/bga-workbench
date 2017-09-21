<?php

namespace BGAWorkbench;

use phpseclib\Net\SFTP;
use Symfony\Component\Finder\SplFileInfo;
use Functional as F;

class ProductionDeployment
{
    /**
     * @var SFTP
     */
    private $sftp;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $directory;

    /**
     * @var SplFileInfo[]
     */
    private $remoteDirectories;

    /**
     * @var bool
     */
    private $isConnected;

    /**
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $directory
     */
    public function __construct(string $host, string $username, string $password, string $directory)
    {
        $this->sftp = new SFTP($host);

        $this->username = $username;
        $this->password = $password;
        $this->directory = $directory;
        $this->isConnected = false;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->isConnected;
    }

    public function connect()
    {
        if (!$this->sftp->login($this->username, $this->password)) {
            throw new \RuntimeException("Couldn't log in");
        }
        $this->isConnected = true;
    }

    /**
     * @param SplFileInfo[] $files
     * @param callable $callable
     * @return int
     */
    public function deployChangedFiles(array $files, $callable) : int
    {
        $remoteMTimes = $this->getMTimesByFilepath();
        $newerFiles = array_values(
            F\filter(
                $files,
                function (SplFileInfo $file) use ($remoteMTimes) {
                    return !isset($remoteMTimes[$file->getRelativePathname()]) ||
                        $remoteMTimes[$file->getRelativePathname()] < $file->getMTime();
                }
            )
        );
        return $this->deployFiles($newerFiles, $callable);
    }

    /**
     * @param SplFileInfo[] $files
     * @param callable $callable
     * @return int
     */
    public function deployFiles(array $files, $callable)
    {
        $total = count($files);
        F\each(
            $files,
            function (SplFileInfo $file, $i) use ($callable, $total) {
                $num = $i + 1;
                call_user_func($callable, $num, $total, $file);
                $this->deployFile($file);
            }
        );
        return $total;
    }

    /**
     * @return array
     */
    private function getRemoteDirectories() : array
    {
        if ($this->remoteDirectories === null) {
            $rawList = $this->sftp->rawlist($this->directory, true);
            $this->remoteDirectories = $this->rawListToDirectories($rawList);
        }

        return $this->remoteDirectories;
    }

    /**
     * @param SplFileInfo $file
     */
    public function remove(SplFileInfo $file)
    {
        $remoteName = $file->getRelativePathname();
        if (!$this->sftp->delete($remoteName)) {
            throw new \RuntimeException("Error deleting {$remoteName}");
        }
    }

    /**
     * @param SplFileInfo $file
     */
    private function deployFile(SplFileInfo $file)
    {
        $remoteName = $file->getRelativePathname();
        $remoteDirectories = $this->getRemoteDirectories();
        $remoteDirpath = dirname($remoteName);
        if ($remoteDirpath !== '.' && !in_array($remoteDirpath, $remoteDirectories, true)) {
            $fullRemoteDirpath = "{$this->directory}/{$remoteDirpath}";
            if (!$this->sftp->mkdir($fullRemoteDirpath, -1, true)) {
                throw new \RuntimeException("Error creating directory {$fullRemoteDirpath}");
            }
            $this->remoteDirectories = array_merge($this->remoteDirectories, $this->pathToAllSubPaths($remoteDirpath));
        }

        $fullRemotePathname = "{$this->directory}/{$remoteName}";
        if (!$this->sftp->put($fullRemotePathname, $file->getPathname(), SFTP::SOURCE_LOCAL_FILE)) {
            throw new \RuntimeException("Error transferring {$file->getPathname()} to {$remoteName}");
        }
    }

    /**
     * @param string $remoteDirpath
     * @return string[]
     */
    private function pathToAllSubPaths($remoteDirpath) : array
    {
        $parts = explode('/', $remoteDirpath);
        return F\map(
            range(1, count($parts)),
            function ($i) use ($parts) {
                return join('/', array_slice($parts, 0, $i));
            }
        );
    }

    /**
     * @return array
     */
    private function getMTimesByFilepath() : array
    {
        $rawList = $this->sftp->rawlist($this->directory, true);
        $this->remoteDirectories = $this->rawListToDirectories($rawList);
        return $this->rawListToMTimesByFilepath($rawList);
    }

    /**
     * @param array $rawRemoteList
     * @return array
     */
    private function rawListToMTimesByFilepath(array $rawRemoteList) : array
    {
        $map = [];
        foreach ($rawRemoteList as $key => $value) {
            if ($key === '.' || $key === '..') {
                continue;
            }

            if (is_array($value)) {
                $subMTimes = $this->rawListToMTimesByFilepath($value);
                foreach ($subMTimes as $subName => $subMTime) {
                    $map[$key . '/' . $subName] = $subMTime;
                }
                continue;
            }

            $map[$key] = $value->mtime;
        }
        return $map;
    }

    /**
     * @param array $rawRemoteList
     * @return array
     */
    private function rawListToDirectories(array $rawRemoteList) : array
    {
        $directories = [];
        foreach ($rawRemoteList as $key => $value) {
            if ($key === '.' || $key === '..') {
                continue;
            }

            if (!is_array($value)) {
                continue;
            }

            $subDirectories = $this->rawListToDirectories($value);
            $directories = array_merge(
                $directories,
                [$key],
                F\map(
                    $subDirectories,
                    function ($subDir) use ($key) {
                        return $key . '/' . $subDir;
                    }
                )
            );
        }
        return $directories;
    }
}
