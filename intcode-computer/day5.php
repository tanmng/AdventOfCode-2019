<?php

/*
 * day5.php
 *
 * The intcode computer used in day 5, extends from the computer used in day 2
 */
require_once('../intcode-computer/day2.php');

/*
 * Help parse and query data about an instruction
 */
class InstructionDay5 {
    private $__raw = null;
    private $__opscode_value = null;
    private $__parameters_count = null;
    private $__parameters_mode = [];

    // Mode of parameters
    public const PARAMETER_MODE_POSITION = 0;
    public const PARAMETER_MODE_IMMEDIATE = 1;

    public function __construct(int $value) {
        // Store the raw value of the instruction
        $this->__raw = $value;

        // Calculate the opscode value
        $this->__opscode_value = $value % 100;

        // Store how many arguments we will need for the instruction
        switch ($this->__opscode_value) {
        case IntcodeComputerDay5::OPSCODE_INPUT:
            $this->__parameters_count = 1;
            // A very quick and easy way to calculate the mode
            $this->__parameters_mode = [
                self::PARAMETER_MODE_IMMEDIATE, // The parameter for this instruction should be parsed immediately as an address
            ];
            break;
        case IntcodeComputerDay5::OPSCODE_OUTPUT:
            $this->__parameters_count = 1;
            // A very quick and easy way to calculate the mode
            $this->__parameters_mode = [
                intval($value / 100) % 2,
            ];
            break;
        case IntcodeComputerDay5::OPSCODE_ADD:
        case IntcodeComputerDay5::OPSCODE_MULTIPLY:
            $this->__parameters_count = 3;
            $this->__parameters_mode = [
                intval($value / 100) % 2, // 1st parameter
                intval($value / 1000) % 2, // 2nd parameter
                self::PARAMETER_MODE_IMMEDIATE,  // Since we write to, we need this as a literal, telling us the address
            ];
            break;
        case IntcodeComputerDay5::OPSCODE_HALT:
            $this->__parameters_mode = [];
            $this->__parameters_count = 0;
            break;
        case IntcodeComputerDay5::OPSCODE_JUMP_IF_TRUE:
        case IntcodeComputerDay5::OPSCODE_JUMP_IF_FALSE:
            $this->__parameters_count = 2;
            $this->__parameters_mode = [
                intval($value / 100) % 2, // 1st parameter
                intval($value / 1000) % 2, // 2nd parameter
            ];
            break;
        case IntcodeComputerDay5::LESS_THAN:
        case IntcodeComputerDay5::EQUALS:
            $this->__parameters_count = 3;
            $this->__parameters_mode = [
                intval($value / 100) % 2, // 1st parameter
                intval($value / 1000) % 2, // 2nd parameter
                self::PARAMETER_MODE_IMMEDIATE,  // Since we write to, we need this as a literal, telling us the address
                /* intval($value / 10000) % 2, // 3rd parameter */
            ];
            break;
        default:
            throw new Exception('Unable to parse the instruction '.$value.', unrecognized opscode '.$this->__opscode_value);
            return;
        }
    }

    public function GetOpsCodeValue(): int {
        return $this->__opscode_value;
    }

    /*
     * Return the mode of the given parameter
     */
    public function GetParameterMode(int $position): int {
        if ($position > count($this->__parameters_mode)) {
            throw new Exception('You are trying to get the mode parameter out of range, this ops code '.$this->__opscode_value.' can have only '.$this->__parameters_count.' parameters');
        }

        return $this->__parameters_mode[$position - 1];
    }

    /*
     * Return a string representation of the parameter mode for given position
     */
    public function GetParameterModeString(int $position): string {
        return [
            self::PARAMETER_MODE_POSITION => 'p',
            self::PARAMETER_MODE_IMMEDIATE => 'i',
        ][$this->GetParameterMode($position)];
    }

    /*
     * Return how many parameters this instruction will need
     */
    public function GetParameterCount(): int {
        return $this->__parameters_count;
    }

    /*
     * A special parameter
     */
    public function IsHalt(): bool {
        return $this->__opscode_value == IntcodeComputerDay5::OPSCODE_HALT;
    }

    public function __tostring(): string {
        return 'instruction '.$this->__raw;
    }
}

class IntcodeComputerDay5 extends IntcodeComputerDay2 {
    // New instructions, the old one still stands
    public const OPSCODE_INPUT = 3;
    public const OPSCODE_OUTPUT = 4;
    public const OPSCODE_JUMP_IF_TRUE = 5;
    public const OPSCODE_JUMP_IF_FALSE = 6;
    public const LESS_THAN = 7;
    public const EQUALS = 8;

    // Store the inputs we need to use for OPSCODE_INPUT
    private $__inputs = [];

    // Easier to know what the current instruction is
    private $__current_instruction = null;

    /*
     * Allow us to pre-define inputs since we don't want the computer to be
     * interractive - yet
     */
    public function AddInputs(array $new_inputs) {
        $this->__inputs += $new_inputs;
    }

    /*
     * Run a step (ie. execute the opcode under program counter), then increase
     * the program counter properly and return if we should continue or not
     *
     * Overriding the implementation of day 2
     */
    public function Step(bool $debug = false): bool {
        // Make sure we didn't break the computer
        $this->__cycle_count += 1;
        if ($this->__cycle_count >= self::MAX_CYCLES_COUNT) {
            throw new Exception('Computer ran to maximum number of cycles - '.self::MAX_CYCLES_COUNT);
        }

        $this->__current_instruction = new InstructionDay5($this->__memory[$this->__program_pointer]);

        if ($debug) {
            $this->PrintDebug();
        }

        if ($this->IsHalted()) {
            return false;
        }

        $params = $this->GetAllParameters();

        switch ($this->__current_instruction->GetOpsCodeValue()) {
        case self::OPSCODE_HALT:
            // Another layer of safety
            $this->__state = self::STATE_HALTED;
            return false;
            break;
        case self::OPSCODE_ADD:
            $this->__state = self::STATE_RUNNING;

            // Set the result cell
            $this->SetMemory($params[2], [$params[0] + $params[1]]);
            break;
        case self::OPSCODE_MULTIPLY:

            // Set the result cell
            $this->SetMemory($params[2], [$params[0] * $params[1]]);
            break;
        case self::OPSCODE_INPUT:
            // Handle input

            // Store the value from input into the specified one
            $input_value = array_shift($this->__inputs);
            $this->__memory[$params[0]] = $input_value;
            break;
        case self::OPSCODE_OUTPUT:
            // Output a value
            print('Output: '.$params[0]."\n");
            break;
        case self::OPSCODE_JUMP_IF_TRUE:
            // Jump if the first param is non-zero
            if ($params[0] != 0) {
                $this->__program_pointer = $params[1];
                // We have to return here otherwise the program pointer will be
                // modified again
                return true;
            }
            // Breaking here will get us go through the instruction
            break;
        case self::OPSCODE_JUMP_IF_FALSE:
            // Jump if the first param is non-zero
            if ($params[0] == 0) {
                $this->__program_pointer = $params[1];
                // We have to return here otherwise the program pointer will be
                // modified again
                return true;
            }
            // Breaking here will get us go through the instruction
            break;
        case self::LESS_THAN:
            // Set 1 into the address given by 3rd parameter if first one is
            // less than 2nd one
            $this->SetMemory($params[2], [ $params[0] < $params[1]? 1 : 0 ]);
            break;
        case self::EQUALS:
            // Set 1 into the address given by 3rd parameter if first one equals
            // to 2nd one
            $this->SetMemory($params[2], [ $params[0] == $params[1]? 1 : 0 ]);
            break;
        default:
            throw new Exception('Unrecorgnized instruction');
            break;
        }

        // Move the program pointer
        $this->__program_pointer += $this->__current_instruction->GetParameterCount() + 1;

        return true;
    }

    /*
     * Get the set of parameters for our instruction
     */
    private function GetAllParameters(): array {
        $param_count = $this->__current_instruction->GetParameterCount();
        $return_values = [];
        foreach (range(0, $param_count - 1) as $i) {
            $return_values[] = $this->GetParameterValue($this->__current_instruction->GetParameterMode($i + 1), $this->__memory[$this->__program_pointer + $i + 1]);
        }

        return $return_values;
    }

    /*
     * Return either the value for parameter (either via positional parameter)
     * or immediate value
     */
    private function GetParameterValue(int $mode, int $value): int {
        switch ($mode) {
        case InstructionDay5::PARAMETER_MODE_IMMEDIATE:
            return $value;
            break;
        case InstructionDay5::PARAMETER_MODE_POSITION:
            return $this->GetMemory($value, 1)[0];
            break;
        default:
            throw new Exception('Invalid parameter mode '.$mode);
            break;
        }
    }

    private function PrintDebug() {
        // Print the damn memory before running
        print(implode('', [
            'Cycle ',
            $this->__cycle_count,
            '; ',
            'Program Pointer ',
            $this->__program_pointer,
            '; ',
            'Instruction ',
            $this->__current_instruction,
            ' which requires ',
            $this->__current_instruction->GetParameterCount(),
            ' parameters',
        ]));
        // Print the mode and values of the parameters if it is not halting
        $params = $this->GetAllParameters();
        foreach ($params as $index => $param_val) {
            if ($this->__current_instruction->GetParameterMode($index + 1) == InstructionDay5::PARAMETER_MODE_POSITION) {
                print(' '.$this->__current_instruction->GetParameterModeString($index + 1).$this->__memory[$this->__program_pointer + $index + 1].'('.$param_val.')');
            } else {
                print(' '.$this->__current_instruction->GetParameterModeString($index + 1).$param_val);
            }
        }
        print("\n");
        // Print the memory
        /* foreach ($this->__memory as $index => $value) { */
        /*     print(implode('', [ */
        /*         str_pad($index, 3, '     ', STR_PAD_LEFT), */
        /*         '. ', */
        /*         str_pad($value, 4, '     ', STR_PAD_LEFT), */
        /*         "\n", */
        /*     ])); */
        /* } */
        print('Memory '.implode(self::MEMORY_DELIMITER.' ', $this->__memory)."\n");
        if (count($this->__inputs)) {
            // There are inputs to use
            print('Inputs '.implode(self::MEMORY_DELIMITER.' ', $this->__inputs)."\n");
        }
    }
}
