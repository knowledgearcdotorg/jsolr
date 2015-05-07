<?php
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\content\LargeFileContent;

JLoader::register('JSolrIndexFilesystemExtractor', dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))))).'/libraries/jsolr/index/filesystem/extractor.php');

JLoader::register('JSolrIndexFilesystemTikaServer', dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))))).'/libraries/jsolr/index/filesystem/tikaserver.php');

class JSolrIndexFileSystemTikaServerTest extends \PHPUnit_Framework_TestCase
{
    public function testSetUrl()
    {
        $server = new JSolrIndexFilesystemTikaServer("dummy.file");
        $server->setAppPath("http://localhost:9998/tika");

        $this->assertEquals($server->getAppPath(), "http://localhost:9998/tika/");
    }

    public function testContentType()
    {
        $root = vfsStream::setup();

        /*$pdf = vfsStream::newFile('document.pdf')
            ->withContent(LargeFileContent::withKilobytes(50))
            ->at($root);*/

        $server = new JSolrIndexFilesystemTikaServer("http://localhost/owncloud/core/skeleton/ownCloudUserManual.pdf");
        $server->setAppPath("http://localhost:9998");

        $this->assertEquals($server->getContentType(), 'application/pdf');
    }

    public function testMetadata()
    {
        $root = vfsStream::setup();

        /*$pdf = vfsStream::newFile('document.pdf')
            ->withContent(LargeFileContent::withKilobytes(50))
            ->at($root);*/

        $server = new JSolrIndexFilesystemTikaServer("http://localhost/owncloud/core/skeleton/ownCloudUserManual.pdf");
        $server->setAppPath("http://localhost:9998");

        $metadata = $server->getMetadata();

        $this->assertEquals($metadata->get("Content-Type"), 'application/pdf');
    }


    public function testContent()
    {
        $root = vfsStream::setup();

        /*$pdf = vfsStream::newFile('document.pdf')
            ->withContent(LargeFileContent::withKilobytes(50))
            ->at($root);*/

        $server = new JSolrIndexFilesystemTikaServer("http://localhost/owncloud/core/skeleton/ownCloudUserManual.pdf");
        $server->setAppPath("http://localhost:9998");

        $this->assertEquals(substr($server->getContent(), 0, 21), "\nownCloud User Manual");
    }
}
