<?php

namespace Quantum;

class VariantObject
{
    public function toArray()
    {
        return (array) $this;
    }

    public function toValuetree()
    {
        return new_vt($this->toArray());
    }
}