<?php
require_once __DIR__.'/vendor/autoload.php';

use Example\Order\Process\Postpayment;
use Example\Order\Process\Prepayment;
use Metabor\NamedCollection;
use Metabor\Statemachine\Graph\GraphBuilder;
use Fhaculty\Graph\Graph;
use Graphp\GraphViz\GraphViz;

try {
    $processes = new NamedCollection();
    $processes->add(new Prepayment());
    $processes->add(new Postpayment());

    if (isset($_GET['process'])) {
        $processName = strtolower($_GET['process']);
        if ($processes->has($processName)) {
            $process = $processes->get($processName);
        }
    }
    if (!isset($process)) {
        $processNames = $processes->getNames();
        $processName = reset($processNames);
        $process = $processes->get($processName);
    }

    $graph = new Graph();
    $builder = new GraphBuilder($graph);
    $builder->addStateCollection($process);

    $viz = new GraphViz();
    $viz->setFormat('svg');
    echo file_get_contents($viz->createImageFile($graph));
} catch (Exception $e) {
    echo $e->getMessage();
}
