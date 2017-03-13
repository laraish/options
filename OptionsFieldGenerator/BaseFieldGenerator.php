<?php

namespace Laraish\Options\OptionsFieldGenerator;

use InvalidArgumentException;
use Laraish\Contracts\Options\OptionsRepository as OptionsRepositoryContract;
use Laraish\Contracts\Options\OptionsFieldGenerator as OptionsFieldGeneratorContract;
use Laraish\Options\OptionsPage;
use Laraish\Support\Template;

abstract class BaseFieldGenerator implements OptionsFieldGeneratorContract
{
    /**
     * The original field name.
     * @var string
     */
    protected $fieldId;

    /**
     * The normalized field name.
     * @var string
     */
    protected $fieldName;

    /**
     * The normalized field name.
     *
     * @var mixed
     */
    protected $fieldValue;

    /**
     * The Options instance.
     * @var OptionsRepositoryContract
     */
    protected $options;

    /**
     * The configs of this generator.
     * @var array
     */
    protected $configs;

    /**
     * The default configs.
     * @var array
     */
    protected $defaultConfigs = [
        'attributes' => []
    ];

    /**
     * The instance of `Template`.
     * @var Template
     */
    protected $templateInstance;

    /**
     * The style sheets to be enqueued for the field.
     * A path relatives to `resources/assets`.
     * @var string
     */
    protected static $styles = [];

    /**
     * The scripts to be enqueued for the field.
     * A path relatives to `resources/assets`.
     * @var string
     */
    protected static $scripts = [];

    /**
     * The template file of the field.
     * A path relatives to `resources/templates`.
     * If you set this property, you will be able to
     * call `static::renderTemplate` to render the template.
     * @var string
     */
    protected static $template;


    /**
     * A static method for enqueuing style and script related to this field generator.
     *
     * @param string $optionsPage menu slug of a OptionsPage.
     *
     * @return void
     */
    final private static function enqueueAssetsOnce($optionsPage)
    {
        static $isEnqueued;

        if ($isEnqueued === true) {
            return;
        }
        $isEnqueued = true;

        // if no script and style need to be enqueued quit immediately.
        if ( ! (static::$scripts OR static::$styles)) {
            return;
        }

        $absolutePath = str_replace('\\', '/', ABSPATH);
        $currentDir   = str_replace('\\', '/', __DIR__);
        $assetsUrl    = home_url(str_replace($absolutePath, '', $currentDir)) . '/resources/assets/';

        add_action('admin_enqueue_scripts', function ($hook) use ($optionsPage, $assetsUrl) {
            $thisPageHookSuffix = '_page_' . $optionsPage;
            if (preg_match("/{$thisPageHookSuffix}$/", $hook)) {
                $prefix = $optionsPage . '__' . static::class . '__';

                if (static::$scripts) {
                    foreach ((array)static::$scripts as $script) {
                        if (filter_var($script, FILTER_VALIDATE_URL) === false) {
                            $script = $assetsUrl . trim($script, '/');
                        }

                        OptionsPage::enqueueAsset($script, 'script', $prefix);
                    }
                }
                if (static::$styles) {
                    foreach ((array)static::$styles as $style) {
                        if (filter_var($style, FILTER_VALIDATE_URL) === false) {
                            $style = $assetsUrl . trim($style, '/');
                        }

                        OptionsPage::enqueueAsset($style, 'style', $prefix);
                    }
                }
            }
        });
    }

    /**
     * Load the template file into template engine just once for this class.
     *
     * @param static $instance
     */
    final private static function setInstanceTemplateOnce($instance)
    {
        static $templateContent;

        if ( ! isset($templateContent)) {
            $templateContent = '';

            if (static::$template) {
                $templatePath = __DIR__ . '/resources/templates/' . trim(static::$template, '/');
                if (file_exists($templatePath)) {
                    $templateContent = file_get_contents($templatePath);
                }
            }

        }

        $instance->templateInstance = new Template($templateContent);
    }

    /**
     * Convert a map to attributes string.
     *
     * @param array $attributes The attributes map something like ['name'=> 'option_name[url]', 'type'=> 'url']
     *
     * @return string
     */
    final protected static function convertToAttributesString(array $attributes)
    {
        $attributesString = '';
        foreach ($attributes as $name => $value) {
            if ($value === null) {
                continue;
            }
            $escapedValue = esc_attr($value);
            $attributesString .= "$name=\"$escapedValue\" ";
        }

        return rtrim($attributesString, ' ');
    }

    /**
     * Generate a datalist element markup.
     *
     * @param string $id
     * @param array $optionValues
     *
     * @return string
     */
    final public static function generateDatalist($id, array $optionValues)
    {
        $innerHTML = '';

        foreach ($optionValues as $optionValue) {
            $innerHTML .= "<option value=\"{$optionValue}\">";
        }

        return "<datalist id=\"{$id}\">{$innerHTML}</datalist>";
    }

    /**
     * Field generator constructor.
     *
     * @param string $fieldName                  The name of this field.
     * @param OptionsRepositoryContract $options The OptionsRepository object.
     * @param string $optionsPage                The menu slug of an options page.
     * @param array $configs                     The configurations.
     */
    public function __construct($fieldName, OptionsRepositoryContract $options, $optionsPage, array $configs = [])
    {
        // check if the 'defaultValue' is a valid value for the field.
        if (array_key_exists('defaultValue', $configs)) {
            if ( ! $this->validateFieldValue($configs['defaultValue'])) {
                throw new InvalidArgumentException("The value `{$configs['defaultValue']}` is not a valid `defaultValue`.");
            }
        }


        $this->options = $options;
        $this->configs = array_replace_recursive($this->defaultConfigs, $configs);

        $this->fieldId    = $fieldName;
        $this->fieldName  = $this->normalizeFieldName($fieldName);
        $this->fieldValue = $this->normalizeFieldValue($fieldName);

        static::enqueueAssetsOnce($optionsPage);
        static::setInstanceTemplateOnce($this);
    }

    /**
     * Get a particular config in `$this->configs`.
     *
     * @param string $key
     *
     * @return mixed
     */
    final protected function config($key)
    {
        return isset($this->configs[$key]) ? $this->configs[$key] : null;
    }

    /**
     * Normalize the name attribute value.
     *
     * @param string $originalFieldName
     *
     * @return string|null
     */
    final private function normalizeFieldName($originalFieldName)
    {
        return $this->options->optionName() . "[$originalFieldName]";
    }

    /**
     * Get the filed value.
     *
     * @param string $originalFieldName
     *
     * @return null|string
     */
    final private function normalizeFieldValue($originalFieldName)
    {
        $defaultValue = $this->config('defaultValue');
        $value        = $this->options->get($originalFieldName, $defaultValue);

        // if the current option has a value
        if ($value !== $defaultValue) {
            // and if the current option is not a valid value for the field
            if ( ! $this->validateFieldValue($value)) {
                // then set the old value to the default value of the field
                $this->options->set($originalFieldName, $defaultValue);
                $value = $defaultValue;
            }
        }

        return $value;
    }

    /**
     * Get the basic attributes (`name` and `value`) and the user defined attributes.
     *
     * @return string
     */
    final protected function allAttributes()
    {
        $name  = $this->fieldName;
        $value = $this->fieldValue;

        return static::convertToAttributesString(array_merge($this->config('attributes'), ['name' => $name, 'value' => $value]));
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
        return $this->validateWithErrorMessage($value, "`{$this->config('title')}` is not a valid value.");
    }

    /**
     * A helper method could be useful to work together with `sanitize` method.
     *
     * @param mixed $value    The value to be sanitized.
     * @param string $message The error message to be shown if the value is invalid.
     *
     * @return mixed
     */
    final protected function validateWithErrorMessage($value, $message)
    {
        //$fieldId = $this->config('id');

        if ( ! $this->validateFieldValue($value)) {
            add_settings_error($this->fieldId, 'invalid_type', $message);

            // restore to old value
            $value = $this->fieldValue;
        }

        return $value;
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
        return true;
    }

    /**
     * Generate the description of the field.
     * @return string
     */
    protected function generateDescription()
    {
        $markUp = '';

        if ($description = $this->config('description')) {
            $markUp = "<p class=\"description\">{$description}</p>";
        }

        return $markUp;
    }

    /**
     * Generate the help link for the field.
     * @return string
     */
    protected function generateHelpLink()
    {
        $markUp = '';

        if ($helpLink = $this->config('helpLink')) {
            $markUp = "<a href=\"{$helpLink}\" target=\"_blank\" class=\"laraish-help-link\"><i class=\"dashicons-before dashicons-info\"></i></a>";
        }

        return $markUp;
    }

    protected function generateInput($type, $allAttributes = null)
    {
        $allAttributes = $allAttributes ?: $this->allAttributes();

        // Add `list` attribute if possible
        if ($datalist = $this->config('datalist')) {
            $datalistHtml = static::generateDatalist($datalist['id'], $datalist['data']);
            $allAttributes .= " list=\"{$datalist['id']}\"";
        }

        // Create the `input` html
        $html = "<input type=\"{$type}\" {$allAttributes}>";

        // Add `datalist` element if possible
        if (isset($datalistHtml)) {
            $html .= $datalistHtml;
        }

        // Add prefix and suffix if possible
        $suffix = $this->config('suffix');
        $prefix = $this->config('prefix');
        if ($suffix OR $prefix) {
            $suffix = $suffix ? "<span class=\"laraish-input-group-addon\">{$suffix}</span>" : '';
            $prefix = $prefix ? "<span class=\"laraish-input-group-addon\">{$prefix}</span>" : '';
            $html   = "<div class=\"laraish-input-group\">{$prefix}" . $html . "{$suffix}</div>";
        }

        // Add help link if possible
        if ($helpLink = $this->generateHelpLink()) {
            $html = '<div class="laraish-flex-line">' . $html . "{$helpLink}</div>";
        }

        // Add the description
        $html .= $this->generateDescription();

        return $html;
    }

    /**
     * Render the template instance.
     *
     * @param array $data
     *
     * @return string
     */
    final protected function renderTemplate(array $data)
    {
        return $this->templateInstance->render($data);
    }

    /**
     * Generate the field markup.
     *
     * @return string
     */
    abstract public function generate();
}