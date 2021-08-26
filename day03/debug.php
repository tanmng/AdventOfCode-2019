<?php

require_once('library.php');
$test_1 = new Segment('R75', new Point(0, 0));
print($test_1."\n");
$test_2 = new Segment('D30', $test_1->end);
print($test_2."\n");
$test_3 = new Segment('U33', new Point(8, -10));
print($test_3."\n");

print_r($test_3->CrossWith($test_1));
