<?php

// Get the data from stdin
$lines = [];
while ($f = fgets(STDIN)) {
    $line = trim($f);
    print('Line: '.$line."\n");
    $lines[] = $line;
}

// Parse the data
$all_objects = [];  // List of all objects
$center_of = [];    // Mapping from an object to the other one which it gravitate around
$distance_to_com = []; // Mapping from object to its distance toward COM

foreach ($lines as $line) {
    $match_parts = null;
    preg_match('/^([A-Z0-9]+)\)([A-Z0-9]+)$/', $line, $match_parts);

    $center = $match_parts[1];
    $object = $match_parts[2];

    // Record this
    $center_of[$object] = $center;
    $all_objects[] = $object;

    if (0 == strcmp($center, 'COM')){
        // This object gravitage around the center of mass
        $distance_to_com[$object] = 1;
    }
}

// Now sum up all the distance, that will be the value we need
function find_distance(string $object): int{
    // Can't remember how to use the keyword use in here - lol
    global $distance_to_com, $center_of;

    if (array_key_exists($object, $distance_to_com)) {
        // We found this earlier
        return $distance_to_com[$object];
    } else {
        // Haven't foudn this earlier - calculate, then store
        $value = 1 + find_distance($center_of[$object]);
        $distance_to_com[$object] = $value;
        return $value;
    }
}

// Part 1
// Sum up the distance from all objects to COM, that's the answer of part 1
$part1 = 0;
foreach ($all_objects as $object) {
    $part1 += find_distance($object);
}

print('Part 1: '.$part1."\n");

// Part 2
// Just find the common ancestor in a tree
function path_to_com(string $object): array {
    global $center_of;

    $path = [];
    $current_object = $object;
    do {
        $current_object = $center_of[$current_object];
        $path[] = $current_object;
    } while (strcmp($current_object, 'COM'));
    return $path;
}

// Create the path from YOU into COM
$from_you_to_com = path_to_com('YOU');
/* print_r($from_you_to_com); */

$from_santa_to_com = path_to_com('SAN');
/* print_r($from_santa_to_com); */

# Find the common ancestor
foreach ($from_you_to_com as $index => $object) {
    $found = array_search($object, $from_santa_to_com, true);

    if (is_int($found)) {
        // We found it
        // Calculate how many jumps we will need
        $jump_count = $index + $found;
        print('Part 2, common object is '.$object.', requiring '.$jump_count." jumps\n");
        break;
    }
}
