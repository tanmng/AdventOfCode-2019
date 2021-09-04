<?php

require_once('../intcode-computer/day5.php');

// Get the data from stdin
$lines = [];
while ($f = fgets(STDIN)) {
    $line = trim($f);
    print('Line: '.$line."\n");
    $lines[] = $line;
}

// Part 1
// Get the computer
$computer = new IntcodeComputerDay5($lines[0]);

// Prepare to input the number 1
$computer->AddInputs([1]);

// Run the whole computer
print('Running computer for part 1'."\n");
$computer->RunTillHalt(0, false);

// Part 2
// Re-create the computer since the memory are all messed up
$computer = new IntcodeComputerDay5($lines[0]);

// Prepare to input the number 5
$computer->AddInputs([5]);

// Run the whole computer
print('Running computer for part 2'."\n");
$computer->RunTillHalt(0, false);

