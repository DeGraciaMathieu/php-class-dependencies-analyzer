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

    function deepDive($class, $classes, &$cycles): void
    {
        if ($this->visited->isMarked($class)) {
            return;
        }

        /**
         * If the class is already in the stack, we have a cycle.
         */
        if ($this->stack->contains($class)) {

            $cycle = array_slice($this->stack->stack, array_search($class, $this->stack->stack));

            $cycles[] = $cycle;

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
         * Mark the class as fully explored.
         */
        $this->visited->mark($class, true);
    }
}
