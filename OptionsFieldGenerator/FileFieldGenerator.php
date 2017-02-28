<?php

namespace Laraish\Options\OptionsFieldGenerator;

class FileFieldGenerator extends BaseFieldGenerator
{
    /**
     * The default configs.
     * @var array
     */
    protected $defaultConfigs = [
        'attributes'   => [],
        'maxFileSize'  => null,
        'isJson'       => false,
        'assoc'        => false,
        'defaultValue' => []
    ];

    static protected $scripts = ['js/FileFieldGenerator.js'];

    static protected $styles = ['css/sharedStyle.css', 'css/media.css'];

    static protected $template = 'FileFieldGenerator.blade.php';

    /**
     * Generate the file field.
     * Note that the file field will only accepts a text base file,
     * and will save the uploaded file's content as the value of the field.
     *
     * @return string
     */
    final public function generate()
    {
        $data = [
            'field_name'      => $this->fieldName,
            'has_value_class' => $this->fieldValue ? 'laraish-has-value' : '',
            'media_img_url'   => home_url('/wp-includes/images/media/document.png'),
            'filename'        => $this->fieldValue ? $this->fieldValue['filename'] : '',
            'description'     => $this->generateDescription(),
        ];

        return $this->renderTemplate($data);
    }

    /**
     * A callback function that sanitizes the option's value.
     *
     * @param mixed $filesInfo
     *
     * @return mixed
     */
    public function sanitize($filesInfo)
    {
        $defaultValue = $this->defaultConfigs['defaultValue'];
        if ($filesInfo === 'laraish::removeFileField' OR $filesInfo == []) {
            return $defaultValue;
        }

        // If the $filesInfo has been sanitized, return it.
        if (array_key_exists('content', $filesInfo)) {
            return $filesInfo;
        }

        $fieldId     = $this->fieldId;
        $maxFileSize = $this->config('maxFileSize');
        $oldValue    = $this->fieldValue;

        $error    = $filesInfo['error'][$fieldId];
        $size     = $filesInfo['size'][$fieldId];
        $tempFile = $filesInfo['tmp_name'][$fieldId];
        $filename = $filesInfo['name'][$fieldId];
        $mime     = $filesInfo['type'][$fieldId];


        /*------------------------------------*\
            # check if has error
        \*------------------------------------*/

        $hasError = $error !== 0;
        if ($hasError) {
            // if no file was uploaded restore the old value and return.
            if ($error === UPLOAD_ERR_NO_FILE) {
                // restore to old value
                return $oldValue;
            }

            switch ($error) {
                case UPLOAD_ERR_INI_SIZE:
                    $errorMessage = "The uploaded file `{$this->config('title')}` exceeds the upload_max_filesize directive in php.ini.";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $errorMessage = "The uploaded file `{$this->config('title')}` exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errorMessage = "The uploaded file `{$this->config('title')}` was only partially uploaded.";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $errorMessage = "Failed to upload the file `{$this->config('title')}`. Missing a upload temporary folder.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $errorMessage = "Failed to write the file `{$this->config('title')}` to disk.";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $errorMessage = "Failed to upload the file `{$this->config('title')}`. A PHP extension stopped the file upload.";
                    break;
                default:
                    $errorMessage = 'Failed to upload file';
            }

            // add error message
            add_settings_error($fieldId, 'file_upload_failed', $errorMessage);

            // restore to old value
            return $oldValue;
        }


        /*-----------------------------------------*\
            # check if has exceeded max file size
        \*-----------------------------------------*/

        $exceedsMaxFileSize = $maxFileSize ? $size > $maxFileSize : false;
        if ($exceedsMaxFileSize) {
            $errorMessage = "The uploaded file `{$this->config('title')}` exceeds the maximum file size ({$maxFileSize} bytes)";
            add_settings_error($fieldId, 'file_upload_failed', $errorMessage);

            // restore to old value
            return $oldValue;
        }


        /*-------------------------------------------*\
            # check if the file is a text base file.
        \*-------------------------------------------*/

        $finfo         = new \finfo(FILEINFO_MIME);
        $type          = $finfo->file($tempFile);
        $isNotTextFile = ! preg_match('@^text/@', $type);
        if ($isNotTextFile) {
            $errorMessage = "The format of uploaded file `{$this->config('title')}` is not acceptable.";
            add_settings_error($fieldId, 'file_upload_failed', $errorMessage);

            // restore to old value
            return $oldValue;
        }


        /*------------------------------------*\
            # finally we can save our data.
        \*------------------------------------*/

        $data = [
            'filename' => $filename,
            'mime'     => $mime,
            'size'     => $size
        ];

        $content = $data['content'] = file_get_contents($tempFile);
        if ($this->config('isJson')) {
            $json = json_decode($content, $this->config('assoc') === true ? true : false);
            if ( ! $json) {
                $errorMessage = "The uploaded file `{$this->config('title')}` should be a valid json file.";
                add_settings_error($fieldId, 'file_upload_failed', $errorMessage);

                // restore to old value
                return $oldValue;
            }

            $data['content'] = $json;
        }

        return $data;
    }

    /**
     * An option could be potentially set to a value before the field saving its value to the database for the first time.
     * This method will be automatically called with the current value of the field if the field has a value.
     * If the value of the current option is a valid value for the field, return `true`, if it's not, return `false`.
     * `$this->validateWithErrorMessage` also use this method to validate the field value.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function validateFieldValue($value)
    {
        return isset($value['content']);
    }

}
