<?php
namespace Elightwalk\ProductDataSync\Model\External;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;


class CsvReader extends DataObject
{
    const IMPORT_FOLDER_NAME = 'import/tsf/import';

    const IMPORTING_FOLDER_NAME = 'import/tsf/importing';

    const IMPORTED_FOLDER_NAME = 'import/tsf/imported';

    const CHUNK_SIZE = 500;

    const CSV_ENCLOSURE = '"';

    const CSV_DELIMITER = '"';
    
    protected $_filesystem; 

    protected $_directoryList;

    protected $_fileDriver;

    protected $_mediaPath;

    protected $_csvReader;



    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\File\Csv $csvReader
    ){

        $this->_filesystem = $filesystem;
        $this->_directoryList = $directoryList;
        $this->_fileDriver = $fileDriver;
        $this->_csvReader = $csvReader;

        $this->_mediaPath = $this->_filesystem->getDirectoryRead($this->_directoryList::MEDIA)->getAbsolutePath();
    }


    public function isImporting(){

        $isDirectories = $this->_fileDriver->readDirectory($this->_mediaPath.self::IMPORTING_FOLDER_NAME);

        if(count($isDirectories)){
            return true;
        }
        return false;
        
    }

    public function initChunking(){

        $csvFiles = $this->_fileDriver->readDirectory($this->_mediaPath.self::IMPORT_FOLDER_NAME);

        if(count($csvFiles) > 0){

            $filePath = $csvFiles[0];

            $csvHeader = [];

            $pathinfo = pathinfo($filePath);
            $filename = $pathinfo['filename'];
            $extension = $pathinfo['extension'];

            $csvDatas = $this->_csvReader->getData($filePath);

            if(isset($csvDatas[0])){
                $csvHeader = $csvDatas[0];
            }
            
            $jk=0;
            $chunkNumber = 0;
            $chunkData = [];
            $chunkData [] = $csvHeader;
            foreach($csvDatas as $key => $csvData){

                if ($key > 0){
                    
                    if(self::CHUNK_SIZE >= $jk){

                        $chunkData [] = $csvData;
                        
                        if($jk==self::CHUNK_SIZE){

                            $this->_csvReader
                                ->setEnclosure('"')
                                ->setDelimiter(',')
                                ->saveData($this->_mediaPath.self::IMPORTING_FOLDER_NAME.'/'.$filename.'_chunk_'.$chunkNumber.'.'.$extension, $chunkData);

                            $jk=0;
                            $chunkNumber++;
                            $chunkData = [];
                            $chunkData[] = $csvHeader;
                        }else{
                            $jk++;
                        }    
                    }
                }
            }

            unlink($filePath);

        }

    }

    public function readCurrentChunk(){

        $importingDirFiles = $this->readDirectory($this->_mediaPath.self::IMPORTING_FOLDER_NAME);

        return $this->_csvReader->getData(current($importingDirFiles)); 
    }

    public function moveCurrentFile() {
        
        $importingDirFiles = $this->readDirectory($this->_mediaPath.self::IMPORTING_FOLDER_NAME);
        $currentFile = current($importingDirFiles);
        $currentData = $this->_csvReader->getData($currentFile); 
        $this->saveFileInImportedFolder($currentFile, $currentData);
        unlink($currentFile);
        return ;
    }

    private function saveFileInImportedFolder($filePath, $data)
    {
        $pathinfo = pathinfo($filePath);
        $filename = $pathinfo['filename'];
        $extension = $pathinfo['extension'];

        $this->_csvReader
            ->setEnclosure('"')
            ->setDelimiter(',')
            ->saveData($this->_mediaPath.self::IMPORTED_FOLDER_NAME.'/'.$filename.'.'.$extension, $data);

        return ;
    }

    /**
     * Read directory
     *
     * @param string $path
     * @return string[]
     * @throws FileSystemException
     */
    protected function readDirectory($path)
    {
        try {
            $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
            $iterator = new \FilesystemIterator($path, $flags);
            $result = [];
            /** @var \FilesystemIterator $file */
            foreach ($iterator as $file) {
                $result[] = $file->getPathname();
            }
            sort($result, SORT_NATURAL | SORT_FLAG_CASE);
            return $result;
        } catch (\Exception $e) {
            throw new FileSystemException(new \Magento\Framework\Phrase($e->getMessage()), $e);
        }
    }


}
