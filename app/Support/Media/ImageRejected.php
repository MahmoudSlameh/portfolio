<?php

namespace App\Support\Media;

use RuntimeException;

/**
 * An image given to the MCP connector could not be used; the message is shown to Claude as is.
 */
final class ImageRejected extends RuntimeException {}
