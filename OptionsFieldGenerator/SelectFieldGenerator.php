<?php

namespace Laraish\Options\OptionsFieldGenerator;

class SelectFieldGenerator extends BaseFieldGenerator
{
    /**
     * The default configs.
     * @var array
     */
    protected $defaultConfigs = [
        'horizontal'   => true,
        'options'      => [],
        'attributes'   => [],
        'defaultValue' => ''
    ];

    /**
     * Generate the field markup.
     *
     * @return string
     */
    final public function generate()
    {
        $innerHTML     = '';
        $selectedValue = $this->fieldValue;

        foreach ($this->config('options') as $optionText => $optionValue) {
            // if it's not an option group
            if ( ! is_array($optionValue)) {
                $escapedOptionText = esc_html($optionText);
                $OptionAttributes  = static::convertToAttributesString(['value' => $optionValue, 'selected' => $selectedValue === (string)$optionValue ? 'selected' : null]);
                $innerHTML .= "<option {$OptionAttributes}>{$escapedOptionText}</option>";
            } else {
                $innerHTML .= "<optgroup label=\"{$optionText}\">";
                foreach ($optionValue as $optionTextInGroup => $optionValueInGroup) {
                    $escapedOptionText = esc_html($optionTextInGroup);
                    $OptionAttributes  = static::convertToAttributesString(['value' => $optionValueInGroup, 'selected' => $selectedValue === (string)$optionValueInGroup ? 'selected' : null]);
                    $innerHTML .= "<option {$OptionAttributes}>{$escapedOptionText}</option>";
                }
                $innerHTML .= '</optgroup>';
            }
        }

        // add placeholder if possible
        $placeholder = $this->config('placeholder');
        if ( ! empty($innerHTML) AND $placeholder AND $selectedValue === null) {
            $placeHolderOption = "<option value=\"\" disabled selected=\"selected\">{$placeholder}</option>";
            $innerHTML         = $placeHolderOption . $innerHTML;
        }

        //return $this->field($name, $additionalAttributes, 'select', $innerHTML, false);
        $allAttributes = static::convertToAttributesString(array_merge($this->config('attributes'), ['name' => $this->fieldName]));

        return "<select {$allAttributes}>{$innerHTML}</select>" . $this->generateDescription();
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