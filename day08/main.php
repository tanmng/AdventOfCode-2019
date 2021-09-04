<?php

// Get the data from stdin
$lines = [];
while ($f = fgets(STDIN)) {
    $line = trim($f);
    $lines[] = $line;
}

// Part 1
define('IMAGE_WIDTH', 25);
define('IMAGE_HEIGHT', 6);

define('BLACK_PIXEL', 0);
define('WHITE_PIXEL', 1);
define('TRANSPARENT_PIXEL', 2);

$layer_char_count = IMAGE_WIDTH * IMAGE_HEIGHT;
$all_chars = str_split($lines[0]);

// Break the image into layers
$layers = array_chunk($all_chars, $layer_char_count);
$comparing_index = BLACK_PIXEL;   // The digit that we wish to use for comparing

$layer_character_frequency = [];
$min_number_of_zeros = IMAGE_WIDTH * IMAGE_HEIGHT + 1; // This way we know for sure that this is more than any layers
$min_layer_index = null;

foreach ($layers as $layer_index => $layer) {
    $current_layer_frequency = array_count_values($layer);
    $layer_character_frequency[] = $current_layer_frequency;

    if ($min_number_of_zeros > $current_layer_frequency[$comparing_index]) {
        $min_number_of_zeros = $current_layer_frequency[$comparing_index];
        $min_layer_index = $layer_index;
    }
}

print('Part 1 - layer '.$min_layer_index.' with '.$min_number_of_zeros.' zeroes, answer '.($layer_character_frequency[$min_layer_index][1] * $layer_character_frequency[$min_layer_index][2])."\n");

// Part 2 - construct the image
$final_image = [];

foreach (range(0, $layer_char_count - 1) as $pixel_index) {
    // Find the first digit for this that is not transparent
    // What to do if all of them are transparent?
    $pixel = TRANSPARENT_PIXEL;

    foreach ($layers as $layer_index => $layer) {
        if ($layer[$pixel_index] != $pixel) {
            // Reach a pixel that is not transparent
            $pixel = $layer[$pixel_index];
            break;
        }
    }

    // Better representation with colour in our shell
    $final_image[] = $pixel == WHITE_PIXEL? '*' : ' ';
}

// Print the image now
$final_image_lines = array_chunk($final_image, IMAGE_WIDTH);

foreach ($final_image_lines as $line_index => $line) {
    print(str_pad($line_index, 3, '   ', STR_PAD_LEFT).': '.implode('', $line)."\n");
}
