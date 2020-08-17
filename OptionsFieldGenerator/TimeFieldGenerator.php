<?php

namespace Laraish\Options\OptionsFieldGenerator;

class TimeFieldGenerator extends BaseFieldGenerator
{
    /**
     * The default configs.
     * @var array
     */
    protected $defaultConfigs = [
        'attributes' => ['class' => 'regular-text'],
        'defaultValue' => '',
    ];

    /**
     * Generate the field markup.
     *
     * @return string
     */
    final public function generate()
    {
        return $this->generateInput('time');
    }

    /**
     * A callback function that sanitizes the option's value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function sanitize($value)
    {
        return $value;
    }
}
