<?php

namespace Laraish\Options\OptionsFieldGenerator;

class CheckboxesFieldGenerator extends BaseFieldGenerator
{
    /**
     * The default configs.
     * @var array
     */
    protected $defaultConfigs = [
        'horizontal' => true,
        'options' => [],
        'attributes' => [],
        'defaultValue' => [],
    ];

    /**
     * Generate the field markup.
     *
     * @return string
     */
    final public function generate()
    {
        $checkedOptions = $this->fieldValue;
        $normalizedFieldName = $this->fieldName . '[]';
        $markUp = '';

        foreach ($this->config('options') as $optionText => $optionValue) {
            $escapedOptionText = esc_html($optionText);
            $checked = in_array((string) $optionValue, (array) $checkedOptions) ? 'checked' : null;

            $allAttributes = static::convertToAttributesString(
                array_merge($this->config('attributes'), [
                    'type' => 'checkbox',
                    'name' => $normalizedFieldName,
                    'value' => $optionValue,
                    'checked' => $checked,
                ])
            );

            $checkboxHtml = "<input {$allAttributes}>";

            $markUp .= "<label>{$checkboxHtml}{$escapedOptionText}</label>\n";

            if (!$this->config('horizontal')) {
                $markUp .= "<br>\n";
            }
        }

        return $markUp . $this->generateDescription();
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
        return is_array($value);
    }
}
