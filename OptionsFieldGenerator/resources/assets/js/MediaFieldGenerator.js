jQuery(function ($) {

    var MediaFieldModel = Backbone.Model.extend({

        defaults: {
            media: {
                id: 0,
                url: ''
            },
            media_frame: null
        },

        select_media: function () {
            var media       = this.get('media'),
                media_frame = this.get('media_frame');

            if (media.id == 0) {
                return this;
            }

            // make the media file selected
            var selection  = media_frame.state().get('selection'),
                attachment = wp.media.attachment(media.id);

            attachment.fetch();
            selection.add(attachment);
        },

        set_media: function (media) {
            this.set({media: media});
            this.trigger('set_media');
        },

        remove_media: function () {
            this.set({media: this.defaults.media});
            this.trigger('remove_media');
        }
    });

    function MediaFieldView(element) {

        var $field      = $(element),
            $input      = $field.find('.laraish-media-field__input'),
            $img        = $field.find('.laraish-media-field__img'),
            $media      = $field.find('.laraish-media-field__media'),
            $filename   = $field.find('.laraish-media-field__filename'),
            media_frame = wp.media(JSON.parse($field.find('.laraish-media-uploader-options').val())),
            model       = new MediaFieldModel({
                media: {
                    id: $input.val(),
                    url: $img.attr('src')
                },
                media_frame: media_frame
            });


        /*------------------------------------*\
         # bind media frame events
         \*------------------------------------*/

        media_frame.on('select', function () {
            // Get media attachment details from the frame state
            var media = media_frame.state().get('selection').first().toJSON();

            model.set_media(media);
            // console.log(media);
        });

        media_frame.on('open', function () {
            model.select_media();
        });


        /*------------------------------------*\
         # bind view events
         \*------------------------------------*/

        // add or edit media
        $field.find('.laraish-add-media-button, .laraish-media-field__edit').on('click', function () {
            media_frame.open();
        });

        // remove media
        $field.find('.laraish-media-field__remove').on('click', function () {
            model.remove_media();
        });

        /*------------------------------------*\
         # bind model events
         \*------------------------------------*/

        model.on('remove_media', function () {
            $field.removeClass('laraish-has-value');
        });

        model.on('set_media', function () {
            $field.addClass('laraish-has-value');
        });

        model.on('change', function () {
            var media = model.get('media');

            $img.attr('src', media.type == 'image' ? media.url : media.icon);
            $media.attr('class', 'laraish-media-field__media ' + media.type);
            $filename.text(media.filename);
            $input.val(media['id']);
        });
    }


    // create views
    $('.js-laraish-media-field').each(function () {
        MediaFieldView(this);
    });

});