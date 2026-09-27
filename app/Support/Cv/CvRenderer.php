<?php

namespace App\Support\Cv;

/**
 * Renders a CV as a self-contained HTML document (one of resources/views/cv), ready for the PDF engine
 * or a browser preview.
 */
final class CvRenderer
{
    public function html(CvOptions $options = new CvOptions): string
    {
        return view($options->template->view(), [
            'cv' => CvData::build($options),
            'options' => $options,
        ])->render();
    }
}
