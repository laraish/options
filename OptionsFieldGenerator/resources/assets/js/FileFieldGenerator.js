jQuery(function ($) {

    function get_substitution_input(name) {
        return $('<input type="hidden" value="laraish::removeFileField">').attr('name', name);
    }


    $('.js-laraish-file-field').each(function () {
        var $field              = $(this),
            $input              = $field.find('.laraish-file-field__input'),
            $filename           = $field.find('.laraish-media-field__filename'),
            $substitution_input = $();

        $field.find('.laraish-media-field__remove').on('click', function () {
            $field.removeClass('laraish-has-value');
            $input.after($substitution_input = get_substitution_input($input.attr('name')));
        });

        $input.on('change', function () {
            $substitution_input.remove();
            $filename.text(this.files[0]['name']);
            $field.addClass('laraish-has-value');
        });

    });
});