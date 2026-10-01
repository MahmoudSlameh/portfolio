<?php

namespace App\Support\Cv;

/**
 * Renders a CV as a self-contained HTML document (one of resources/views/cv), ready for the PDF engine
 * or a browser preview.
 */
final class CvRenderer
{
    /**
     * @param  bool  $preview  For a browser: page-like padding and width (the PDF uses @page margins)
     */
    public function html(CvOptions $options = new CvOptions, bool $preview = false): string
    {
        return view($options->template->view(), [
            'cv' => CvData::build($options),
            'options' => $options,
            'preview' => $preview,
        ])->render();
    }
}
