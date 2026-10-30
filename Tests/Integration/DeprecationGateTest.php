<?php

namespace Shaygan\AffiliateBundle\Tests\Integration;

use PHPUnit\Framework\TestCase;

final class DeprecationGateTest extends TestCase
{
    public function testActualPhpunitCommandEnforcesProjectAndDirectDeprecationsOnly(): void
    {
        $root = dirname(__DIR__, 2);
        $vendor = $root.'/vendor';
        $token = bin2hex(random_bytes(8));
        $api = $vendor.'/affiliate-gate-'.$token.'/api';
        $wrapper = $vendor.'/affiliate-wrapper-'.$token.'/wrapper';
        mkdir($api, 0777, true);
        mkdir($wrapper, 0777, true);
        file_put_contents($api.'/probe.php', '<?php function affiliate_gate_deprecated_api(): void { if (str_ends_with(getenv("AFFILIATE_DEPRECATION_PROBE"), "unsilenced")) { trigger_error("affiliate gate vendor probe", E_USER_DEPRECATED); } else { @trigger_error("affiliate gate vendor probe", E_USER_DEPRECATED); } }');
        file_put_contents($wrapper.'/probe.php', '<?php function affiliate_gate_vendor_wrapper(): void { affiliate_gate_deprecated_api(); }');

        try {
            foreach (['clean' => 0, 'self' => 1, 'self-unsilenced' => 1, 'direct' => 1, 'direct-unsilenced' => 1, 'indirect' => 0, 'indirect-unsilenced' => 0] as $probe => $expectedExit) {
                $environment = array_merge(getenv(), [
                    'AFFILIATE_DEPRECATION_PROBE' => $probe,
                    'AFFILIATE_DEPRECATION_API' => $api.'/probe.php',
                    'AFFILIATE_DEPRECATION_WRAPPER' => $wrapper.'/probe.php',
                ]);
                $process = proc_open([
                    PHP_BINARY, $vendor.'/bin/phpunit', '--configuration', $root.'/phpunit.xml.dist',
                    '--do-not-cache-result', $root.'/Tests/Fixtures/DeprecationGateProbeTest.php',
                ], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root, $environment);
                self::assertIsResource($process);
                $output = stream_get_contents($pipes[1]).stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                $exit = proc_close($process);

                self::assertSame($expectedExit, $exit, $probe." deprecation policy:\n".$output);
                if ($expectedExit === 1) {
                    self::assertStringContainsString('Project deprecation:', $output);
                }
                if ($probe !== 'clean') {
                    $message = str_starts_with($probe, 'self') ? 'affiliate gate self probe' : 'affiliate gate vendor probe';
                    self::assertStringContainsString($message, $output, 'Failing and allowed notices must remain visible.');
                }
            }
        } finally {
            unlink($api.'/probe.php');
            unlink($wrapper.'/probe.php');
            rmdir($api);
            rmdir(dirname($api));
            rmdir($wrapper);
            rmdir(dirname($wrapper));
        }
    }
}
