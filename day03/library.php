<?php
/*
 * library.php
 *
 * Library of things used in this day
 */


/*
 * Representation of a point in the grid - it's coordinate
 */
class Point {
    public $x, $y;

    public function __construct(int $_x, $_y) {
        $this->x = $_x;
        $this->y = $_y;
    }

    public function ManhattanDistance(Point $other): int {
        return abs($this->x - $other->x) + abs($this->y - $other->y);
    }

    public function __toString(): string {
        return 'Point ('.$this->x.', '.$this->y.')';
    }
}


/*
 * Representation of a segment of the wire
 */
class Segment {
    public $horizontal = false;
    public $vertical = false;

    // When the line follows the axis we can store the coordinate which doesn't
    // change
    public $x, $y;

    private $raw;

    private $lower_bound, $upper_bound; // Bounds of the values of the axis that does change

    public $length;

    public $begin, $end;

    /*
     * Parse the representation 
     */
    public function __construct(string $input_string, Point $begin_point) {
        $parts = [];
        $match_result = preg_match('/^([UDLR])([0-9]+)$/', $input_string, $parts);
        // Validate the input string
        if ($match_result == false) {
            // Incorrect template
            throw new Exception("Incorrect input string '".$input_string."'");
        }

        $this->raw = $input_string;

        // Parse this now
        $direction = $parts[1];
        $length = intval($parts[2]);

        $this->length = $length;

        // Form the 2 points of this segment
        $this->begin = $begin_point;

        // Caldulate 2nd point
        $this->begin = clone $begin_point;

        // Avoid directly referencing
        $this->end = clone $begin_point;

        switch ($direction) {
        case 'U':
            $this->vertical = true;
            $this->end->y = $this->begin->y + $length;
            $this->x = $this->begin->x;

            $this->upper_bound = $this->end->y;
            $this->lower_bound = $this->begin->y;
            break;
        case 'D':
            $this->vertical = true;
            $this->end->y = $this->begin->y - $length;
            $this->x = $this->begin->x;

            $this->upper_bound = $this->begin->y;
            $this->lower_bound = $this->end->y;
            break;
        case 'L':
            $this->horizontal = true;
            $this->end->x = $this->begin->x - $length;
            $this->y = $this->begin->y;

            $this->upper_bound = $this->begin->x;
            $this->lower_bound = $this->end->x;
            break;
        case 'R':
            $this->horizontal = true;
            $this->end->x = $this->begin->x + $length;
            $this->y = $this->begin->y;

            $this->upper_bound = $this->end->x;
            $this->lower_bound = $this->begin->x;
            break;
        }
    }

    /*
     * Help represent this
     */
    public function __toString(): string {
        $val = 'Segment from '.$this->begin.' to '.$this->end;
        if ($this->horizontal) {
            $val = 'HORIZONTAL '.$val.': '.$this->y;
        }
        if ($this->vertical) {
            $val = 'VERTICAL '.$val.': '.$this->x;
        }

        $val .= ' ('.$this->raw.')';

        return $val;
    }

    /*
     * Check if the current segment cross over the other one, if so, return the
     * point of crossing
     */
    public function CrossWith(Segment $other) {
        if (!($this->vertical ^ $other->vertical)) {
            // Both segments are vertical or both of them are horizontal, in
            // either case this cannot cross
            return false;
        }

        // We need to know which one is vertical and which one is horizontal
        if ($this->vertical) {
            $vertical_segment = clone $this;
            $horizontal_segment = clone $other;
        } else {
            $vertical_segment = clone $other;
            $horizontal_segment = clone $this;
        }

        if ($vertical_segment->lower_bound < $horizontal_segment->y && $vertical_segment->upper_bound > $horizontal_segment->y &&
            $horizontal_segment->lower_bound < $vertical_segment->x && $horizontal_segment->upper_bound > $vertical_segment->x) {
            // Cross
            // Not that from their document it seems like = values are unlikely
            // Return the point, along with distance from beginning of self as
            // well as beginning of other here
            $cross_point = new Point($vertical_segment->x, $horizontal_segment->y);
            return [
                $cross_point,
                $cross_point->ManhattanDistance($this->begin),
                $cross_point->ManhattanDistance($other->begin),
            ];
        }

        return false;
    }

}
