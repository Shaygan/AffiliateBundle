<?php

namespace Shaygan\AffiliateBundle\Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class DeprecationGateProbeTest extends TestCase
{
    public function testDeprecationPolicy(): void
    {
        require getenv('AFFILIATE_DEPRECATION_API');
        require getenv('AFFILIATE_DEPRECATION_WRAPPER');

        switch (getenv('AFFILIATE_DEPRECATION_PROBE')) {
            case 'self':
                @trigger_error('affiliate gate self probe', E_USER_DEPRECATED);
                break;
            case 'self-unsilenced':
                trigger_error('affiliate gate self probe', E_USER_DEPRECATED);
                break;
            case 'direct':
            case 'direct-unsilenced':
                affiliate_gate_deprecated_api();
                break;
            case 'indirect':
            case 'indirect-unsilenced':
                affiliate_gate_vendor_wrapper();
                break;
        }

        self::assertTrue(true);
    }
}
