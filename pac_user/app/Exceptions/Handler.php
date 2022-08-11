<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Howtomakeaturn\PDFInfo\Exceptions\OpenPDFException;
use Howtomakeaturn\PDFInfo\Exceptions\OpenOutputException;
use Howtomakeaturn\PDFInfo\Exceptions\PDFPermissionException;
use Howtomakeaturn\PDFInfo\Exceptions\OtherException;
use Howtomakeaturn\PDFInfo\Exceptions\CommandNotFoundException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //PDF関連のエラーをエラーメールから除外
        Howtomakeaturn\PDFInfo\Exceptions\OpenPDFException::class,
        Howtomakeaturn\PDFInfo\Exceptions\OpenOutputException::class,
        Howtomakeaturn\PDFInfo\Exceptions\PDFPermissionException::class,
        Howtomakeaturn\PDFInfo\Exceptions\OtherException::class,
        Howtomakeaturn\PDFInfo\Exceptions\CommandNotFoundException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if ($this->shouldntReport($exception)) {
            return;
        }
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }
}
