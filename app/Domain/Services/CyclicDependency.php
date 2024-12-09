<?php

namespace App\Domain\Services;

use App\Domain\Services\Visited;
use App\Domain\Services\Cycle;
use App\Domain\Services\Stack;

class CyclicDependency
{
    public function __construct(
        private Visited $visited,
        private Stack $stack,
        private Cycle $cycle,
    ) {}

    public function detect(array $classes): Cycle
    {
        foreach ($classes as $givenClass => $_) {

            if ($this->visited->unknown($givenClass)) {
                $this->deepDive($givenClass, $classes);
            }
        }

        return $this->cycle;
    }

    private function deepDive(string $class, array $classes): void
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

            $this->cycle->add($cycle);

            /**
             * Stop the recursion, we can switch to another class.
             */
            return;
        } 

        $this->stack->push($class);

        $this->visited->mark($class, false);

        foreach ($classes[$class]->getDependencies() as $dependency) {
            if (isset($classes[$dependency])) {
                $this->deepDive($dependency, $classes);
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
