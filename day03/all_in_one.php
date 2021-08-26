<?php

require_once('library.php');

// Get the data from stdin
$lines = [];
while ($f = fgets(STDIN)) {
    $line = trim($f);
    print('Line: '.$line."\n");
    $lines[] = $line;
}

$central_port = new Point(0, 0);

// Parse the first wire
$first_wire_raw = $lines[0];

$first_wire_parts = explode(',', $first_wire_raw);

$first_wire = [];
$first_wire_segment_distance = []; // The distance from the beginning of each segment to central port
$distance_so_far = 0;   // The distance we have traverse so far following segments of this wire
$first_wire_vertical = [];
$first_wire_vertical_distance = [];
$first_wire_horizontal = [];
$first_wire_horizontal_distance = [];
foreach ($first_wire_parts as $index => $raw) {
    if ($index == 0) {
        // First segment ever
        $segment = new Segment($raw, $central_port);
    } else {
        // Subsequent ones
        $segment = new Segment($raw, $first_wire[count($first_wire) - 1]->end);
    }

    // Save this segment
    $first_wire[] = $segment;

    // Save the distance from this segment
    $first_wire_segment_distance[] = $distance_so_far;

    if ($segment->vertical) {
        $first_wire_vertical[] = $segment;
        $first_wire_vertical_distance[] = $distance_so_far;
    } else {
        $first_wire_horizontal[] = $segment;
        $first_wire_horizontal_distance[] = $distance_so_far;
    }

    // Increase distance_so_far
    $distance_so_far += $segment->length;
}

$all_crosses = [];
$all_manhattan_distances = [];
$all_traverse_distances = [];

// Parse the 2nd wire and find all the crosses with 1st one
$second_wire_parts = explode(',', $lines[1]);
$distance_so_far = 0;
$second_wire = [];
foreach ($second_wire_parts as $index => $raw) {
    if ($index == 0) {
        // First segment ever
        $segment = new Segment($raw, $central_port);
    } else {
        // Subsequent ones
        $segment = new Segment($raw, $second_wire[count($second_wire) - 1]->end);
    }

    $second_wire[] = $segment;

    if ($segment->vertical) {
        // Try to find if it crosses with any line 
        $other_set = $first_wire_horizontal;
        $other_set_distance = $first_wire_horizontal_distance;
    } else {
        $other_set = $first_wire_vertical;
        $other_set_distance = $first_wire_vertical_distance;
    }


    // Check to find if there is any cross
    foreach ($other_set as $index => $other) {
        /* print('Trying to find cross between '.$segment.' and '.$other."\n"); */
        $point_data = $segment->CrossWith($other);

        if ($point_data != false) {
            // We found a cross
            $point = $point_data[0];
            $all_crosses[] = $point;

            $all_manhattan_distances[] = $point->ManhattanDistance($central_port);

            // Record the traverse distance
            $traverse_distance = $distance_so_far + $point_data[1] + $point_data[2] + $other_set_distance[$index];

            $all_traverse_distances[] = $traverse_distance;
        }
    }

    // Increase distance so far
    $distance_so_far += $segment->length;
}


/* print('First wire:'."\n"); */
/* foreach ($first_wire as $e) { */
/*     print('  - '.$e."\n"); */
/* } */
/* print('Second wire:'."\n"); */
/* foreach ($second_wire as $e) { */
/*     print('  - '.$e."\n"); */
/* } */
print('All the crosses:'."\n");
foreach ($all_crosses as $e) {
    print('  - '.$e."\n");
}
print('Min Manhattan Distance: '.min($all_manhattan_distances)."\n");
print('Min Traverse Distance: '.min($all_traverse_distances)."\n");
