<?php

namespace Laraish\Options\OptionsFieldGenerator;

use Laraish\Support\Arr;

class SelectFieldGenerator extends BaseFieldGenerator
{
    /**
     * The default configs.
     * @var array
     */
    protected $defaultConfigs = [
        'richMode' => false,
        'options' => [],
        'multiple' => false,
        'attributes' => ['class' => 'regular-text'],
        'defaultValue' => '',
    ];

    protected static $scripts = ['js/libs/selectize.js', 'js/selectFieldGenerator.js'];

    protected static $styles = ['css/libs/selectize.css'];

    /**
     * Generate the field markup.
     *
     * @return string
     */
    final public function generate()
    {
        $innerHTML = '';
        $selectedValue = Arr::cast($this->fieldValue);

        if ($this->config('richMode') === true) {
            $this->configs['attributes']['class'] .= ' laraish-select';
        }

        $options = Arr::cast($this->config('options'));

        if (Arr::isSequential($options)) {
            $options = array_combine($options, $options);
        }

        foreach ($options as $optionText => $optionValue) {
            // if it's not an option group
            if (!is_array($optionValue)) {
                $escapedOptionText = esc_html($optionText);
                $isSelected = in_array((string) $optionValue, $selectedValue);
                $OptionAttributes = static::convertToAttributesString([
                    'value' => $optionValue,
                    'selected' => $isSelected ? 'selected' : null,
                ]);
                $innerHTML .= "<option {$OptionAttributes}>{$escapedOptionText}</option>";
            } else {
                $innerHTML .= "<optgroup label=\"{$optionText}\">";
                if (Arr::isSequential($optionValue)) {
                    $optionValue = array_combine($optionValue, $optionValue);
                }
                foreach ($optionValue as $optionTextInGroup => $optionValueInGroup) {
                    $escapedOptionText = esc_html($optionTextInGroup);
                    $isSelected = in_array((string) $optionValueInGroup, $selectedValue);
                    $OptionAttributes = static::convertToAttributesString([
                        'value' => $optionValueInGroup,
                        'selected' => $isSelected ? 'selected' : null,
                    ]);
                    $innerHTML .= "<option {$OptionAttributes}>{$escapedOptionText}</option>";
                }
                $innerHTML .= '</optgroup>';
            }
        }

        // Add placeholder if possible
        $placeholder = $this->config('placeholder');
        if (!empty($innerHTML) and $placeholder and $selectedValue === null) {
            $placeHolderOption = "<option value=\"\" disabled selected=\"selected\">{$placeholder}</option>";
            $innerHTML = $placeHolderOption . $innerHTML;
        }

        // If `multiple` is allowed append `[]` to the end of `name` attribute,
        // so we can receive the data as array instead of string.
        $isMultiple = $this->config('multiple');
        $attributes = ['name' => $this->fieldName . ($isMultiple ? '[]' : '')];
        if ($isMultiple) {
            $attributes['multiple'] = 'multiple';
        }

        $allAttributes = array_merge($this->config('attributes'), $attributes);
        $allAttributes = static::convertToAttributesString($allAttributes);

        $html = "<select {$allAttributes}>{$innerHTML}</select>";

        // Add help link if possible
        if ($helpLink = $this->generateHelpLink()) {
            $html = '<div class="laraish-flex-line">' . $html . "{$helpLink}</div>";
        }

        $html .= $this->generateDescription();

        return $html;
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
        return is_string($value) or is_array($value);
    }
}
