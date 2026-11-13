<?php
declare(strict_types=1);

function generateUserId(): string
{
    return 'USR-' . bin2hex(random_bytes(8));
}