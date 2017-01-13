A simple framework for creating WordPress options page.

#Basic Usage

Here is an example for creating an options page.

```php
use Laraish\Options\OptionsPage;

$optionsPage = new OptionsPage([
    'menuSlug'    => 'my_options_page',
    'menuTitle'   => 'My Options Page',
    'pageTitle'   => 'My Options Page',
    'iconUrl'     => 'dashicons-welcome-learn-more',
    'optionGroup' => 'my_options_page',
    'optionName'  => 'my_options',
    'capability'  => 'manage_categories',
    'sections'    => [
        [
            'id'          => 'section-id',
            'title'       => 'Section title',
            'description' => 'Section Description',
            'fields'      => [
                [
                    'id'          => 'my-avatar',
                    'type'        => 'media',
                    'title'       => 'Avatar',
                    'description' => 'Choose an image for your avatar.'
                ],
                [
                    'id'    => 'my-email',
                    'type'  => 'email',
                    'title' => 'E-mail',
                ],
                [
                    'id'         => 'my-nice-name',
                    'type'       => 'text',
                    'title'      => 'Nice name',
                    'attributes' => [
                        'placeholder' => 'your nice name',
                        'maxlength'   => 10,
                        'class'       => 'regular-text'
                    ],
                ],
                [
                    'id'    => 'my-description',
                    'type'  => 'textarea',
                    'title' => 'About Me',
                ],
            ]
        ]
    ],
    'helpTabs'    => [
        [
            'title'    => 'tab-1',
            'content'  => '<p>description here</p>',
        ],
        [
            'title'    => 'tab-2',
            'content'  => '<p>description here</p>',
        ]
    ],
    'scripts'     => ['https://unpkg.com/vue/dist/vue.js'],
    'styles'      => ['/my-css.css'],
]);
    
$optionsPage->register();

```

The example code above is going to create an options page looks like this

<img src="https://1design.jp/wp-content/uploads/2017/01/options-page.png">

## Usage of `OptionsSection` and `OptionsField`
You can also replace the value of `'sections'` with an array of `OptionsSection` objects and replace the value of `'fields'` with an array of `OptionsField` objects.

```php
use Laraish\Options\OptionsPage;

/*------------------------------------*\
    # Field objects
\*------------------------------------*/

$avatarField = new OptionsField([
    'id'          => 'my-avatar',
    'type'        => 'media',
    'title'       => 'Avatar',
    'description' => 'Choose an image for your avatar.'
]);

$emailField = new OptionsField([
    'id'    => 'my-email',
    'type'  => 'email',
    'title' => 'E-mail',
]);

$niceNameField = new OptionsField([
    'id'         => 'my-nice-name',
    'type'       => 'text',
    'title'      => 'Nice name',
    'attributes' => [
        'placeholder' => 'your nice name',
        'maxlength'   => 10,
        'class'       => 'regular-text'
    ]
]);

$descriptionField = new OptionsField([
    'id'    => 'my-description',
    'type'  => 'textarea',
    'title' => 'About Me',
]);

$demoField = new OptionsField([
    'id'    => 'my-demo',
    'type'  => 'text',
    'title' => 'Demo text field',
]);



/*------------------------------------*\
    # Section object
\*------------------------------------*/

$demoSection = new OptionsSection([
    'id'          => 'section-id',
    'title'       => 'Section title',
    'description' => 'Section Description',
    'fields'      => [
        $demoField,
    ]
]);



/*------------------------------------*\
    # Page object
\*------------------------------------*/

$optionsPage = new OptionsPage([
    'menuSlug'    => 'my_options_page',
    'menuTitle'   => 'My Options Page',
    'pageTitle'   => 'My Options Page',
    'iconUrl'     => 'dashicons-welcome-learn-more',
    'optionGroup' => 'my_options_page',
    'optionName'  => 'my_options',
    'capability'  => 'manage_categories',
    'sections'    => [
        [
            'id'          => 'section-id',
            'title'       => 'Section title',
            'description' => 'Section Description',
            'fields'      => [
                $avatarField,
                $emailField,
                $niceNameField,
                $descriptionField,
            ]
        ],

        $demoSection,
    ],
    'helpTabs'    => [
        [
            'title'   => 'tab-1',
            'content' => '<p>description here</p>',
        ],
        [
            'title'   => 'tab-2',
            'content' => '<p>description here</p>',
        ]
    ],
    'scripts'     => ['https://unpkg.com/vue/dist/vue.js'],
    'styles'      => ['/my-css.css'],
]);


/*------------------------------------*\
    # register page/section/field
\*------------------------------------*/

// register page
$optionsPage->register();

// register a section to a page(Settings -> General)
$demoSection->register('general', 'demo-section-group', 'demo-section-options');

// register a field to a section of page(Settings -> General -> `default` section)
$demoField->register('general', 'default', 'demo-section-group', 'demo-section-options');
```

## Get the value of an option
You can use the `OptionsRepository` to get the value of an option.

```php
use Laraish\Options\OptionsRepository;

// Get the value of 'my-nice-name' in 'my_options'.
// 'my_options' is the option name.
$myOptions = new OptionsRepository('my_options');
echo $myOptions->get('my-nice-name');
    
// also you can set the value by calling the set() method.
$myOptions->set('my_options','new value');
```




# `OptionsPage`

## Options

### `menuSlug`
| Type   | required |
|--------|----------|
| string | yes      |

The slug name to refer to the menu by (should be unique for this menu).

### `menuTitle`
| Type   | required |
|--------|----------|
| string | yes      |

The text to be used for the menu.

### `pageTitle`
| Type   | required |
|--------|----------|
| string | yes      |

The text to be displayed in the title tags of the page when the menu is selected.

### `optionGroup`
| Type   | required |
|--------|----------|
| string | yes      |

The option group you wish to use in the page.

### `optionName`
| Type   | required |
|--------|----------|
| string | yes      |

The option name you wish to use in the page.

### `capability`
| Type   | Default          |
|--------|------------------|
| string | 'manage_options' |

The [capability](https://codex.wordpress.org/Roles_and_Capabilities) required for this menu to be displayed to the user.

### `position`
| Type   | Default |
|--------|---------|
| int    | null    |

The position in the menu order this one should appear.

### `iconUrl`
| Type   | Default |
|--------|-------- |
| string | ''      |

The URL (or [icon name](https://developer.wordpress.org/resource/dashicons/)) to the icon to be used for the menu.

### `parent`

| Type                                | Default |
|-------------------------------------|---------|
| null / string / OptionsPageContract | null    |

If you wish to make the page as a sub-page of a top-level page, set the top top-level page here.

### `sections`
| Type  | Default |
|-------|-------- |
| array | []      |

Settings-Sections to be inserted into the page. Every element of this array represents a Settings-Section.  
See [Options for OptionsSection](#options-1) for more details.

### `renderFunction`
| Type     | Default |
|----------|-------- |
| callable | null    |

The function to be called to output the content for the page.  
This function retrieves two arguments; the first one is an instance of `OptionsPage`, the second one is an instance of `OptionsForm`. By using the `OptionsForm` object you can create form input elements much easier than by hard coding. 

Below is an example of customizing the output of an options page.

```php
use Laraish\Options\OptionsPage;

$optionsPage = new OptionsPage([
    'menuSlug'       => 'my_options_page',
    'menuTitle'      => 'My Options Page',
    'pageTitle'      => 'My Options Page',
    'optionGroup'    => 'my_options_page',
    'optionName'     => 'my_options',
    'renderFunction' => function (OptionsPage $page, OptionsForm $form) {
        ?>
        <div class="wrap">
            <h1><?php echo $page->pageTitle(); ?></h1>
            <form action="options.php" method="post" enctype="multipart/form-data">
                <?php
                // output security fields for the registered setting "{{ $this->optionGroup }}"
                settings_fields($page->optionGroup());


                // output fields
                ?>
                <p><?php echo $form->email('email', ['attributes' => ['placeholder' => 'foo@example.com']]); ?></p>
                <p><?php echo $form->textarea('about-us'); ?></p>


                <?php
                // output save settings button
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
]);
```

### `helpTabs`
| Type     | Default |
|----------|-------- |
| array    | []      |

The help tabs to be added to the page. Every tab represented by an [array](https://codex.wordpress.org/Class_Reference/WP_Screen/add_help_tab).

### `scripts`
| Type     | Default |
|----------|-------- |
| array    | []      |

The scripts to be enqueued to the page.


### `styles`
| Type     | Default |
|----------|-------- |
| array    | []      |

The styles to be enqueued to the page.

## Methods
### `register()`

Register the settings page.


# `OptionsSection`

## Options

### `id`
| Type   | required |
|--------|----------|
| string | yes      |

ID of the section.

### `title`
| Type   | required |
|--------|----------|
| string | yes      |

Title of the section.

### `description`
| Type   | Default |
|--------|---------|
| string | null    |

Description of the section.

### `renderFunction`
| Type     | Default |
|----------|-------- |
| callable | null    |

Function that fills the section with the desired content. The function should echo its output.

### `fields`
| Type     | Default |
|----------|-------- |
| array    | []      |

settings fields in the section. See [Options for OptionsField](#options-2) for more details.

### `capability`
| Type   | Default          |
|--------|------------------|
| string | 'manage_options' |

The [capability](https://codex.wordpress.org/Roles_and_Capabilities) required for this section to be displayed to the current user.

## Methods
### `register()`

Register the settings page.

# `OptionsField`
## Options
### id
| Type   | Default |
|--------|-------- |
| string | ''      |

The ID of this field.

### title
| Type   | Default |
|--------|-------- |
| string | null    |

The title of this field.

### type
| Type   | Default |
|--------|-------- |
| string | 'text'  |

The type of the field. Default is 'text'. See [Field types](#field-types) for more details.

### capability
| Type   | Default          |
|--------|------------------|
| string | 'manage_options' |

The capability required for this field to be displayed to the current user.

### renderFunction
| Type     | Default |
|----------|-------- |
| callable | null    |

Function that fills the field with the desired content. The function should echo its output.  
This function will be given an argument(array) with two elements `field`(OptionsField) and `form`(OptionsForm) object.

### sanitizeFunction
| Type     | Default |
|----------|-------- |
| callable | null    |

The sanitize function called before saving option data.

# Field types
## Common Options
<dl>
<dt>attributes (array)</dt>
<dd>The attributes of the field element.<br>
For example <code>['placeholder'=> 'Type your name', 'class'=> 'foo bar baz']</code>.
</dd>
<dt>defaultValue (mixed)</dt>
<dd>The default value of the field.</dd>
</dl>

## Color
Same as [Common Options](#common-options).

## Date
Same as [Common Options](#common-options).

## Email
Same as [Common Options](#common-options).

## Hidden
Same as [Common Options](#common-options).

## Number
Same as [Common Options](#common-options).

## Password
Same as [Common Options](#common-options).

## Range
Same as [Common Options](#common-options).

## Search
Same as [Common Options](#common-options).

## Textarea
Same as [Common Options](#common-options).

## Text
Same as [Common Options](#common-options).

## Time
Same as [Common Options](#common-options).

## Url
Same as [Common Options](#common-options).

## Checkboxes
<dl>
<dt>horizontal (true)</dt>
<dd>The layout of the checkboxes. Set to <code>false</code> if you want to put the checkboxes vertically.</dd>
<dt>options ([])</dt>
<dd>An array of options. <br>For example <code>['Red'=> '#f00', 'Green'=> '#0f0', 'Blue'=> '#00f']</code>.</dd>
</dl>

## Checkbox
<dl>
<dt>text (string)</dt>
<dd>The text of the checkbox.</dd>
<dt>value (string)</dt>
<dd>The value of the checkbox.</dd>
</dl>

## Radios
Same as [Checkboxes](#checkboxes).

## Select
Same as [Checkboxes](#checkboxes).

## File
<dl>
<dt>maxFileSize (string)</dt>
<dd>The maximum file size (byte) of the file.</dd>
<dt>isJson (bool)</dt>
<dd>Set to <code>true</code> if you are going to upload a json file.<br>Default value is <code>false</code>.</dd>
</dl>

## Media
<dl>
<dt>button_text (string)</dt>
<dd>The text of the media uploader button.</dd>
<dt>media_uploader_title (string)</dt>
<dd>The title of media uploader.</dd>
</dl>









