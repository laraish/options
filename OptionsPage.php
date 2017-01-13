<?php

namespace Laraish\Options;

use Laraish\Contracts\Options\OptionsPage as OptionsPageContract;
use Laraish\Contracts\Options\OptionsSection as OptionsSectionContract;
use Laraish\Support\Traits\ClassHelper;

class OptionsPage implements OptionsPageContract
{
    use ClassHelper;

    /**
     * The slug name to refer to this menu by (should be unique for this menu).
     * @var string
     */
    private $menuSlug;

    /**
     * The text to be used for the menu.
     * @var string
     */
    private $menuTitle;

    /**
     * The text to be displayed in the title tags of the page when the menu is selected.
     * @var string
     */
    private $pageTitle;

    /**
     * The capability required for this menu to be displayed to the user.
     * @var string
     */
    private $capability = 'manage_options';

    /**
     * The URL to the icon to be used for this menu.
     * @var string
     */
    private $iconUrl = '';

    /**
     * The position in the menu order this one should appear.
     * @var null|int
     */
    private $position = null;

    /**
     * Settings Sections to be inserted into this page.
     * @var array
     */
    private $sections = [];

    /**
     * If you wish to make this page as a sub-page of a top-level page,
     * set the top top-level here.
     * @var null|string|OptionsPageContract
     */
    private $parent;

    /**
     * The option group you wish to use in this page.
     * @var string
     */
    private $optionGroup;

    /**
     * The option name you wish to use in this page.
     * @var string
     */
    private $optionName;

    /**
     * The function to be called to output the content for this page.
     * @var callable
     */
    private $renderFunction;

    /**
     * The scripts to be enqueued.
     * @var array
     */
    private $scripts = [];

    /**
     * The styles to be enqueued.
     * @var array
     */
    private $styles = [];

    /**
     * The help tabs to be added to this page.
     * @var array
     */
    private $helpTabs = [];


    /**
     * OptionsPage constructor.
     *
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        $requiredOptions   = ['menuSlug', 'menuTitle', 'pageTitle', 'optionGroup', 'optionName'];
        $acceptableOptions = array_merge($requiredOptions, ['capability', 'iconUrl', 'position', 'sections', 'parent', 'optionGroup', 'renderFunction', 'scripts', 'styles', 'helpTabs']);

        $this->convertMapToProperties($configs, $acceptableOptions, $requiredOptions, function ($option) {
            return "The option `$option` must be defined when instantiate the class `" . static::class . "`.";
        });
    }

    /**
     * Register settings page.
     * @return void
     */
    final public function register()
    {
        // check user capabilities
        if ( ! current_user_can($this->capability())) {
            return;
        }

        // By default, the options groups for all registered settings require the manage_options capability.
        // This filter is required to change the capability required for a certain options page.
        $option_page = $this->optionGroup();
        add_filter("option_page_capability_{$option_page}", function ($capability) {
            return $this->capability();
        });

        // enqueue script and style
        $this->enqueueAssets();

        // if this is a sub-page, register this page as a sub-page to it's parent page
        if ($this->parent()) {
            $registerFunction = function () {
                $hookSuffix = add_submenu_page(
                    ($this->parent() instanceof OptionsPageContract ? $this->parent()->menuSlug() : $this->parent()),
                    $this->pageTitle(),
                    $this->menuTitle(),
                    $this->capability(),
                    $this->menuSlug(),
                    [$this, 'render']
                );

                $this->addHelpTabs($hookSuffix);
            };
        } else {
            $registerFunction = function () {
                $hookSuffix = add_menu_page(
                    $this->pageTitle(),
                    $this->menuTitle(),
                    $this->capability(),
                    $this->menuSlug(),
                    [$this, 'render'],
                    $this->iconUrl(),
                    $this->position()
                );

                $this->addHelpTabs($hookSuffix);
            };
        }

        add_action('admin_menu', $registerFunction);

        // if there are any sections append them into this page.
        if ($this->sections) {
            foreach ($this->sections as &$section) {
                if ( ! $section instanceof OptionsSectionContract) {
                    $section = new OptionsSection($section);
                }

                $section->register($this, $this->optionGroup(), $this->optionName());
            }
        }
    }

    private function addHelpTabs($hookSuffix)
    {
        if (empty($this->helpTabs)) {
            return;
        }
        
        add_action("load-{$hookSuffix}", function () {
            $screen = get_current_screen();
            foreach ($this->helpTabs as $index => $helpTab) {
                if (empty($helpTab['id'])) {
                    $helpTab['id'] = uniqid('laraish__');
                }
                $screen->add_help_tab($helpTab);
            }
        });
    }

    /**
     * The function to be called to output the content for this page.
     * @return void
     */
    public function render()
    {
        // Display settings errors registered by `add_settings_error()`.
        settings_errors();

        if (isset($this->renderFunction)) {
            $form = new OptionsForm(OptionsRepository::getInstance($this->optionName()), $this->menuSlug());
            call_user_func_array($this->renderFunction, [$this, $form]);
        } else {
            ?>
            <div class="wrap">
                <h1><?php echo esc_html(get_admin_page_title()) ?></h1>
                <form action="options.php" method="post" enctype="multipart/form-data">
                    <?php
                    if ($this->optionGroup) {
                        // output security fields for the registered setting "{{ $this->optionGroup }}"
                        settings_fields($this->optionGroup);
                    }

                    // output setting sections and their fields
                    do_settings_sections($this->menuSlug());

                    // output save settings button
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
        }
    }

    /**
     * Enqueue the css and javascript files that this page required.
     */
    private function enqueueAssets()
    {
        add_action('admin_enqueue_scripts', function ($hook) {
            wp_enqueue_media();

            if ( ! ($this->scripts() OR $this->styles())) {
                return;
            }

            $thisPageHookSuffix = '_page_' . $this->menuSlug();
            if (preg_match("/{$thisPageHookSuffix}$/", $hook)) {
                $prefix = $this->menuSlug() . '__' . static::class;

                if ($this->scripts()) {
                    foreach ((array)$this->scripts() as $scriptUrl) {
                        $scriptName = pathinfo($scriptUrl)['basename'];
                        wp_enqueue_script("{$prefix}__{$scriptName}", $scriptUrl, ['jquery', 'underscore', 'backbone']);
                    }
                }
                if ($this->styles()) {
                    foreach ((array)$this->styles() as $styleUrl) {
                        $styleName = pathinfo($styleUrl)['basename'];
                        wp_enqueue_style("{$prefix}__{$styleName}", $styleUrl);
                    }
                }
            }
        });
    }

    /**
     * The slug name to refer to this menu by (should be unique for this menu).
     * @return string
     */
    final public function menuSlug()
    {
        return $this->menuSlug;
    }

    /**
     * The text to be used for the menu.
     * @return string
     */
    final public function menuTitle()
    {
        return $this->menuTitle;
    }

    /**
     * The text to be displayed in the title tags of the page when the menu is selected.
     * @return string
     */
    final public function pageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * The option-group to be used in the page.
     * @return string
     */
    final public function optionGroup()
    {
        return $this->optionGroup;
    }

    /**
     * The option-name to be used in the page.
     * @return string
     */
    final public function optionName()
    {
        return $this->optionName;
    }

    /**
     * The capability required for this menu to be displayed to the user.
     * @return string
     */
    final public function capability()
    {
        return $this->capability;
    }

    /**
     * The URL to the icon to be used for this menu.
     * @return string
     */
    final public function iconUrl()
    {
        return $this->iconUrl;
    }

    /**
     * The position in the menu order this one should appear.
     * @return int
     */
    final public function position()
    {
        return $this->position;
    }

    /**
     * The parent page of this page if any.
     * @return null|string|OptionsPage
     */
    final public function parent()
    {
        return $this->parent;
    }

    /**
     * Script to be enqueued.
     * @return string
     */
    final public function scripts()
    {
        return $this->scripts;
    }

    /**
     * Style to be enqueued.
     * @return string
     */
    final public function styles()
    {
        return $this->styles;
    }
}