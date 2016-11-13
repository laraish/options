<div class="js-laraish-media-field laraish-media-field {{ $has_value_class }}">
    <div class="laraish-show-if-value--inline-block">
        <div class="laraish-media-field__media {{ $media_type }}">
            <img class="laraish-media-field__img" src="{{ $media_img_url }}">
            <ul class="laraish-media-field__controller">
                <li><a class="laraish-media-field__edit dashicons-before dashicons-edit"></a></li>
                <li><a class="laraish-media-field__remove dashicons-before dashicons-trash"></a></li>
            </ul>
            <div class="laraish-media-field__filename">{{ $filename }}</div>
        </div>
    </div>

    <div class="laraish-hide-if-value">
        <input type="button" value="{{ $button_text }}" class="button laraish-add-media-button">
    </div>

    {{ $description }}

    <input type="hidden" class="laraish-media-field__input" name="{{ $field_name }}" value="{{ $attachment_id }}">
    <input type="hidden" value="{{ $media_uploader_options }}" class="laraish-media-uploader-options">
</div>