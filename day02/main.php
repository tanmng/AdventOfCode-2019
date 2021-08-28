<?php

require_once('../intcode-computer/day2.php');

// Get the data from stdin
$lines = [];
while ($f = fgets(STDIN)) {
    $line = trim($f);
    print('Line: '.$line."\n");
    $lines[] = $line;
}

// Get the computer
$computer = new IntcodeComputerDay2($lines[0]);

// Modify the memory according to instruction
$computer->SetMemory(1, [12, 2]);

// Run the whole computer
$computer->RunTillHalt(0, false);

// Now that the computer has halted, let's print out the memory
$first_memory = $computer->GetMemory(0, 1)[0];
print('Part 1: '.$first_memory."\n");

// Part 2
$target_value = 19690720; // Apparently we need our computer to reach this

/*
 * Run an attempt with given inputs for address 1 and 2, while check if the
 * output is that of expected_output
 */
function attempt(string $computer_init_memory, int $input_1, int $input_2, int $expected_output): bool {
    $computer = new IntcodeComputerDay2($computer_init_memory);

    // Modify the memory with attempt values
    $computer->SetMemory(1, [$input_1, $input_2]);
    $computer->RunTillHalt();

    $first_memory = $computer->GetMemory(0, 1)[0];

    return $first_memory == $expected_output;
}

// Actually run part 2
$part_2_found = false;
foreach (range(0, 100) as $noun) {
    foreach (range(0, 100) as $verb) {
        // Attempt and check
        if (attempt($lines[0], $noun, $verb, $target_value)) {
            print('Part 2, found inputs to be noun='.$noun.' and verb='.$verb.' which means answer is '.(100 * $noun + $verb)."\n");
            $part_2_found = true;
            // No need to search anymore
            break 2;
        }
    }
}

if (!$part_2_found) {
    print('Could NOT find answer for part 2'."\n");
}
