<?php 

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @author ZONCHELLO Sébastien
 * Cette classe permet de télécharger un fichier
 */
class FileUploader
{
    private $targetDirectory;
    private $slugger;

    public function __construct($targetDirectory, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file) {

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        $fileMimeType = $file->getClientMimeType();
        $fileSize = $file->getSize();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {}

        return [
            "fileName" => $fileName,
            "fileMimeType" => $fileMimeType,
            "fileSize" => $fileSize
        ];
        
    }

    public function setTargetDirectory($targetDirectory) {
        $this->targetDirectory = $targetDirectory;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

}