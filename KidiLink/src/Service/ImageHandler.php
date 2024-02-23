<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class ImageHandler
{
    private $params;
    private $filesystem;

    public function __construct(ParameterBagInterface $params, Filesystem $filesystem)
    {
        $this->params = $params;
        $this->filesystem = $filesystem;
    }
    //pour avoir le student www data
    // sudo chown -R $USER:www-data .
    /**
     * Processes and saves the base64-encoded image content.
     *
     * @param string $base64Content Base64-encoded image content.
     * @param string $type           Type of the image.
     *
     * @return string The generated file name.
     */
    public function processUploadBase64($base64Content, $type)
    {
        $base64 = $this->extractBase64String($base64Content);

        // TODO: Handle errors, etc.
        $fileName = $this->saveFileBase64($base64, $type);

        return $fileName;
    }

    /**
     * Saves the base64-decoded image content to a file.
     *
     * @param array  $base64DataAndFileType Array containing base64-decoded image content and file type.
     * @param string $type                  Type of the image.
     *
     * @return string The generated file name.
     */
    public function saveFileBase64($base64DataAndFileType, $type)
    {
        $uploadsDirectoryPath = $this->params->get('uploads_directory');

        $data = base64_decode($base64DataAndFileType[0]);
        $fileName = uniqid($type) . ($base64DataAndFileType[1] ? $base64DataAndFileType[1] : "");
        $filePath = $uploadsDirectoryPath . '/' . $fileName;

        file_put_contents($filePath, $data);

        // TODO: Add condition to return filePath only if successful
        return $fileName;
    }

    /**
     * Extracts the base64-encoded string and file type from the given base64 content.
     *
     * @param string $base64Content Base64-encoded image content.
     *
     * @return array An array containing base64-encoded string and file extension.
     */
    public function extractBase64String(string $base64Content)
    {
        $array = [];

        $data = explode(';base64,', $base64Content);
        $array[] = $data[1];

        $fileTypeAux = explode("image/", $data[0]);
        $fileType = $fileTypeAux[1];

        if ($fileType === "jpeg") {
            $array[] = ".jpg";
        }
        if ($fileType === "png") {
            $array[] = ".png";
        }

        return $array;
    }

    /**
     * Deletes the image file.
     *
     * @param string $fileName The name of the image file to delete.
     *
     * @return bool True if the file was successfully deleted, false otherwise.
     */
    public function deleteImage($fileName)
    {
        $uploadsDirectoryPath = $this->params->get('uploads_directory');

        $filePath = $uploadsDirectoryPath . '/' . $fileName;

        $deleted = $this->filesystem->remove($filePath);

        return $deleted;
    }
}