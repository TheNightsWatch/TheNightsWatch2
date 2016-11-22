<?php
namespace NightsWatch\Routine;

trait RoutineEvents
{
    /** @var callable */
    private $beforeRoutine = null;

    /** @var callable */
    private $afterRoutine = null;

    public function before(callable $callable = null)
    {
        $this->beforeRoutine = $callable;
    }

    public function after(callable $callable = null)
    {
        $this->afterRoutine = $callable;
    }

    public function doBefore()
    {
        if (!is_null($this->beforeRoutine)) {
            $func = $this->beforeRoutine;
            $func();
        }
    }

    public function doAfter()
    {
        if (!is_null($this->afterRoutine)) {
            $func = $this->afterRoutine;
            $func();
        }
    }
}
