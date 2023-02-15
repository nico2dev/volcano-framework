<?php

namespace Volcano\Validation;


interface ValidatesWhenResolvedInterface
{
    /**
     * Validate the given class instance.
     *
     * @return void
     */
    public function validate();
}
