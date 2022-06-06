<?php

namespace App\Modules\Globale\Helpers\PdfParser\Encoding;

abstract class AbstractEncoding
{
    abstract public function getTranslations(): array;
}
