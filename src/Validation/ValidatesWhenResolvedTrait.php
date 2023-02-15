<?php

namespace Volcano\Validation;

use Volcano\Validation\ValidationException;
use Volcano\Validation\UnAuthorizedException;

/**
 * Provides default implementation of ValidatesWhenResolvedInterface.
 */
trait ValidatesWhenResolvedTrait
{
    /**
     * Validate the class instance.
     *
     * @return void
     */
    public function validate()
    {
        $instance = $this->getValidatorInstance();

        if (! $this->passesAuthorization()) {
            $this->failedAuthorization();
        } else if (! $instance->passes()) {
            $this->failedValidation($instance);
        }
    }

    /**
     * Get the validator instance for the request.
     *
     * @return \Volcano\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        return $this->validator();
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Volcano\Validation\Validator  $validator
     * @return mixed
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }

    /**
     * Determine if the request passes the authorization check.
     *
     * @return bool
     */
    protected function passesAuthorization()
    {
        if (method_exists($this, 'authorize')) {
            return $this->authorize();
        }

        return true;
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return mixed
     */
    protected function failedAuthorization()
    {
        throw new UnAuthorizedException();
    }
}
