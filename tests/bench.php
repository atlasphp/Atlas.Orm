<?php
$dir = __DIR__;
while ($dir !== '') {
    $dir = dirname($dir);
    $file = "{$dir}/vendor/autoload.php";
    if (file_exists($file)) {
        require $file;
        echo "Autoloader at $file" . PHP_EOL;
        break;
    }
}

if (! file_exists($file)) {
    "No autoloader found." . PHP_EOL;
    exit(1);
}

use Atlas\Orm\AtlasContainer;
use Atlas\Orm\SqliteFixture;
use Atlas\Orm\DataSource\Author\AuthorMapper;
use Atlas\Orm\DataSource\Reply\ReplyMapper;
use Atlas\Orm\DataSource\Reply\ReplyRecord;
use Atlas\Orm\DataSource\Reply\ReplyRecordSet;
use Atlas\Orm\DataSource\Summary\SummaryMapper;
use Atlas\Orm\DataSource\Summary\SummaryTable;
use Atlas\Orm\DataSource\Tag\TagMapper;
use Atlas\Orm\DataSource\Tagging\TaggingMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapper;
use Atlas\Orm\DataSource\Thread\ThreadMapperEvents;
use Atlas\Orm\DataSource\Thread\ThreadRecord;
use Atlas\Orm\DataSource\Thread\ThreadRecordSet;
use Atlas\Orm\Mapper\Record;
use Atlas\Orm\Mapper\RecordSet;
use Aura\Sql\ExtendedPdo;

function bench($label, $callable)
{
    $k = 100000;
    $before = microtime(true);
    for ($i = 0; $i < $k; $i ++) {
        $callable();
    }
    $after = microtime(true);
    echo ($after - $before) . " : {$label}" . PHP_EOL;
}

$atlasContainer = new AtlasContainer('sqlite::memory:');
$atlasContainer->setMappers([
    AuthorMapper::CLASS,
    ReplyMapper::CLASS,
    SummaryMapper::CLASS,
    TagMapper::CLASS,
    ThreadMapper::CLASS,
    TaggingMapper::CLASS,
]);

$connection = $atlasContainer->getConnectionLocator()->getDefault();
$fixture = new SqliteFixture($connection);
$fixture->exec();

$atlas = $atlasContainer->getAtlas();
$threadMapper = $atlas->mapper(ThreadMapper::CLASS);
$threadTable = $threadMapper->getTable();

bench('ThreadTable::newRow()', function () use ($threadTable) {
    $threadTable->newRow();
});

bench('ThreadMapper::newRecord()', function () use ($threadMapper) {
    $threadMapper->newRecord();
});

bench('Atlas::newRecord()', function () use ($atlas) {
    $atlas->newRecord(ThreadMapper::CLASS);
});
