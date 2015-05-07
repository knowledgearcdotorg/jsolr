<?php
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\content\LargeFileContent;

JLoader::register('JSolrIndexFilesystemExtractor', dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))))).'/libraries/jsolr/index/filesystem/extractor.php');

JLoader::register('JSolrIndexFilesystemTikaApp', dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))))).'/libraries/jsolr/index/filesystem/tikaapp.php');

class JSolrIndexFileSystemTikaAppTest extends \PHPUnit_Framework_TestCase
{
    public function testContentType()
    {
        $root = vfsStream::setup();

        /*$pdf = vfsStream::newFile('document.pdf')
            ->withContent(LargeFileContent::withKilobytes(50))
            ->at($root);*/

        $app = new JSolrIndexFilesystemTikaApp("http://localhost/owncloud/core/skeleton/ownCloudUserManual.pdf");
        $app->setAppPath("/opt/apache/tika/tika.jar");

        $this->assertEquals('application/pdf', $app->getContentType());
    }

    public function testMetadata()
    {
        $root = vfsStream::setup();

        /*$pdf = vfsStream::newFile('document.pdf')
            ->withContent(LargeFileContent::withKilobytes(50))
            ->at($root);*/

        $app = new JSolrIndexFilesystemTikaApp("http://localhost/owncloud/core/skeleton/ownCloudUserManual.pdf");
        $app->setAppPath("/opt/apache/tika/tika.jar");

        $metadata = $app->getMetadata();

        $this->assertEquals('application/pdf', $metadata->get("Content-Type"));
    }


    public function testContent()
    {
        $root = vfsStream::setup();

        /*$pdf = vfsStream::newFile('document.pdf')
            ->withContent(LargeFileContent::withKilobytes(50))
            ->at($root);*/

        $app = new JSolrIndexFilesystemTikaApp("http://localhost/owncloud/core/skeleton/ownCloudUserManual.pdf");
        $app->setAppPath("/opt/apache/tika/tika.jar");

        $this->assertEquals("\nownCloud User Manual", substr($app->getContent(), 0, 21));
    }
}
