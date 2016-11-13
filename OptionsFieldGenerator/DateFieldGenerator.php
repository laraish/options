<?php

namespace Laraish\Options\OptionsFieldGenerator;

class DateFieldGenerator extends BaseFieldGenerator
{
    /**
     * The default configs.
     * @var array
     */
    protected $defaultConfigs = [
        'attributes'   => ['class' => 'regular-text'],
        'defaultValue' => ''
    ];

    /**
     * Generate the field markup.
     *
     * @return string
     */
    final public function generate()
    {
        $allAttributes = $this->allAttributes();

        return "<input type=\"date\" {$allAttributes}>" . $this->generateDescription();
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