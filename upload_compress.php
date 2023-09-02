<?php
$msg = [];
class UploadCompress
{

    protected $glob;

    public $method;
    public $image;
    public $new_image;
    public $width;
    public $extension;
    public $height;
    public $quality;
    public $errors;

    function __construct()
    {
        global $msg;
        $this->errors = $msg;
    }

    function UploadAndCompress($method = null, $image = null, $new_image = null, $width = null, $height = null, $quality = 100, $extension = null)
    {
        $this->method = $method;
        $this->image = $image;
        $this->new_image = $new_image;
        $this->width = $width;
        $this->height = $height;
        $this->quality = $quality;
        $this->extension = $extension;

        if (!array_key_exists('errors', $this->errors) || !is_array($this->errors['errors'])) {
            $this->errors['errors'] = array();
        }

        if (!in_array($method, array('force', 'max', 'crop'))) {
            $this->errors['errors'][] = 'Invalid method selected.';
        }

        if (!$this->image) {
            $this->errors['errors'][] = 'No source image location specified.';
        } else {
            $extension = $this->extension;
            if (!in_array($extension, array('.jpg', '.jpeg', '.png', '.gif', '.bmp'))) {
                $this->errors['errors'][] = 'Invalid source file extension!';
            }
        }

        if (!$this->new_image) {
            $this->errors['errors'][] = 'No destination image location specified.';
        } else {
            $new_extension = strtolower(substr($this->new_image, strrpos($this->new_image, '.')));
            if (!in_array($new_extension, array('.jpg', '.jpeg', '.png', '.gif', '.bmp'))) {
                $this->errors['errors'][] = 'Invalid destination file extension!';
            }
        }

        $width = abs(intval($this->width));
        if (!$width) {
            $this->errors['errors'][] = 'No width specified!';
        }

        $height = abs(intval($this->height));
        if (!$height) {
            $this->errors['errors'][] = 'No height specified!';
        }

        if (count($this->errors['errors']) > 0) {
            return $this->echo_errors();
        }

        if (!$image) {
            $this->errors['errors'][] = 'Image could not be generated!';
        } else {
            $current_width = imagesx($image);
            $current_height = imagesy($image);
            if ((!$current_width) || (!$current_height)) {
                $this->errors['errors'][] = 'Generated image has invalid dimensions!';
            }
        }
        if (count($this->errors['errors']) > 0) {
            @imagedestroy($image);
            return $this->echo_errors();
        }

        if ($this->method == 'force') {
            $new_image = $this->resizeImageForce(image: $image, width: $width, height: $height);
        } elseif ($this->method == 'max') {
            $new_image = $this->resizeImageMax(image: $image, max_width: $width, max_height: $height);
        } elseif ($this->method == 'crop') {
            $new_image = $this->resizeImageCrop(image: $image, width: $width, height: $height);
        }

        if ((!$new_image) && (count($this->errors['errors']) == 0)) {
            $this->errors['errors'][] = 'New image could not be generated!';
        }

        if (count($this->errors['errors']) > 0) {
            @imagedestroy($image);
            return $this->echo_errors();
        }

        $save_error = false;
        if (in_array($extension, array('.jpg', '.jpeg'))) {
            imagejpeg($new_image, $this->new_image, $this->quality) or ($save_error = true);
        } elseif ($extension == '.png') {
            imagepng($new_image, $this->new_image, $this->quality) or ($save_error = true);
        } elseif ($extension == '.gif') {
            imagegif($new_image, $this->new_image, $this->quality) or ($save_error = true);
        } elseif ($extension == '.bmp') {
            imagewbmp($new_image, $this->new_image, $this->quality) or ($save_error = true);
        }
        if ($save_error) {
            $this->errors['errors'][] = 'New image could not be saved!';
        }
        if (count($this->errors['errors']) > 0) {
            @imagedestroy($image);
            @imagedestroy($new_image);
            return $this->echo_errors();
        }

        imagedestroy($image);
        imagedestroy($new_image);

        return true;
    }

    function resizeImageMax($image, $max_width, $max_height)
    {
        if (!array_key_exists('errors', $this->errors) || !is_array($this->errors['errors'])) {
            $this->errors['errors'] = array();
        }
        $w = imagesx($image); // current width
        $h = imagesy($image); // current height
        if ((!$w) || (!$h)) {
            $this->errors['errors'][] = 'Image could not be resized because it was not a valid image.';
            return false;
        }

        if (($w <= $max_width) && ($h <= $max_height)) {
            return $image;
        } // no resizing needed

        // try max width first...
        $ratio = $max_width / $w;
        $new_w = $max_width;
        $new_h = $h * $ratio;

        // if that didn't work
        if ($new_h > $max_height) {
            $ratio = $max_height / $h;
            $new_h = $max_height;
            $new_w = $w * $ratio;
        }

        // resize the image
        $new_w = round($new_w);
        $new_h = round($new_h);
        $new_image = imagecreatetruecolor($new_w, $new_h);
        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_w, $new_h, $w, $h);

        return $new_image;
    }

    function resizeImageCrop($image, $width, $height)
    {
        if (!array_key_exists('errors', $this->errors) || !is_array($this->errors['errors'])) {
            $this->errors['errors'] = array();
        }

        $w = @imagesx($image); // current width
        $h = @imagesy($image); // current height
        if ((!$w) || (!$h)) {
            $this->errors['errors'][] = 'Image could not be resized because it was not a valid image.';
            return false;
        }
        if (($w == round($width)) && ($h == round($height))) {
            return $image;
        } // no resizing needed

        // try max width first...
        $ratio = $width / $w;
        $new_w = $width;
        $new_h = $h * $ratio;

        // if that created an image smaller than what we wanted, try the other way
        if ($new_h < $height) {
            $ratio = $height / $h;
            $new_h = $height;
            $new_w = $w * $ratio;
        }

        // resize the image
        $new_w = round($new_w);
        $new_h = round($new_h);
        $image2 = imagecreatetruecolor($new_w, $new_h);
        imagecopyresampled($image2, $image, 0, 0, 0, 0, $new_w, $new_h, $w, $h);

        // check to see if cropping needs to happen
        if (($new_h != $height) || ($new_w != $width)) {
            $image3 = imagecreatetruecolor($width, $height);
            if ($new_h > $height) { //crop vertically
                $extra = $new_h - $height;
                $x = 0; // source x
                $y = round($extra / 2); // source y
                imagecopyresampled($image3, $image2, 0, 0, $x, $y, $width, $height, $width, $height);
            } else {
                $extra = $new_w - $width;
                $x = round($extra / 2); // source x
                $y = 0; // source y
                imagecopyresampled($image3, $image2, 0, 0, $x, $y, $width, $height, $width, $height);
            }
            imagedestroy($image2);
            return $image3;
        } else {
            return $image2;
        }
    }

    function resizeImageForce($image, $width, $height)
    {
        if (!array_key_exists('errors', $this->errors) || !is_array($this->errors['errors'])) {
            $this->errors['errors'] = array();
        }

        $w = @imagesx($image); // current width
        $h = @imagesy($image); // current height
        if ((!$w) || (!$h)) {
            $this->errors['errors'][] = 'Image could not be resized because it was not a valid image.';
            return false;
        }
        if (($w == $width) && ($h == $height)) {
            return $image;
        } // no resizing needed

        $image2 = imagecreatetruecolor($width, $height);
        imagecopyresampled($image2, $image, 0, 0, 0, 0, $width, $height, $w, $h);

        return $image2;
    }

    function echo_errors()
    {
        if ((!array_key_exists('errors', $this->errors)) || (!is_array($this->errors['errors']))) {
            $this->errors['errors'] = array();
        }
        return $this->errors;
    }
}
