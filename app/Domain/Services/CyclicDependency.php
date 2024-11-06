<?php

namespace App\Domain\Services;

use App\Domain\Services\Visited;

class CyclicDependency
{
    public function __construct(
        private Visited $visited,
        private Stack $stack,
    ) {}

    public function detect(array $classes): array
    {
        $cycles = [];

        foreach ($classes as $givenClass => $_) {

            if ($this->visited->unknown($givenClass)) {
                $this->deepDive($givenClass, $classes, $cycles);
            }
        }

        return $cycles;
    }

    private function deepDive(string $class, array $classes, array &$cycles): void
    {
        /**
         * Duplicate class can exist in the array.
         * If the class is already visited, we can switch to another class.
         */
        if ($this->visited->isMarked($class)) {
            return;
        }

        /**
         * This stack is used to detect cycles.
         * Progressively, we push dependencies in the stack.
         * If the class is already in the stack, we have a cycle.
         */
        if ($this->stack->contains($class)) {

            $cycle = $this->stack->extractCycle($class);

            $cycles[] = $cycle;

            /**
             * Stop the recursion, we can switch to another class.
             */
            return;
        } 

        $this->stack->push($class);

        $this->visited->mark($class, false);

        foreach ($classes[$class]->getDependencies() as $dependency) {
            if (isset($classes[$dependency])) {
                $this->deepDive($dependency, $classes, $cycles);
            }
        }

        /**
         * Remove the class from the stack.
         */
        $this->stack->pop();

        /**
         * Mark the class as fully explored to avoid infinite loops.
         */
        $this->visited->mark($class, true);
    }
}
