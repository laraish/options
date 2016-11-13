<div class="js-laraish-file-field laraish-media-field {{ $has_value_class }}">
    <div class="laraish-show-if-value--inline-block">
        <div class="laraish-media-field__media application">
            <img class="laraish-media-field__img" src="{{ $media_img_url }}">
            <ul class="laraish-media-field__controller">
                <li><a class="laraish-media-field__remove dashicons-before dashicons-trash"></a></li>
            </ul>
            <div class="laraish-media-field__filename">{{ $filename }}</div>
        </div>
    </div>

    <div class="laraish-hide-if-value">
        <input type="file" name="{{ $field_name }}" class="laraish-file-field__input">
    </div>

    {{ $description }}
</div>