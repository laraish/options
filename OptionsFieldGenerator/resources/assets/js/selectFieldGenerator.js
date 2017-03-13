jQuery(function () {
    var $                = jQuery,
        $selects         = $('.laraish-select'),
        availableOptions = [
            'delimiter',
            'create',
            'createOnBlur',
            'highlight',
            'persist',
            'openOnFocus',
            'maxOptions',
            'maxItems',
            'hideSelected',
            'closeAfterSelect',
            'allowEmptyOption',
            'scrollDuration',
            'loadThrottle',
            'loadingClass',
            'placeholder',
            'preload',
            'dropdownParent',
            'addPrecedence',
            'selectOnTab',
            'diacritics',
        ],
        defaultOptions   = {
            hideSelected: true
        };


    $selects.each(function () {
        var $this   = $(this),
            options = {};

        availableOptions.forEach(function (optionName) {
            var dataName = camelCaseToDashed(optionName),
                value    = $this.data(dataName);
            if (value !== undefined) {
                options[optionName] = value;
            }
        });

        $this.selectize(_.extend(_.clone(defaultOptions), options));
    });


    function camelCaseToDashed(str) {
        return str.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
    }

});