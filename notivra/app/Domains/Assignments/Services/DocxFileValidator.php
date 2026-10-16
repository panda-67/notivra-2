<?php

namespace App\Domains\Assignments\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class DocxFileValidator
{
    public function validate(UploadedFile $file): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new \RuntimeException(
                'PHP extension ext-zip is required to validate DOCX files.'
            );
        }

        $zip = new ZipArchive();

        if ($zip->open($file->getRealPath()) !== true) {
            $this->reject();
        }

        try {
            $requiredEntries = [
                '[Content_Types].xml',
                '_rels/.rels',
                'word/document.xml',
            ];

            foreach ($requiredEntries as $entry) {
                if ($zip->locateName($entry) === false) {
                    $this->reject();
                }
            }

            $documentXml = $zip->getFromName('word/document.xml');

            if (! is_string($documentXml) || trim($documentXml) === '') {
                $this->reject();
            }

            $previousSetting = libxml_use_internal_errors(true);

            try {
                $document = new \DOMDocument();

                $validXml = $document->loadXML(
                    $documentXml,
                    LIBXML_NONET
                );

                if (
                    ! $validXml
                    || $document->documentElement?->localName !== 'document'
                    || $document->documentElement?->namespaceURI
                    !== 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'
                ) {
                    $this->reject();
                }
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previousSetting);
            }
        } finally {
            $zip->close();
        }
    }

    private function reject(): never
    {
        throw ValidationException::withMessages([
            'file' => 'File harus berupa dokumen DOCX yang valid.',
        ]);
    }
}
