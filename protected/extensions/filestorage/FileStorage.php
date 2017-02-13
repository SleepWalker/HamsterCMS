<?php
namespace ext\filestorage;

use KoKoKo\assert\Assert;

class FileStorage extends \CApplicationComponent
{
    public $storePath = 'application.uploads';

    /**
     * @param  \CUploadedFile $file      the file to store
     * @param  string         $storageId storage id to place file to
     *
     * @return File
     */
    public function store(\CUploadedFile $file, $storageId = '')
    {
        Assert::assert($storageId, 'storageId')->match('/[a-zA-z0-9_-\/]/');

        $uploadPath = $this->getUploadPath();

        $destinationPath = $this->getDestinationPath();

        $directoryPath = $this->join([
            $uploadPath,
            $storageId,
            $destinationPath,
        ]);
        if (!file_exists($directoryPath) && !is_dir($directoryPath)) {
            throw new FileStorageException("$directoryPath must be a directory");
        }

        if (!is_dir($directoryPath)) {
            if (!\CFileHelper::createDirectory($directoryPath, null, true)) {
                throw new FileStorageException("Can not create directory $directoryPath");
            }
        }
        $path = $this->join([
            $directoryPath,
            $file->getName(),
        ]);

        if (file_exists($path)) {
            // TODO: rename
        }

        if (!$path->saveAs($path)) {
            throw new FileStorageException("Can not save to $path");
        }

        $file = new File();
        [
            'storage_id',
            'user_id',
            'path',
            'name',
            'orig_name',
            'mime_type',
            'date_created',
        ];
        $file->save();

        return $file;
    }

    public function get(\CActiveRecord $file, FileStorageStrategy $strategy)
    {
        $file->setStrategy($strategy);
    }

    public function getUploadPath()
    {
        $uploadPath = \Yii::app()->getPathOfAlias($this->storePath);

        if (!is_writable($uploadPath)) {
            throw new FileStorageException("$uploadPath is not writeable or does not exists.");
        }

        return $uploadPath;
    }

    private function getDestinationPath()
    {
        return $this->join([
            date('Y'),
            date('m'),
            date('d'),
        ]);
    }

    private function join(array $parts = [])
    {
        return implode('/', $parts);
    }
}

class File extends \CActiveRecord
{
    private $strategy;

    public function setStrategy(FileStorageStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    public function getUrl()
    {
        return $this->strategy->getUrl();
    }
}


class FileStorageStrategy
{
    public function getUrl()
    {
        \Yii::app()->createAbsoluteUrl(
            '/uploads'
            . '/' . $this->storage_id
            . '/' . $this->path
            . '/' . $this->name
        );
    }
}
