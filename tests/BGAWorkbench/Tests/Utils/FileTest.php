<?php

namespace BGAWorkbench\Tests;

use BGAWorkbench\Utils\FileUtils;
use PHPUnit\Framework\TestCase;

class FileUtilsTest extends TestCase
{
    public function testJoinPathNormal()
    {
        $result = FileUtils::joinPath('hello', 'good', 'world');

        assertThat($result, equalTo('hello' . DIRECTORY_SEPARATOR . 'good' . DIRECTORY_SEPARATOR . 'world'));
    }

    public function testJoinPathSingle()
    {
        $result = FileUtils::joinPath('hello');

        assertThat($result, equalTo('hello'));
    }

    public function testJoinPathEmpty()
    {
        $this->expectException('InvalidArgumentException');
        FileUtils::joinPath();
    }

    public function testJoinPathWithFileInfo()
    {
        $result = FileUtils::joinPath(
            new \SplFileInfo(DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'vagrant'),
            'world'
        );

        assertThat(
            $result,
            equalTo(DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . 'vagrant' . DIRECTORY_SEPARATOR . 'world')
        );
    }
}
