<?php

/*
 * day2.php
 *
 * The intcode computer used in day 2, others will extend this
 */

/* use Exception; */

class IntcodeComputerDay2 {
    // Memory and program pointer
    private $__memory = [];
    private $__program_pointer = [];
    private $__state = null;

    // A small helper
    private $__memory_length = null;

    public const MEMORY_DELIMITER = ',';
    private const MAX_CYCLES_COUNT = 1000;

    // States of the computer
    public const STATE_INIT = 0;
    public const STATE_RUNNING = 1;
    public const STATE_HALTED = 2;

    // Some constants OPSCODE
    public const OPSCODE_HALT = 99;
    public const OPSCODE_ADDS = 1;
    public const OPSCODE_MULTIPLIES = 2;

    // A safety mechanism
    private $__cycle_count = 0;

    /*
     * Initialize the computer
     */
    public function __construct(string $input_program) {
        $this->__program_pointer = 0;
        $this->__state = self::STATE_INIT;

        // Parse the program
        $input_parts = explode(self::MEMORY_DELIMITER, $input_program);

        $this->__memory_length = 0;
        $this->__cycle_count = 0;
        foreach ($input_parts as $part) {
            $this->__memory[] = intval($part);
            $this->__memory_length += 1;
        }
    }

    /*
     * Some helper method
     *
     */

    /*
     * Write some data into the memory
     * No need to be safe right, since the length of an array is easily defined
     */
    public function SetMemory(int $begin_address, array $values) {
        $input_length = count($values);

        if ($begin_address + $input_length > $this->__memory_length) {
            // It will be a shame if we set outside of the computer
            // I don't think we should allow that
            throw new Exception('You are trying to force more data into memory than it can take');
        }

        // Now set
        foreach (range(0, $input_length - 1) as $i) {
            $this->__memory[$begin_address + $i] = intval($values[$i]);
        }
    }

    /*
     * Return the memory from given address
     */
    public function GetMemory(int $begin_address, int $input_length): array {
        if ($begin_address + $input_length > $this->__memory_length) {
            // It will be a shame if we set outside of the computer
            // I don't think we should allow that
            throw new Exception('You are trying to read data from outside the range of the computer');
        }

        $return_values = [];

        // Now set
        foreach (range(0, $input_length - 1) as $i) {
            $return_values[] = $this->__memory[$begin_address + $i];
        }

        return $return_values;
    }

    /*
     * Return whether the computer is halted
     */
    public function IsHalted(): bool {
        if ($this->__state == self::STATE_HALTED) {
            return true;
        }
        if ($this->__memory[$this->__program_pointer] == self::OPSCODE_HALT) {
            $this->__state = self::STATE_HALTED;
            return true;
        }

        return false;
    }

    /*
     * Run a step (ie. execute the opcode under program counter), then increase
     * the program counter properly and return if we should continue or not
     */
    public function Step(bool $debug = false): bool {
        // Make sure we didn't break the computer
        $this->__cycle_count += 1;
        if ($this->__cycle_count >= self::MAX_CYCLES_COUNT) {
            throw new Exception('Computer ran to maximum number of cycles - '.self::MAX_CYCLES_COUNT);
        }

        if ($debug) {
            $this->PrintDebug();
        }

        if ($this->IsHalted()) {
            return false;
        }
        $current_instruction = $this->__memory[$this->__program_pointer];

        switch ($current_instruction) {
        case self::OPSCODE_HALT:
            // Another layer of safety
            $this->__state = self::STATE_HALTED;
            return false;
            break;
        case self::OPSCODE_ADDS:
            $this->__state = self::STATE_RUNNING;
            // Perform the add operations
            $address_1 = $this->__memory[$this->__program_pointer + 1];
            $address_2 = $this->__memory[$this->__program_pointer + 2];
            $address_3 = $this->__memory[$this->__program_pointer + 3];

            // Perform the calculation
            $value_1 = $this->GetMemory($address_1, 1)[0];
            $value_2 = $this->GetMemory($address_2, 1)[0];

            // Set the result cell
            $this->SetMemory($address_3, [$value_1 + $value_2]);
            // Increase program counter
            $this->__program_pointer += 4;
            break;
        case self::OPSCODE_MULTIPLIES:
            $this->__state = self::STATE_RUNNING;
            // Perform the add operations
            $address_1 = $this->__memory[$this->__program_pointer + 1];
            $address_2 = $this->__memory[$this->__program_pointer + 2];
            $address_3 = $this->__memory[$this->__program_pointer + 3];

            // Perform the calculation
            $value_1 = $this->GetMemory($address_1, 1)[0];
            $value_2 = $this->GetMemory($address_2, 1)[0];

            // Set the result cell
            $this->SetMemory($address_3, [$value_1 * $value_2]);
            // Increase program counter
            $this->__program_pointer += 4;
            break;
        }

        return true;
    }

    /*
     * Run the computer till it halts
     */
    public function RunTillHalt(int $begin_address = 0, bool $debug = false) {
        do {
            // Run a single step
            $this->Step($debug);
        } while (!$this->IsHalted());
        if ($debug) {
            print('Halted'."\n");
            $this->PrintDebug();
        }
    }

    /*
     * Print the state
     */
    private function PrintDebug(): void {
        // Print the damn memory before running
        print('Cycle '.$this->__cycle_count.'; Program Pointer '.$this->__program_pointer.' with code '.$this->__memory[$this->__program_pointer].' ('.$this->CodeName($this->__memory[$this->__program_pointer]).')'." \n");
        print('Memory '.implode(self::MEMORY_DELIMITER.' ', $this->__memory)."\n");
    }

    /*
     * Translate the code value into a string representation
     */
    private function CodeName(int $code): string {
        switch ($code) {
            case self::OPSCODE_MULTIPLIES:
                return 'multiplies';
                break;
            case self::OPSCODE_ADDS:
                return 'adds';
                break;
            case self::OPSCODE_HALT:
                return 'halt';
                break;
            default:
                return 'unknown';
                break;

        }
    }
}
