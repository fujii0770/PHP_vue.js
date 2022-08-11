<?php namespace App\Utils;

use Howtomakeaturn\PDFInfo\PDFInfo;
use Howtomakeaturn\PDFInfo\Exceptions\OpenPDFException;
use Howtomakeaturn\PDFInfo\Exceptions\OpenOutputException;
use Howtomakeaturn\PDFInfo\Exceptions\PDFPermissionException;
use Howtomakeaturn\PDFInfo\Exceptions\OtherException;
use Howtomakeaturn\PDFInfo\Exceptions\CommandNotFoundException;
use Illuminate\Support\Facades\Log;

/**
 * Created by PhpStorm.
 * User: hp
 * Date: 5/27/2019
 * Time: 12:12 PM
 */

class PDFUtils extends PDFInfo
{
    const NUMBER_PAGE_LOAD = 3;
    const MAX_EXAMINE_PAGES = 9999;

    public function __construct($file)
    {
        $this->file = $file;

        $this->loadOutputForPages();

        $this->parseOutput();
    }

    private function loadOutputForPages()
    {
        $cmd = escapeshellarg($this->getBinary()); // escapeshellarg to work with Windows paths with spaces.

        $file = escapeshellarg($this->file);
        // Parse entire output
        // Surround with double quotes if file name has spaces

        exec("$cmd -l ". self::MAX_EXAMINE_PAGES . " -f 1 $file", $output, $returnVar);
        if ( $returnVar === 1 ){
            throw new OpenPDFException();
        } else if ( $returnVar === 2 ){
            throw new OpenOutputException();
        } else if ( $returnVar === 3 ){
            throw new PDFPermissionException();
        } else if ( $returnVar === 99 ){
            throw new OtherException();
        } else if ( $returnVar === 127 ){
            throw new CommandNotFoundException();
        }
        $this->output = $output;
    }

    private function parseOutput()
    {
        $output = implode("\n", $this->output);
        preg_match_all("/^([A-Za-z0-9 ]+):\s*(.+)$/im", $output, $matches, PREG_SET_ORDER);

        // Iterate through lines
        $map = [];
        foreach($matches as $match) {
            $key = $match[1];
            if (isset($map[$key])) {
                Log::warning("duplicate key (ignored): ". $key);
            } else {
                $map[$key] = $match[2];
            }
        }

        $this->title = $map['Title'] ?? null;
        $this->author = $map['Author'] ?? null;
        $this->creator = $map['Creator'] ?? null;
        $this->producer = $map['Producer'] ?? null;
        $this->creationDate = $map['CreationDate'] ?? null;
        $this->modDate = $map['ModDate'] ?? null;
        $this->tagged = $map['Tagged'] ?? null;
        $this->form = $map['Form'] ?? null;
        $this->pages = $map['Pages'] ?? null; // num of pages
        $this->encrypted = $map['Encrypted'] ?? null;
        $this->fileSize = $map['File size'] ?? null;
        $this->optimized = $map['Optimized'] ?? null;
        $this->PDFVersion = $map['PDF version'] ?? null;

        // pdfinfo の出力結果そのまま
        // 表示を想定した情報は getShownPagesInfo で得られる
        $this->pagesInfo = [];

        for ($i = 1; $i <= min((int)$this->pages, self::MAX_EXAMINE_PAGES); $i++) {
            $strPage = str_pad($i, 4, " ", STR_PAD_LEFT);
            $this->pagesInfo[] = [
                "size" => $map["Page $strPage size"] ?? null,
                "rot" => $map["Page $strPage rot"] ?? null,
            ];
        }
    }

    /**
     * ページの回転情報も考慮した（表示を想定した）ページサイズ情報を返す
     */
    public function getShownPagesInfo() {
        return array_map(function ($pageInfo) {
            $rot = $pageInfo["rot"];
            $needsSwapSize = $rot == "90" || $rot == "270";

            preg_match('/([0-9\.]+) x ([0-9\.]+)/', $pageInfo["size"], $matches);

            return $needsSwapSize ? [
                // 表示時は 幅・高さ が入れ替わるため
                'width_pt' => (float)$matches[2],
                'height_pt' => (float)$matches[1],
            ] : [
                'width_pt' => (float)$matches[1],
                'height_pt' => (float)$matches[2],
            ];
        }, $this->pagesInfo);
    }
}