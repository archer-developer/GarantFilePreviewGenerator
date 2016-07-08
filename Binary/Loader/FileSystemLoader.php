<?php

namespace Garant\FilePreviewGeneratorBundle\Binary\Loader;

use Liip\ImagineBundle\Binary\Loader\FileSystemLoader as BaseLoader;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Model\FileBinary;

class FileSystemLoader extends BaseLoader
{
    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        if (false !== strpos($path, '../')) {
            throw new NotLoadableException(sprintf("Source image was searched with '%s' out side of the defined root path", $path));
        }

        // Windows absolute path
        if(preg_match('/^[a-zA-Z]\:\\\\/', $path)){
            $absolutePath = $path;
        }
        else{
            $absolutePath = $this->rootPath.'/'.ltrim($path, '/');
        }

        if (false == file_exists($absolutePath)) {
            throw new NotLoadableException(sprintf('Source image not found in "%s"', $absolutePath));
        }

        $mimeType = $this->mimeTypeGuesser->guess($absolutePath);

        return new FileBinary(
            $absolutePath,
            $mimeType,
            $this->extensionGuesser->guess($mimeType)
        );
    }
}
