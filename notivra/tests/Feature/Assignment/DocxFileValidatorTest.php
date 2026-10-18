<?php

namespace Tests\Feature\Assignment;

use App\Domains\Assignments\Services\DocxFileValidator;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use ZipArchive;

class DocxFileValidatorTest extends TestCase
{
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_valid_docx_structure_is_accepted(): void
    {
        $file = $this->createDocx();

        $validator = new DocxFileValidator();

        $validator->validate($file);

        $this->assertTrue(true);
    }

    public function test_invalid_zip_content_is_rejected(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'invalid.docx',
            'This is not a ZIP archive.',
        );

        $validator = new DocxFileValidator();

        try {
            $validator->validate($file);
            $this->fail('File DOCX tidak valid seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('file', $exception->errors());
        }
    }

    public function test_docx_missing_required_entry_is_rejected(): void
    {
        $file = $this->createDocx([
            '[Content_Types].xml' => '<Types/>',
            '_rels/.rels' => '<Relationships/>',
        ]);

        $validator = new DocxFileValidator();

        try {
            $validator->validate($file);
            $this->fail('DOCX tanpa word/document.xml seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('file', $exception->errors());
        }
    }

    public function test_invalid_document_xml_is_rejected(): void
    {
        $file = $this->createDocx([
            '[Content_Types].xml' => '<Types/>',
            '_rels/.rels' => '<Relationships/>',
            'word/document.xml' => '<not-a-document/>',
        ]);

        $validator = new DocxFileValidator();

        try {
            $validator->validate($file);
            $this->fail('XML dokumen yang salah seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('file', $exception->errors());
        }
    }


    private function createDocx(?array $entries = null): UploadedFile
    {
        if (! class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP ext-zip tidak tersedia.');
        }

        $path = tempnam(sys_get_temp_dir(), 'docx-test-');

        if ($path === false) {
            $this->fail('Tidak dapat membuat file sementara.');
        }

        $this->temporaryFiles[] = $path;

        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            $this->fail('Tidak dapat membuat arsip DOCX sementara.');
        }

        $defaultEntries = [
            '[Content_Types].xml' => '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"/>',
            '_rels/.rels' => '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>',
            'word/document.xml' => '<?xml version="1.0"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body/></w:document>',
        ];

        foreach ($entries ?? $defaultEntries as $name => $content) {
            $zip->addFromString($name, $content);
        }

        $zip->close();

        return new UploadedFile(
            $path,
            'report.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true,
        );
    }
}
