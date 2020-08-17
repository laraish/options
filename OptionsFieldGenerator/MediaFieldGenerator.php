<?php

namespace Laraish\Options\OptionsFieldGenerator;

class MediaFieldGenerator extends BaseFieldGenerator
{
    /**
     * The default configs.
     * @var array
     */
    protected $defaultConfigs = [
        'button_text' => null,
        'media_uploader_title' => null,
        'defaultValue' => [],
    ];

    protected static $scripts = ['js/MediaFieldGenerator.js'];

    protected static $styles = ['css/sharedStyle.css', 'css/media.css'];

    protected static $template = 'MediaFieldGenerator.blade.php';

    /**
     * Generate the field markup.
     *
     * @return string
     */
    final public function generate()
    {
        $mediaInfo = $this->fieldValue;
        $fieldName = $this->fieldName;

        $data = [
            'has_value_class' => '',
            'media_img_url' => '',
            'media_type' => '',
            'filename' => '',
            'attachment_id' => 0,
            'description' => $this->generateDescription(),
            'button_text' => $this->config('button_text') ?: __('Add Media'),
            'field_name' => $fieldName,
            'media_uploader_options' => esc_attr(
                json_encode([
                    'multiple' => false,
                    'title' => $this->config('media_uploader_title'),
                ])
            ),
        ];

        if ($mediaInfo) {
            $data['has_value_class'] = 'laraish-has-value';
            $data['media_img_url'] = $mediaInfo['type'] == 'image' ? $mediaInfo['url'] : $mediaInfo['icon'];
            $data['attachment_id'] = $mediaInfo['id'];
            $data['media_type'] = $mediaInfo['type'];
            $data['filename'] = $mediaInfo['filename'];
        }

        return $this->renderTemplate($data);
    }

    protected function validateFieldValue($value)
    {
        return isset($value['url']);
    }

    /**
     * A callback function that sanitizes the option's value.
     *
     * @param array|int|null $attachmentId
     *
     * @return mixed
     */
    public function sanitize($attachmentId)
    {
        if (is_string($attachmentId)) {
            $mediaMetaData = wp_prepare_attachment_for_js($attachmentId);

            return $mediaMetaData;
        }

        return $attachmentId;
    }
}
