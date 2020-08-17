<?php

namespace Laraish\Options\OptionsFieldGenerator;

class CheckboxFieldGenerator extends BaseFieldGenerator
{
    /**
     * The default configs.
     * @var array
     */
    protected $defaultConfigs = [
        'value' => '',
        'text' => '',
        'attributes' => [],
        'defaultValue' => '',
    ];

    /**
     * Generate the field markup.
     *
     * @return string
     */
    final public function generate()
    {
        $optionValue = $this->fieldValue;
        $text = $this->config('text');
        $value = $this->config('value');
        $checked = (string) $value === $optionValue ? 'checked' : null;

        $attributes = static::convertToAttributesString([
            'type' => 'checkbox',
            'name' => $this->fieldName,
            'value' => $value,
            'checked' => $checked,
        ]);

        $innerHTML = "<input {$attributes}>";

        return "<label>{$innerHTML}{$text}</label>" . $this->generateDescription();
    }

    /**
     * An option could be potentially set to a value before the field saving its value to the database for the first time.
     * This method will be automatically called with the current value of the field if the field has a value.
     * If the value of the current option is a valid value for the field, return `true`, if it's not, return `false`.
     * `$this->validateWithErrorMessage` also use this method to validate the field value.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateFieldValue($value)
    {
        return is_string($value);
    }
}
