<?php

namespace Laraish\Options;

use Laraish\Contracts\Options\OptionsPage as OptionsPageContract;
use Laraish\Contracts\Options\OptionsSection as OptionsSectionContract;
use Laraish\Contracts\Options\OptionsField as OptionsFieldContract;
use Laraish\Support\Traits\ClassHelper;

class OptionsSection implements OptionsSectionContract
{
    use ClassHelper;

    /**
     * ID of the section.
     * @var string
     */
    private $id;

    /**
     * Title of the section.
     * @var string
     */
    private $title;

    /**
     * The capability required for this section to be displayed to the current user.
     * @var string
     */
    private $capability = 'manage_options';

    /**
     * Description of the section.
     * @var string
     */
    private $description;

    /**
     * settings fields in the section.
     * @var array
     */
    private $fields;

    /**
     * Function that fills the section with the desired content. The function should echo its output.
     * @var callable
     */
    private $renderFunction;

    /**
     * OptionsSection constructor.
     *
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        $requiredOptions   = ['id', 'title'];
        $acceptableOptions = array_merge($requiredOptions, ['description', 'renderFunction', 'fields', 'capability']);

        $this->convertMapToProperties($configs, $acceptableOptions, $requiredOptions, function ($option) {
            return "The option `$option` must be defined when instantiate the class `" . static::class . "`.";
        });
    }

    /**
     * Register the section to a specific page.
     *
     * @param OptionsPageContract|string $optionsPage menu-slug of a page or a OptionsPage object
     * @param string $optionGroup                     The option-group to be used.
     * @param string $optionName                      The option-group to be used.
     * @param bool $hook                              Determine if call register functions in appropriate hook or not.
     *
     * @return void
     */
    final public function register($optionsPage, $optionGroup, $optionName, $hook = true)
    {
        // check user capabilities
        if ( ! current_user_can($this->capability())) {
            return;
        }

        $page = $optionsPage instanceof OptionsPageContract ? $optionsPage->menuSlug() : $optionsPage;

        $register = function ($page, OptionsSectionContract $section, $optionGroup, $optionName) {
            add_settings_section($section->id(), $section->title(), [$section, 'render'], $page);
            if (isset($this->fields)) {
                foreach ($this->fields as &$field) {
                    if ( ! $field instanceof OptionsFieldContract) {
                        $field = new OptionsField($field);
                    }
                    $field->register($page, $section->id(), $optionGroup, $optionName, false);
                }
            }
        };

        if ($hook) {
            add_action('admin_init', function () use ($page, $register, $optionGroup, $optionName) {
                $register($page, $this, $optionGroup, $optionName);
            });
        } else {
            $register($page, $this, $optionGroup, $optionName);
        }
    }

    /**
     * The id of the section.
     * @return string
     */
    final public function id()
    {
        return $this->id;
    }

    /**
     * Title of the section.
     * @return string
     */
    final public function title()
    {
        return $this->title;
    }

    /**
     * The capability required for this field to be displayed to the current user.
     * @return string
     */
    public function capability()
    {
        return $this->capability;
    }

    /**
     * The description of the section.
     * @return string
     */
    final public function description()
    {
        return $this->description;
    }

    /**
     * Function that fills the section with the desired content. The function should echo its output.
     * @return void
     */
    public function render()
    {
        if (isset($this->renderFunction)) {
            call_user_func_array($this->renderFunction, [$this]);
        } elseif ($this->description()) {
            echo '<p>' . $this->description() . '</p>';
        }
    }
}