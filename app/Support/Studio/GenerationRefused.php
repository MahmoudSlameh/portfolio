<?php

namespace App\Support\Studio;

use RuntimeException;

/**
 * A generation was not started (AI not ready, daily limit reached); the message is for the owner.
 */
final class GenerationRefused extends RuntimeException {}
