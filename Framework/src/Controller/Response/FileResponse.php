<?php

namespace Framework\Controller\Response;

/**
 * Klass för en respons där servern ska skicka en fil till klienten.
 */
class FileResponse {

    /**
     * @var string Filens sökväg.
     */
    public string $filePath;

    /**
     * @param string $fileName Filens sökväg.
     */
    function __construct(string $fileName){
        $this->filePath = $fileName;
    }

}