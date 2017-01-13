<?php

namespace Laraish\Options;

use InvalidArgumentException;
use Laraish\Contracts\Options\OptionsField as OptionsFieldContract;
use Laraish\Contracts\Options\OptionsPage as OptionsPageContract;
use Laraish\Contracts\Options\OptionsSection as OptionsSectionContract;
use Laraish\Support\Traits\ClassHelper;

class OptionsField implements OptionsFieldContract
{
    use ClassHelper;

    /**
     * The ID of this field
     * @var string
     */
    private $id;

    /**
     * The title of this field
     * @var string
     */
    private $title;

    /**
     * The type of the field. Default is 'text'.
     * @var string
     */
    private $type = 'text';

    /**
     * The capability required for this field to be displayed to the current user.
     * @var string
     */
    private $capability = 'manage_options';

    /**
     * The option-group that this field is going to register or refer.
     * It's recommended to set this property to the same value as page-slug of the page that this field will reside in.
     * @var string
     */
    private $optionGroup;

    /**
     * The option-name that this field is going to register or refer.
     * @var string
     */
    private $optionName;

    /**
     * Function that fills the field with the desired content. The function should echo its output.
     * This function will be given an argument(array) with two elements `field`(OptionsField) and `form`(OptionsForm) object.
     * @var callable
     */
    private $renderFunction;

    /**
     * The sanitize function called before saving option data.
     * @var callable
     */
    private $sanitizeFunction;

    /**
     * The configurations of the field.
     * @var array
     */
    private $configs;

    /**
     * SettingsSection constructor.
     * require an `id` and `title` option.
     *
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        $requiredConfigs = ['id', 'title'];
        $optionalConfigs = array_merge($requiredConfigs, ['type', 'renderFunction', 'sanitizeFunction', 'capability']);

        // convert the basic configs to properties.
        $this->convertMapToProperties($configs, $optionalConfigs, $requiredConfigs, function ($option) {
            return "The option `$option` must be defined when instantiate the class `" . static::class . "`.";
        });

        $this->configs = $configs;
    }

    /**
     * Register field to a specific page.
     *
     * @param OptionsPageContract|string $optionsPage       menu-slug of a page or a SettingsPage object
     * @param OptionsSectionContract|string $optionsSection section-id of a section or a SettingsSection object
     * @param string $optionGroup                           The option-group to be used.
     * @param string $optionName                            The option-name to be used.
     * @param bool $hook                                    Determine if call register functions in appropriate hook or not.
     *
     * @return void
     */
    final public function register($optionsPage, $optionsSection, $optionGroup, $optionName, $hook = true)
    {
        // check user capabilities
        if ( ! current_user_can($this->capability())) {
            return;
        }

        $page    = $optionsPage instanceof OptionsPageContract ? $optionsPage->menuSlug() : $optionsPage;
        $section = $optionsSection instanceof OptionsSectionContract ? $optionsSection->id() : $optionsSection;

        // Copy the original `Field` object and change the `optionGroup` and `optionName` private properties.
        // So the state of the original object won't change.
        $field = $this->cloneAndChangeState(['optionGroup' => $optionGroup, 'optionName' => $optionName]);

        // create the args that will be passed to render function.
        $args    = [];
        $options = OptionsRepository::getInstance($field->optionName());

        if ($this->renderFunction) {
            $form           = new OptionsForm($options, $page);
            $args           = ['field' => $field, 'form' => $form];
            $renderFunction = $this->renderFunction;
            if ($this->sanitizeFunction) {
                $sanitizeFunction = $this->sanitizeFunction;
            } else {
                $sanitizeFunction = function ($values) {
                    return $values;
                };
            }
        } else {
            // the corresponding OptionsFieldGenerator class name to be used for rendering and sanitize the field.
            $optionsFieldGeneratorClassName = '\\Laraish\\Options\\OptionsFieldGenerator\\' . ucfirst(strtolower($this->type())) . 'FieldGenerator';

            if ( ! class_exists($optionsFieldGeneratorClassName)) {
                throw new InvalidArgumentException("Invalid field type `{$field->type()}`.");
            }

            /** @var \Laraish\Contracts\Options\OptionsFieldGenerator $optionsFieldGenerator */
            $optionsFieldGenerator = new $optionsFieldGeneratorClassName($field->id(), $options, $page, $field->configs());

            $renderFunction = function () use ($optionsFieldGenerator) {
                echo $optionsFieldGenerator->generate();
            };

            $sanitizeFunction = $this->sanitizeFunction ?: [$optionsFieldGenerator, 'sanitize'];
        }

        // the register function
        $register = function (OptionsFieldContract $field, $page, $section, $optionGroup, $optionName, array $args, $renderFunction) {
            register_setting($optionGroup, $optionName);
            add_settings_field($field->id(), $field->title(), $renderFunction, $page, $section, $args);
        };

        if ($hook) {
            add_action('admin_init', function () use ($field, $page, $section, $optionGroup, $optionName, $register, $args, $renderFunction) {
                $register($field, $page, $section, $optionGroup, $optionName, $args, $renderFunction);
            });
        } else {
            $register($field, $page, $section, $optionGroup, $optionName, $args, $renderFunction);
        }


        // add the sanitize function later (after `admin_init`)
        // to avoid unnecessary sanitize function calls at the creation of FieldGenerator classes
        add_action('current_screen', function () use ($optionName, $sanitizeFunction, $field) {
            add_filter("pre_update_option_{$optionName}", function ($value, $oldValue) use ($sanitizeFunction, $field, $optionName) {
                $fieldId = $field->id();

                if (isset($value[$fieldId])) {
                    $_value    = $value[$fieldId];
                    $_oldValue = isset($oldValue[$fieldId]) ? $oldValue[$fieldId] : null;

                    $value[$fieldId] = call_user_func_array($sanitizeFunction, [$_value, $_oldValue]);
                } elseif (isset($_FILES[$optionName]['tmp_name'][$fieldId])) {
                    $filesInfo = $_FILES[$optionName];

                    $value[$fieldId] = call_user_func_array($sanitizeFunction, [$filesInfo]);
                }

                return $value;
            }, 10, 2);
        });
    }

    /**
     * The configurations of the field.
     *
     * @param null $name
     *
     * @return mixed
     */
    public function configs($name = null)
    {
        if ($name === null) {
            return $this->configs;
        }

        return isset($this->configs[$name]) ? $this->configs[$name] : null;
    }

    /**
     * The option-group that this field is going to register or refer.
     * @return string
     */
    final public function optionGroup()
    {
        return $this->optionGroup;
    }

    /**
     * The option-name that this field is going to register or refer.
     * @return string
     */
    final public function optionName()
    {
        return $this->optionName;
    }

    /**
     * String for use in the 'id' attribute of tags.
     * @return string
     */
    final public function id()
    {
        return $this->id;
    }

    /**
     * Title of the field.
     * @return string
     */
    final public function title()
    {
        return $this->title;
    }

    /**
     * The type of the field.
     * @return string
     */
    final public function type()
    {
        return $this->type;
    }

    /**
     * The capability required for this field to be displayed to the current user.
     * @return string
     */
    public function capability()
    {
        return $this->capability;
    }
}