<?php
namespace hamster\models;

use KoKoKo\assert\Assert;

\Yii::import('application.vendor.wideImage.WideImage');

/**
 * The followings are the available columns in table 'image':
 * @property string $id
 * @property string $namespace
 * @property string $name
 * @property string $origName
 */
class UploadedFile extends \CActiveRecord
{
    /**
     * @var \CUploadedFile
     */
    public $file;

    /**
     * @return string the associated database table name
     */
    public function tableName(): string
    {
        return '{{uploads}}';
    }

    public function getAdminThumbUrl(): string
    {
        return $this->getResizedUrl([
            'prefix' => 'th-admin',
            'width' => 320,
            'height' => 320,
        ]);
    }

    public function getResizedUrl(array $options): string
    {
        $prefix = $options['prefix'] ?? null;
        $width = $options['width'] ?? null;
        $height = $options['height'] ?? null;

        Assert::assert($prefix, 'prefix')->string()->lengthGreater(1);

        if ($height) {
            Assert::assert($height, 'height')->positive();
        }

        if ($width) {
            Assert::assert($width, 'width')->positive();
        }

        $fileName = $prefix . '-' . $this->name;
        $thumbPath = $this->createPath($this->namespace, 'resized' . DIRECTORY_SEPARATOR . $fileName);

        if (!file_exists($thumbPath)) {
            $sourceFilePath = $this->createPath($this->namespace, $this->name);
            $this->ensureDir(
                $this->createPath($this->namespace, 'resized')
            );

            \WideImage::load($sourceFilePath)
                ->resize($width, $height)
                ->saveToFile($thumbPath, 75);
        }

        return "/uploads/{$this->namespace}/resized/{$fileName}";
    }

    public function store(\CUploadedFile $file, string $namespace)
    {
        if (strlen($namespace) < 1) {
            throw new \InvalidArgumentException('Namespace must be at least character long');
        }

        $destFileName = uniqid() . '.' . $file->getExtensionName();
        $destPath = $this->createPath($namespace, $destFileName);

        if (file_exists($destPath)) {
            $destFileName = uniqid() . '.' . $file->getExtensionName();
            $destPath = $this->createPath($namespace, $destFileName);
        }

        if (file_exists($destPath)) {
            throw new \CException('Can not save file to the destination. File already exists there.');
        }

        if (!$file->saveAs($destPath)) {
            throw new \CException('Error saving file');
        }

        $oldFilePath = null;

        if ($this->name && $this->namespace) {
            $oldFilePath = $this->createPath($this->namespace, $this->name);
        }

        $this->name = $destFileName;
        $this->namespace = $namespace;
        $this->origName = $file->getName();

        if (!$this->save()) {
            unlink($destPath);
            throw new \CException('Can not store file info in db');
        }

        if ($oldFilePath) {
            if (!unlink($oldFilePath)) {
                \Yii::log(
                    'Can not remove file: ' . dirname($oldFilePath),
                    \CLogger::LEVEL_WARNING
                );
            }
        }
    }

    /**
     * Creates path, where uploaded file must be stored
     *
     * @param  string $relativePath
     * @param  string $namespace
     * @return string
     */
    private function createPath(string $namespace, string $relativePath): string
    {
        $uploadDir = $this->getUploadDir($namespace);

        return $uploadDir . DIRECTORY_SEPARATOR . $relativePath;
    }

    private function getUploadDir(string $namespace): string
    {
        $uploadDir = $this->getUploadsRoot() . DIRECTORY_SEPARATOR . $namespace;

        $this->ensureDir($uploadDir);

        if (!is_writable($uploadDir)) {
            throw new \CException("Нужны права на запись в директорию '$uploadDir'");
        }

        return $uploadDir;
    }

    private function ensureDir(string $path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }

    private function getUploadsRoot(): string
    {
        $uploadsRoot = \Yii::getPathOfAlias('webroot.uploads');

        if (!is_writable($uploadsRoot)) {
            throw new \CException("Нужны права на запись в директорию '$uploadsRoot'");
        }

        return $uploadsRoot;
    }

    protected function beforeDelete(): bool
    {
        if (parent::beforeDelete()) {
            $filePath = $this->createPath($this->namespace, $this->name);

            if (!unlink($filePath)) {
                \Yii::log(
                    'Can not remove file: ' . dirname($filePath),
                    \CLogger::LEVEL_WARNING
                );
            }

            $resizedPath = $this->createPath($this->namespace, 'resized');

            if (is_dir($resizedPath)) {
                $thumbs = scandir($resizedPath);

                foreach ($thumbs as $thumb) {
                    if (strpos($thumb, $this->name) === false) {
                        continue;
                    }

                    unlink($resizedPath . DIRECTORY_SEPARATOR . $thumb);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules(): array
    {
        return [
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels(): array
    {
        return [
            'name' => 'Имя файла',
            'origName' => 'Оригинальное имя файла',
            'namespace' => 'Категория',
        ];
    }

    /**
     * Returns the static model of the specified AR class.
     *
     * @param string $className active record class name.
     * @return Image the static model class
     */
    public static function model($className = __CLASS__): UploadedFile
    {
        return parent::model($className);
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.
        $criteria = new \CDbCriteria;

        $criteria->compare('name', $this->name, true);

        return new \CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }
}
